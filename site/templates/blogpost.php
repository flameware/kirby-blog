<?php snippet('header', data: ['title'=>'Massive Void | 손성기']) ?>
    <main class="main">
        <?php
        /**
         * 글 머리(제목 + 작성일)
         *
         * 제목은 아직 title 필드가 아니라 본문 첫 블록의 heading이다(이슈 #3).
         * 첫 블록이 heading일 때만 떼어내 제목으로 쓴다.
         * 위치와 무관하게 첫 heading을 찾으면 본문 중간 소제목이
         * 페이지 제목으로 조용히 승격되는 사고가 나므로 그렇게 하지 않는다.
         */
        $blocks = $page->blocks()->toBlocks();
        $first = $blocks->first();
        $hasTitleBlock = $first !== null && $first->type() === 'heading';
        $body = $hasTitleBlock ? $blocks->offset(1) : $blocks;
        ?>
        <header class="post-header">
            <?php if ($hasTitleBlock): ?>
            <h1 class="post-title"><?= $first->text() ?></h1>
            <?php endif ?>
            <?php if ($date = $page->date()->toDate('Y-m-d')): ?>
            <time class="post-date" datetime="<?= $date ?>"><?= $date ?></time>
            <?php endif ?>
        </header>
        <?= $body ?>
        <ul class="tags">
            <?php foreach ($page->tags()->split() as $category): ?>
            <li><?= $category ?></li>
            <?php endforeach ?>
        </ul>
        <?php snippet('postnav') ?>

    </main>
<?php snippet('footer') ?>
