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

    <?php if ($goatcounter = option("analytics.goatcounter")): ?>
    <script data-goatcounter="https://<?= $goatcounter ?>.goatcounter.com/count"
            async src="//gc.zgo.at/count.js"></script>
    <?php endif; ?>

</head>
<body>
    <?php /* 내비게이션. 브랜드와 링크가 한 덩어리로 묶인 island이고, 모바일에서도
             접히지 않는다. 본문 열 바깥에 있는 것이 핵심이다 — 열 안에 두면 열 폭(46.7vw)과
             열의 정렬 방식에 끌려다닌다. body가 이미 가운데 정렬하는 flex라, 밖으로 꺼내면
             폭만 정해도 화면 중앙에 놓인다.
             근거: docs/adr/0009-navigation-island.md */ ?>
    <nav class="mainnav">
        <a class="mainnav-brand" href="<?= $site->url() ?>"><strong><?= $site->title() ?></strong></a>
        <ul class="mainnav-links">
            <?php foreach ($site->children()->listed() as $item): ?>
            <?php /* 글 상세(blog/어떤-글)에서도 blog가 활성이어야 하므로 자손까지 본다.
                     aria-current가 곧 CSS 선택자다 — 형광펜과 접근성이 어긋날 수 없다. */ ?>
            <?php $isCurrent = $page->is($item) || $page->isDescendantOf($item) ?>
            <li><a href="<?= $item->url() ?>"<?= $isCurrent ? ' aria-current="page"' : "" ?>><?= $item->title() ?></a></li>
            <?php endforeach ?>
        </ul>
    </nav>
    <div class="<?= $page->uri() === "projects"
      ? "projects-container"
      : "container" ?>">
