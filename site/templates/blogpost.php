<?php snippet('header', data: ['title'=>'Massive Void | 손성기']) ?>
    <main class="main">
        <p class="date">작성: <?= $page->date() ?></p>
        <?= $page->blocks()->toBlocks() ?>
        <ul class="tags">
            <?php foreach ($page->tags()->split() as $category): ?>
            <li><?= $category ?></li>
            <?php endforeach ?>
        </ul>

    </main>
<?php snippet('footer') ?>