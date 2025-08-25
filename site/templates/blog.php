<?php snippet('header') ?>

    <main class="main">
        <h1><?= $page->title() ?></h1>
        <ul class="blog-list">
            <?php foreach ($page->children()->listed() as $blogpost) { ?>
            <li>
                <a href="<?= $blogpost->url() ?>" class="contrast">
                    <?= $blogpost->title() ?>
                </a>
            </li>
            <?php } ?>  
        </ul>
    </main>
<?php snippet('footer') ?>