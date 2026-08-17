<?php snippet('header', data: ['title'=>'Massive Void | 손성기']) ?>
    <main class="main">
        <?php
        /**
         * 글 머리(제목 + 작성일)
         *
         * 제목은 title 필드 하나뿐이다. 본문에는 제목이 없다.
         * 근거: docs/adr/0004-title-field.md
         */
        ?>
        <header class="post-header">
            <h1 class="post-title"><?= $page->title()->esc() ?></h1>
            <?php if ($date = $page->date()->toDate('Y-m-d')): ?>
            <time class="post-date" datetime="<?= $date ?>"><?= $date ?></time>
            <?php endif ?>
        </header>
        <?= $page->blocks()->toBlocks() ?>
        <ul class="tags">
            <?php foreach ($page->tags()->split() as $category): ?>
            <li><?= $category ?></li>
            <?php endforeach ?>
        </ul>
        <?php snippet('postnav') ?>

    </main>
<?php snippet('footer') ?>
