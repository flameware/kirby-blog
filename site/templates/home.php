<?php snippet('header') ?>

    <main class="main">
        <p>Massive Void는 손성기의 글과 개인작업을 모은 공간입니다.<br /> UX디자이너/프로덕트디자이너로 일하고 있습니다. <br /> 헤비메탈과 건축, F1과 NBA에 관심이 있습니다. </p>

    <hr />
        <!-- blog sample -->

        <ul class="home-bloglist">
            <?php $blog = page(id: 'blog')->children()->listed()->limit(5);
            foreach ($blog as $blogpost):
            ?>
            <li class="blog-item">
                <a href="<?= $blogpost->url() ?>">
                    <?php foreach ($blogpost->blocks()->toBlocks() as $block) { ?>
                        <?php if ($block->type() === "heading"): ?>
                                <?= $block->content()->text() ?>
                        <?php endif ?>
                    <?php } ?>
                </a>
                <small class="blog-date"><?= $blogpost->date() ?></small>
            </li>

            <?php endforeach ?>
        <small><a href="<?= page('blog')->url() ?>">...블로그 더 보기</a></small>

        </ul>
        
        <hr />
        <!-- projects sample -->
        <ul class="home-projectlist">
            <?php $projects = page('projects')->children()->listed()->limit(4);
            foreach ($projects as $project):
            ?>
            <li>
                <a href="<?= $project->url() ?>">
                    <figure>
                        <?= $project->image() ?>
                        <figcaption><?= $project->title() ?></figcaption>
                    </figure>
                </a>
            </li>

            <?php endforeach ?>
        <small><a href="<?= page('projects')->url() ?>">...프로젝트 더 보기</a></small>
        </ul>
        <hr />
    </main>

<?php snippet('footer') ?>