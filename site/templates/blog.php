<?php snippet('header') ?>

    <main class="main">
        <h1><?= $page->title() ?></h1>
        <ul class="blog-list">
            <?php foreach ($page->children()->listed() as $blogpost) { ?>
            <li>
                <a href="<?= $blogpost->url() ?>" class="contrast">
                    <?php foreach ($blogpost->blocks()->toBlocks() as $block) { ?>
                        <?php if ($block->type() === "heading"): ?>
                            <?= $block ?>
                        <?php endif ?>
                    <?php } ?>
                </a>
            </li>
            <?php } ?>  
        </ul>
    </main>
<?php snippet('footer') ?>