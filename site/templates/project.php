<?php snippet('header') ?>

    <main class="main">
        <article>
        <h1><?= $page->title() ?></h1>
        <div class="project-layout">
            <div class="project-info">
                <?php if ($page->text()->isNotEmpty()) { ?>
                    <p class="project-text">
                         <?= $page->text() ?>
                    </p>
                <?php } ?>
                <dl>
                    <dt>Year</dt>
                    <dd><?= $page->year() ?></dd>
                    
                    <?php if ($page->category()->isNotEmpty()) { ?>
                    <dt>Category</dt>
                    <dd><?= $page->category() ?></dd>
                    <?php } ?>

                </dl>
            </div>
            <div class="project-gallery">
            <ul>
                <?php foreach ($page->images() as $image) { ?>
                    <li>
                        <a href="<?= $image->url() ?>">
                            <?= $image->resize(1200,1200) ?>
                        </a>
                </li>
                <?php } ?>
                </ul>
            </div>
        </div>
        </article>

    </main>

<?php snippet('footer') ?>