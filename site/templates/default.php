<?php snippet('header', data: ['title'=>'Massive Void | 손성기']) ?>

    <main class="main">
        <h1><?= $page->title() ?></h1>
        <?= $page->text() ?>
    </main>

<?php snippet('footer') ?>