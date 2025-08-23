<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $site->title() ?></title>
    
    <?= css(url: 'assets/css/index.css') ?>

</head>
<body>
    <h1><?= $page->title() ?></h1>
    <?= $page->text() ?>
</body>
</html>

