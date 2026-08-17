<?php snippet('header', data: ['title'=>'Massive Void | 손성기']) ?>

    <main class="main">
        <?php
        /**
         * 프로젝트 머리
         *
         * 프로젝트는 작성일을 갖지 않으므로 제목만 온다.
         * 근거: docs/adr/0004-title-field.md
         */
        ?>
        <header class="post-header">
            <h1 class="post-title"><?= $page->title()->esc() ?></h1>
        </header>
        <?= $page->blocks()->toBlocks() ?>
        <div class="project-gallery">
        <?php foreach( $page->images() as $image) { ?>
            <a href="<?= $image->url() ?>" target="_blank">
                <?= $image ?>
            </a>
        <?php } ?>
        </div>
        <ul class="tags">
            <?php foreach ($page->tags()->split() as $category): ?>
            <li><?= $category ?></li>
            <?php endforeach ?>
        </ul>
        <?php snippet('postnav') ?>

    </main>

<?php snippet('footer') ?>