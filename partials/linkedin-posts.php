<?php $posts = li_posts(); ?>
<section class="card section-gap">
  <?= card_head('megaphone', 'All posts', '<span class="meta">Drafts and scheduled first</span>', count($posts)) ?>
  <div class="panel panel-flush">
    <?php if (!$posts): ?>
      <?= empty_state('megaphone', 'No posts yet', 'Plan your first teardown post.', '<button class="btn btn-sm" type="button" data-open="post-dialog">Add post</button>') ?>
    <?php endif ?>
    <?php foreach ($posts as $post): ?>
      <?= partial('linkedin-post-row', ['post' => $post]) ?>
    <?php endforeach ?>
  </div>
</section>
