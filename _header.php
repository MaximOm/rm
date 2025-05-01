<?php $version = 'r1'; ?>
<!doctype html>
<html lang="en" data-loading class="text-white antialiased">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo isset($header['title']) ? $header['title'] : ''; ?> : Riphean Marble pvt ltd</title>
    <meta name="description" content="<?php echo isset($header['description']) ? $header['description'] : ''; ?>" />
    <meta name="keywords" content="<?php echo isset($header['keywords']) ? $header['keywords'] : ''; ?>" />
    <meta name="author" content="<?php echo isset($header['author']) ? $header['author'] : ''; ?>" />

<!--  dns prefetch -->
    <link rel="dns-prefetch" href="/assets/logo.svg">
    <link rel="dns-prefetch" href="/assets/font/centurygothic.ttf">


    <link rel="shortcut icon" href="/assets/favicon--Dr9Mwvm.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/img/apple-touch-icon-dNRuRzj6.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/img/favicon-32x32-UUL8F1lo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/img/favicon-16x16-4uoj4kIi.png">

    <link rel="stylesheet" href="/assets/css/main.css?v=<?=$version ?>" media="all">

    <!-- Basic Styles for Layout -->
    <link rel="stylesheet" href="/assets/css/desktop.css?v=<?=$version ?>" media="screen and (min-width: 1024px)">
    <link rel="stylesheet" href="/assets/css/pad.css?v=<?=$version ?>" media="screen and (min-width: 768px) and (max-width: 1023px)">
    <link rel="stylesheet" href="/assets/css/mobile.css?v=<?=$version ?>" media="screen and (max-width: 767px)">
    <link rel="stylesheet" crossorigin href="assets/css/index-slider-hero.css">

    <link rel="stylesheet" href="/assets/css/aos.css">


    <?php if (isset($header['stylesheets'])) {foreach ($header['stylesheets'] as $stylesheet) {echo '<link rel="stylesheet" href="'.$stylesheet.'">';}} ?>


    <script src="/assets/js/aos.js"></script>

    <?php if(isset($header['css'])) echo '<style>'.$header['css'].'</style>'; ?>


</head>



<body <?php if(isset($page_name)) echo "class='page-$page_name'"; ?> >
