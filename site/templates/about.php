<?php snippet('header') ?>


<main class="main">
        <?php /* 본문이 인사말로 시작하는 화면이라 제목을 그리지 않는다. 문서에는 있어야 하므로 숨긴다.
                 근거: docs/adr/0011-page-title-outline.md */ ?>
        <h1 class="visually-hidden"><?= $page->title()->esc() ?></h1>

        <?= $page->blocks()->toBlocks() ?>

    </main>


<?php snippet('footer') ?>