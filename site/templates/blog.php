<?php snippet('header') ?>
    <main class="main">
        <?php /* 목록만 보여주는 화면이라 제목을 그리지 않는다. 문서에는 있어야 하므로 숨긴다.
                 근거: docs/adr/0011-page-title-outline.md */ ?>
        <h1 class="visually-hidden"><?= $page->title()->esc() ?></h1>

        <ul class="bloglist">
            <?php $blog = page(id: 'blog')->children()->listed();
            foreach ($blog as $blogpost):
            ?>
            <li class="blog-item">
                <a href="<?= $blogpost->url() ?>">
                    <span><?= $blogpost->title()->esc() ?></span>
                </a>
                <small class="blog-date"><?= $blogpost->date() ?></small>
            </li>
            <?php endforeach ?>
        </ul>
    </main>
<?php snippet('footer') ?>