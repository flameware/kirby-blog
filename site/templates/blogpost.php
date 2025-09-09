<?php snippet('header', data: ['title'=>'Massive Void | 손성기']) ?>
    <main class="main">
        <?= $page->blocks()->toBlocks() ?>
        <p class="date">작성: <?= $page->date() ?></p>
        <ul class="tags">
            <?php foreach ($page->tags()->split() as $category): ?>
            <li><?= $category ?></li>
            <?php endforeach ?>
        </ul>

    </main>
<?php snippet('footer') ?>