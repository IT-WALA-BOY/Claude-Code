<?php
declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function rate(): float
{
    return (float) setting('pkr_rate', '282') ?: 282.0;
}

/** "$6,240" for whole dollars, "$6,240.50" otherwise. */
function usd(float|int|string|null $amount): string
{
    $amount = (float) $amount;
    $decimals = fmod(abs($amount), 1.0) >= 0.005 ? 2 : 0;
    return ($amount < 0 ? '-$' : '$') . number_format(abs($amount), $decimals);
}

/** Short money for chart axes: "$3k", "$1.5k". */
function usd_short(float $amount): string
{
    return $amount >= 1000 ? '$' . rtrim(rtrim(number_format($amount / 1000, 1), '0'), '.') . 'k' : usd($amount);
}

function pkr(float|int|string|null $amount): string
{
    return 'Rs ' . number_format((float) $amount);
}

function money(float|int|string $amount, string $currency): string
{
    return $currency === 'PKR' ? pkr($amount) : usd($amount);
}

/** Converts a stored amount to USD using the rate saved with it. */
function to_usd(float|string $amount, string $currency, float|string|null $rate = null): float
{
    return $currency === 'PKR' ? (float) $amount / ((float) $rate ?: rate()) : (float) $amount;
}

function pct(float $part, float $whole): int
{
    return $whole > 0 ? (int) round(min(100, max(0, $part / $whole * 100))) : 0;
}

/** Formats a number without trailing zeros: 24, 1.5. */
function num(float|int|string $n): string
{
    return rtrim(rtrim(number_format((float) $n, 1), '0'), '.');
}

function plural(int $n, string $one, ?string $many = null): string
{
    return $n . ' ' . ($n === 1 ? $one : ($many ?? $one . 's'));
}

function icon(string $name, int $size = 20, string $class = ''): string
{
    static $sprite = null;
    $sprite ??= e(asset('icons.svg'));
    return sprintf(
        '<svg class="i%s" width="%d" height="%d" aria-hidden="true"><use href="%s#%s"/></svg>',
        $class ? ' ' . e($class) : '',
        $size,
        $size,
        $sprite,
        e($name)
    );
}

function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $first = mb_substr($parts[0] ?? '', 0, 1);
    $last = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';
    return mb_strtoupper($first . $last) ?: 'A';
}

/** Adds attributes to a tag only when their value is not empty. */
function attrs(array $attrs): string
{
    $out = '';
    foreach ($attrs as $k => $v) {
        if ($v === true) {
            $out .= ' ' . $k;
        } elseif ($v !== null && $v !== false && $v !== '') {
            $out .= sprintf(' %s="%s"', $k, e($v));
        }
    }
    return $out;
}
