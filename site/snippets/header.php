<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <!--
    <style>
     @import url('https://fonts.googleapis.com/css2?family=Gowun+Batang:wght@400;700&display=swap');
    </style> -->
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Gowun+Dodum&family=Instrument+Sans:ital,wght@0,400..700;1,400..700&display=swap');
    </style>

    <?= css(url: 'assets/css/index.css') ?>
    <?= css(url: '@auto') ?>


</head>
<body>
    <div class="<?= $page->uri() === 'projects' ? 'projects-container' : 'container' ?>">
        <nav class="mainnav">
            <ul>
                <li><a href="<?= $site->url() ?>"><strong><?= $site->title() ?></strong></a></li>
            </ul>
            <ul class="submenu">
                <?php foreach ($site->children()->listed() as $item) { ?>
                <li><a href="<?= $item->url() ?>"><?= $item->title() ?></a></li>
                <?php } ?>    
            </ul>
        </nav>