<?php snippet('header', data: ['title'=>'Massive Void | 손성기']) ?>

    <main class="main">
        <?= $page->blocks()->toBlocks() ?>
        <div class="project-gallery">
        <?php foreach( $page->images() as $image) { ?>
            <a href="<?= $image->url() ?>" target="_blank">
                <?= $image ?>
            </a>
        <?php } ?>
        </div>
        <ul class="tags">
            <?php foreach ($page->tags()->split() as $category): ?>
            <li><?= $category ?></li>
            <?php endforeach ?>
        </ul> 
   
    </main>

<?php snippet('footer') ?>