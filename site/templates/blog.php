<?php snippet('header') ?>
    <main class="main">
        <ul class="bloglist">
            <?php $blog = page(id: 'blog')->children()->listed();
            foreach ($blog as $blogpost):
            ?>
            <li class="blog-item">
                <a href="<?= $blogpost->url() ?>">
                    <p><?= $blogpost->title()->esc() ?></p>
                </a>
                <small class="blog-date"><?= $blogpost->date() ?></small>
            </li>
            <?php endforeach ?>
        </ul>
    </main>
<?php snippet('footer') ?>