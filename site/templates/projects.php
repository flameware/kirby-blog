<?php snippet('header') ?>

        <main class="main">
        <ul class="projects">
            <?php foreach ($page->children()->listed() as $project) { ?>
            <li>
                <a href="<?= $project->url() ?>" class="contrast">
                    <figure>
                        <?= $project->image() ?>
                        <figcaption><?= $project->title() ?></figcaption>
                    </figure>
                </a>
            </li>
            <?php } ?>  
        </ul>
    </main>
<?php snippet('footer') ?>