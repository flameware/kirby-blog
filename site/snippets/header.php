<?php
/**
 * 문서 머리
 *
 * 제목과 요약은 템플릿이 넘기지 않는다. 페이지가 스스로 답한다.
 * 근거: docs/adr/0005-page-metadata.md
 */
$metaTitle = $page->metaTitle();
$metaDescription = $page->metaDescription();
$metaCard = $page->metaCard();
?>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow" />
    <title><?= esc($metaTitle) ?></title>
    <?php if ($metaDescription !== ""): ?>
    <meta name="description" content="<?= esc($metaDescription) ?>">
    <?php endif ?>

    <meta property="og:title" content="<?= esc($metaTitle) ?>">
    <?php if ($metaDescription !== ""): ?>
    <meta property="og:description" content="<?= esc($metaDescription) ?>">
    <?php endif ?>
    <meta property="og:url" content="<?= $page->url() ?>">
    <meta property="og:type" content="<?= $page->metaType() ?>">
    <meta property="og:site_name" content="<?= esc($site->title()) ?>">
    <meta property="og:locale" content="ko_KR">
    <?php if ($metaCard !== null): ?>
    <meta property="og:image" content="<?= $metaCard["url"] ?>">
    <meta property="og:image:width" content="<?= $metaCard["width"] ?>">
    <meta property="og:image:height" content="<?= $metaCard["height"] ?>">
    <meta property="og:image:alt" content="<?= esc($metaCard["alt"]) ?>">
    <?php endif ?>
    <meta name="twitter:card" content="<?= $metaCard !== null
      ? "summary_large_image"
      : "summary" ?>">
    <link rel="icon" href="<?= url(
      "assets/favicon.svg",
    ) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <link rel="canonical" href="<?= $page->url() ?>">

    <style>
     @import url('https://fonts.googleapis.com/css2?family=Gowun+Batang:wght@400;700&display=swap');
    </style>
    <!--
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Gowun+Dodum&family=Instrument+Sans:ital,wght@0,400..700;1,400..700&display=swap');
    </style> -->

    <?= css(url: "assets/css/index.css") ?>
    <?= css(url: "@auto") ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileToggle = document.querySelector('.mobile-menu-toggle');
            const submenu = document.querySelector('.submenu');

            if (mobileToggle && submenu) {
                mobileToggle.addEventListener('click', function() {
                    mobileToggle.classList.toggle('active');
                    submenu.classList.toggle('active');
                });

                document.addEventListener('click', function(event) {
                    if (!event.target.closest('.mainnav')) {
                        mobileToggle.classList.remove('active');
                        submenu.classList.remove('active');
                    }
                });

                submenu.addEventListener('click', function(event) {
                    if (event.target.tagName === 'A') {
                        mobileToggle.classList.remove('active');
                        submenu.classList.remove('active');
                    }
                });
            }
        });
    </script>

    <?php if ($goatcounter = option("analytics.goatcounter")): ?>
    <script data-goatcounter="https://<?= $goatcounter ?>.goatcounter.com/count"
            async src="//gc.zgo.at/count.js"></script>
    <?php endif; ?>

</head>
<body>
    <div class="<?= $page->uri() === "projects"
      ? "projects-container"
      : "container" ?>">
        <nav class="mainnav">
            <ul>
                <li><a href="<?= $site->url() ?>"><strong><?= $site->title() ?></strong></a></li>
            </ul>
            <button class="mobile-menu-toggle" aria-label="Toggle navigation menu">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
            <ul class="submenu">
                <?php foreach ($site->children()->listed() as $item) { ?>
                <li><a href="<?= $item->url() ?>"><?= $item->title() ?></a></li>
                <?php } ?>
            </ul>
        </nav>
