<?php snippet('header') ?>

    <main class="main">
        <?php /* 화면에는 그리지 않는 제목. 홈의 제목은 사이트 이름 자체다 —
                 metaTitle()이 <title>을 만드는 방식과 같은 근거를 쓴다.
                 근거: docs/adr/0011-page-title-outline.md */ ?>
        <h1 class="visually-hidden"><?= $site->title() ?></h1>

        <div class="home-intro">
            <?php /* 문장마다 새 줄에서 시작한다. 문장 하나를 블록 하나로 두는 것은
                 text-wrap: balance가 블록마다(Chrome은 6줄 이하만) 적용되기 때문이다 —
                 <br />로 나누면 문단 전체가 한 블록이라 balance가 통째로 무시된다.
                 문장 중간에서 줄을 끊지 않는다. 근거: CONTEXT.md "인사말", #44 */ ?>
            <p>
                <span>이곳은 손성기의 글과 개인작업을 모은 공간입니다.</span>
                <span>디지털 서비스를 만들고 고치는 일을 합니다.</span>
                <span>헤비메탈과 건축, F1과 NBA, 그리고 그 밖의 다양한 것에 관심이 있습니다.</span>
            </p>
        </div>

    <hr />
        <!-- blog sample -->

        <ul class="home-bloglist">
            <?php $blog = page(id: 'blog')->children()->listed()->limit(5);
            foreach ($blog as $blogpost):
            ?>
            <li class="home-blog-item">
                <a href="<?= $blogpost->url() ?>">
                    <span><?= $blogpost->title()->esc() ?></span>
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
                        <figcaption><span><?= $project->title() ?></span></figcaption>
                    </figure>
                </a>
            </li>

            <?php endforeach ?>
        <small><a href="<?= page('projects')->url() ?>">개인 프로젝트 더 보기 ></a></small>
        </ul>
        <hr />
    </main>

<?php snippet('footer') ?>
