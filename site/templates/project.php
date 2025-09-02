<?php snippet('header') ?>

    <main class="main">
        <p class="project-date"><?= $page->date() ?></p>
        <ul class="tags">
            <?php foreach ($page->tags()->split() as $category): ?>
            <li><?= $category ?></li>
            <?php endforeach ?>
        </ul> 
        <?= $page->blocks()->toBlocks() ?>
        <div class="project-gallery">
        <?php foreach( $page->images() as $image) { ?>
            <a href="<?= $image->url() ?>" target="_blank">
                <?= $image ?>
            </a>
        <?php } ?>
        </div>
   
    </main>

<?php snippet('footer') ?>