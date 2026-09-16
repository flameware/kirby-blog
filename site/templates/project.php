<?php snippet('header') ?>

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
        <?php
        /**
         * 작업 이미지
         *
         * 예전에는 이미지마다 <a target="_blank">를 감싸 원본 파일을 새 탭에 열었다.
         * 이제 라이트박스가 그 일을 하므로 링크를 걷어냈다 — 같은 <img>가 화면에 따라
         * 다르게 반응하면 독자는 눌러 봐야 알 수 있다.
         * 근거: docs/adr/0015-own-javascript.md
         */
        ?>
        <div class="project-gallery">
        <?php foreach( $page->images() as $image) { ?>
            <?= $image ?>
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