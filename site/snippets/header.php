<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $site->title() ?></title>
    
    <?= css(url: 'assets/css/pico.min.css') ?>
    <?= css(url: 'assets/css/index.css') ?>
    <?= css(url: '@auto') ?>


</head>
<body class="container">
    <nav class="menu">
        <ul>
            <li><a href="<?= $site->url() ?>"><strong><?= $site->title() ?></strong></a></li>
        </ul>
        <ul>
            <?php foreach ($site->children()->listed() as $item) { ?>
            <li><a href="<?= $item->url() ?>" class="contrast"><?= $item->title() ?></a></li>
            <?php } ?>    
        </ul>
    </nav>