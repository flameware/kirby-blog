<?php snippet('header') ?>
        <main class="main">
        <?php /* 목록만 보여주는 화면이라 제목을 그리지 않는다. 문서에는 있어야 하므로 숨긴다.
                 근거: docs/adr/0011-page-title-outline.md */ ?>
        <h1 class="visually-hidden"><?= $page->title()->esc() ?></h1>

        <?php
        /* 카드의 `sizes`는 .projects 그리드(assets/css/templates/projects.css)를 옮겨 적은
           것이다. 그리드의 좌우 여백·열 수·간격을 고치면 여기도 고친다.
             넓은 화면: 좌우 4vw, 4열, 간격 1rem → (92vw - 3 × 16px) / 4
             480px 이하: 좌우 20px, 2열, 간격 1rem → (100vw - 40px - 16px) / 2
           근거: docs/adr/0018-responsive-images.md, #43 */
        $sizes    = "(max-width: 480px) calc((100vw - 40px - 16px) / 2), calc((92vw - 48px) / 4)";
        $projects = $page->children()->listed();
        ?>
        <ul class="projects">
            <?php foreach ($projects as $project) { ?>
            <li>
                <a href="<?= $project->url() ?>" class="contrast">
                    <figure>
                        <?php if ($image = $project->image()) {
                            // 넓은 화면 첫 줄 4장은 첫 화면 안이다
                            snippet('image', [
                                'image'   => $image,
                                'sizes'   => $sizes,
                                'full'    => false,
                                'loading' => $projects->indexOf($project) < 4 ? 'eager' : 'lazy',
                            ]);
                        } ?>
                        <figcaption><span><?= $project->title() ?></span></figcaption>
                    </figure>
                </a>
            </li>
            <?php } ?>  
        </ul>
    </main>
<?php snippet('footer') ?>