<?php
declare(strict_types=1);

/*
 * Optional Claude helper. Only used when an Anthropic API key is saved in Settings.
 * Raw HTTPS through PHP's cURL extension, because the app ships without Composer (zip + .sql deploy).
 */

const AI_MODEL = 'claude-opus-5';
const AI_URL = 'https://api.anthropic.com/v1/messages';

function ai_enabled(): bool
{
    return setting('anthropic_key') !== '' && function_exists('curl_init');
}

/**
 * Sends one prompt and returns the JSON object Claude produced for $schema.
 * Structured outputs guarantee the reply matches the schema.
 */
function ai_json(string $prompt, array $schema, int $maxTokens = 4000): array
{
    if (!ai_enabled()) {
        fail('Add an Anthropic API key in Settings to use this.');
    }
    $body = [
        'model' => AI_MODEL,
        'max_tokens' => $maxTokens,
        'output_config' => ['effort' => 'low', 'format' => ['type' => 'json_schema', 'schema' => $schema]],
        'fallbacks' => 'default', // on a safety decline, the API retries on a suitable model in the same call
        'messages' => [['role' => 'user', 'content' => $prompt]],
    ];
    $ch = curl_init(AI_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 90,
        CURLOPT_HTTPHEADER => [
            'content-type: application/json',
            'x-api-key: ' . setting('anthropic_key'),
            'anthropic-version: 2023-06-01',
            'anthropic-beta: server-side-fallback-2026-07-01',
        ],
        CURLOPT_POSTFIELDS => json_encode($body),
    ]);
    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    $res = is_string($raw) ? json_decode($raw, true) : null;
    if ($status === 401) {
        fail('Claude rejected the API key. Check it in Settings.');
    }
    if ($status === 429 || $status >= 500 || !is_array($res)) {
        fail('Claude is busy right now. Try again in a minute.');
    }
    if ($status !== 200) {
        error_log('Claude API ' . $status . ': ' . $raw);
        fail('Claude could not answer that. Try again.');
    }
    if (($res['stop_reason'] ?? '') === 'refusal') {
        fail('Claude declined this request. Fill in the parts yourself.');
    }
    foreach ($res['content'] ?? [] as $block) {
        if (($block['type'] ?? '') === 'text') {
            $data = json_decode($block['text'], true);
            if (is_array($data)) {
                return $data;
            }
        }
    }
    fail('Claude sent an answer the dashboard could not read. Try again.');
}

/** Splits a goal into ordered parts with sizes that add up to the total. */
function ai_split_goal(string $title, float $total, string $unit, int $count): array
{
    $prompt = "I am planning a personal goal and need it split into parts I finish in order.\n"
        . "Goal: $title\nTotal: " . num($total) . " $unit\n"
        . ($count > 0 ? "Number of parts: $count\n" : "Pick a sensible number of parts (3 to 12).\n")
        . "If the goal is a known course or program (for example the CXL mini degree), use its real module names and order. "
        . "Keep each name under 60 characters, no em dashes. The part sizes must add up to the total.";
    $schema = [
        'type' => 'object',
        'properties' => [
            'parts' => [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => ['title' => ['type' => 'string'], 'size' => ['type' => 'number']],
                    'required' => ['title', 'size'],
                    'additionalProperties' => false,
                ],
            ],
        ],
        'required' => ['parts'],
        'additionalProperties' => false,
    ];
    $parts = [];
    foreach (ai_json($prompt, $schema)['parts'] ?? [] as $p) {
        $name = mb_substr(trim(str_replace('—', ',', (string) ($p['title'] ?? ''))), 0, 160);
        $size = round((float) ($p['size'] ?? 0) * 2) / 2;
        if ($name !== '' && $size > 0) {
            $parts[] = ['title' => $name, 'target' => $size];
        }
    }
    if (!$parts) {
        fail('Claude did not suggest any parts. Try again.');
    }
    return $parts;
}
