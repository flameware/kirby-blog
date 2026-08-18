<?php snippet('header') ?>

    <main class="main">
        <?php /* 화면에는 그리지 않는 제목. 홈의 제목은 사이트 이름 자체다 —
                 metaTitle()이 <title>을 만드는 방식과 같은 근거를 쓴다.
                 근거: docs/adr/0011-page-title-outline.md */ ?>
        <h1 class="visually-hidden"><?= $site->title() ?></h1>

        <div class="home-intro">
            <p>이곳은 손성기의 글과 개인작업을 모은 공간입니다.<br /> UX디자이너/프로덕트디자이너로 일하고 있습니다. <br /> 헤비메탈과 건축, F1과 NBA, <br /> 그리고 그 밖의 다양한 것에 관심이 있습니다. </p>
        </div>

    <hr />
        <!-- blog sample -->

        <ul class="home-bloglist">
            <?php $blog = page(id: 'blog')->children()->listed()->limit(5);
            foreach ($blog as $blogpost):
            ?>
            <li class="home-blog-item">
                <a href="<?= $blogpost->url() ?>">
                    <p><?= $blogpost->title()->esc() ?></p>
                </a>
                <small class="home-blog-date"><?= $blogpost->date() ?></small>
            </li>

            <?php endforeach ?>
        <small><a href="<?= page('blog')->url() ?>">블로그 더 보기 ></a></small>

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
        <small><a href="<?= page('projects')->url() ?>">개인 프로젝트 더 보기 ></a></small>
        </ul>
        <hr />
    </main>

<?php snippet('footer') ?>
