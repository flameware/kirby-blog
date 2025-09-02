<?php snippet('header') ?>

    <main class="main">
        <ul class="bloglist">
            <?php $blog = page(id: 'blog')->children()->listed()->limit(5);
            foreach ($blog as $blogpost):
            ?>
            <li class="blog-item">
                <a href="<?= $blogpost->url() ?>">
                    <?php foreach ($blogpost->blocks()->toBlocks() as $block) { ?>
                        <?php if ($block->type() === "heading"): ?>
                                <p><?= $block->content()->text() ?></p>
                        <?php endif ?>
                    <?php } ?>
                </a>
                <small class="blog-date"><?= $blogpost->date() ?></small>
            </li>
            <?php endforeach ?>
        </ul>
    </main>
<?php snippet('footer') ?>