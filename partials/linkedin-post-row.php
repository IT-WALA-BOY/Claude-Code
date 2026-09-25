<?php
/** @var array $post */
$tone = ['posted' => 'positive', 'scheduled' => 'muted', 'draft' => 'warning'][$post['status']];
$label = match ($post['status']) {
    'posted' => 'Posted' . ($post['impressions'] ? ' · ' . number_format((int) $post['impressions']) . ' views' : ''),
    'scheduled' => 'Scheduled',
    default => 'Draft' . ($post['post_on'] === today() ? ' · post today' : ''),
};
$json = json_encode(['id' => (int) $post['id'], 'topic' => $post['topic'], 'status' => $post['status'], 'post_on' => (string) $post['post_on'],
    'url' => $post['url'], 'impressions' => (int) $post['impressions'], 'reactions' => (int) $post['reactions']], JSON_UNESCAPED_UNICODE);
?>
<button class="post-row" type="button" data-record="<?= e($json) ?>" data-edit="post-dialog">
  <span class="date-tile"><small><?= e($post['post_on'] ? day($post['post_on'])->format('D') : 'No') ?></small><b><?= e($post['post_on'] ? day($post['post_on'])->format('j') : 'date') ?></b></span>
  <span class="post-topic truncate"><?= e($post['topic']) ?></span>
  <?= status_badge($label, $tone) ?>
</button>
