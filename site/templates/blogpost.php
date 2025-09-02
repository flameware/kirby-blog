<?php snippet('header') ?>
    <main class="main">
        <?= $page->blocks()->toBlocks() ?>
        <?= $page->date() ?>
        <ul class="tags">
            <?php foreach ($page->tags()->split() as $category): ?>
            <li><?= $category ?></li>
            <?php endforeach ?>
        </ul>       

    </main>
<?php snippet('footer') ?>