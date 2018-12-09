<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="theme-color" content="#333333">
    <title><?= $title; ?> <?php if ($title) { ?> - <? } ?> <?= Config::get('site.name'); ?></title>
    <link rel="stylesheet" href="/styles/bootstrap-grid.min.css">
    <link rel="stylesheet" href="/styles/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.2/jquery.fancybox.min.css">
    <link rel="stylesheet" href="/styles/main.css?v=0.0.5">
    <link rel="stylesheet" href="/styles/mobile.css?v=0.0.5">

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=cyrillic-ext">
    <link rel="stylesheet" href="/styles/fontawesome.min.css">
    <link rel="stylesheet" href="/styles/solid.min.css">

    <!--    Favicon-->
    <link rel="shortcut icon" href="/images/favicon.png">
</head>
<body>
<noscript>
    <div class="container">
        <div class="row">
            <div class="col-12">Для работы с сайтом необходима поддержка JavaScript</div>
        </div>
    </div>
</noscript>
<!-- Header -->
<div class="container-fluid header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-4">
                <a href="/">
                    <img src="<?= Config::get('site.logo'); ?>" alt="<?= Config::get('site.name'); ?>" class="logo">
                </a>
            </div>
            <div class="col-8 m-main-menu-container">
                <div class="m-menu" id="m-main-menu">
                    <i class="fa fa-bars"></i>
                </div>
                <div class="menu">
                    <a href="/">Главная</a>
                    <a href="/information/delivery">Доставка</a>
                    <a href="/information/pay">Оплата</a>
                    <a href="/information/about-us">О нас</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toolbar (search, cart, login) -->
<div class="container toolbar">
    <div class="row">
        <!-- Mobile categories-->
        <div class="col-md-5 col-12 mobile-menu order-3 order-md-1">
            <div id="menu-btn">
                <i class="fa fa-bars"></i>
                Категории
            </div>
            <div class="m-categories">
                <?php if (isset($categories)):
                    foreach ($categories as $category): ?>
                        <div class="m-category-item"><a
                                href="/products/category/<?= $category['id']; ?>"><?= $category['name']; ?></a></div>
                    <?php endforeach;
                endif; ?>
            </div>
        </div>

        <div class="col-xl-3 offset-xl-7 col-md-4 col-6 offset-lg-5 order-md-2">
            <input type="text" class="search" placeholder="Введите название..." id="search-text">
        </div>
        <div class="col-xl-2 col-md-3 col-6 buttons order-1 order-md-3">
            <a href="#" id="search-button"><i class="fas fa-search"></i></a>
            <a href="/account/login"><i class="fas fa-user"></i></a>
            <a href="/cart"><i class="fas fa-shopping-cart"></i></a>
        </div>
    </div>
</div>

<!-- Main content -->
<div class="container main">
    <div class="row">

        <div class="col-3 hidden-md">
            <div id="categories-large" class="">
                <div class="block-name">
                    Категории
                </div>
                <div class="categories">
                    <?php foreach ($categories as $category): ?>
                        <?php if ($category['subs']): ?>
                            <div class="category-item has-submenu">
                                <a href="/products/category/<?= $category['id']; ?>"><?= $category['name']; ?></a>
                                <div class="subcategories">
                                    <?php foreach ($category['subs'] as $subcategory): ?>
                                        <a href="/products/category/<?= $subcategory['id']; ?>"><?= $subcategory['name']; ?></a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="category-item">
                                <a href="/products/category/<?= $category['id']; ?>"><?= $category['name']; ?></a>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

        <!-- Content -->

        <div class="col-lg-9 col-12">
            <?= $content; ?>
        </div>

        <!-- End Content -->

    </div>
</div>

<div class="container-fluid footer">
    <div class="container">
        <div class="row">
            <div class="col-3 hidden-md">
                <a href="/">
                    <img src="<?= Config::get('site.logo'); ?>" alt="<?= Config::get('site.name'); ?>" class="logo">
                </a>
                <div>
                    <?= Config::get('site.desc'); ?>
                </div>
            </div>
            <div class="col-lg-2 offset-lg-1 col-sm-4">
                <div class="footer-name">
                    Категории
                </div>
                <div class="footer-menu">
                    <?php foreach ($categories as $category): ?>
                        <a href="/products/category/<?= $category['id']; ?>"
                           class="footer-link"><?= $category['name']; ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-lg-2 col-sm-4">
                <div class="footer-name">
                    Информация
                </div>
                <div class="footer-menu">
                    <a href="/information/delivery" class="footer-link">Доставка</a>
                    <a href="/information/pay" class="footer-link">Оплата</a>
                    <a href="/information/about-us" class="footer-link">О нас</a>
                </div>
            </div>
            <div class="col-lg-4 col-sm-4">
                <div class="footer-name">
                    Контакты
                </div>
                <div class="footer-menu">
                    <div class="footer-contact icon-address"><?= Config::get('contact.address'); ?></div>
                    <a href="tel:<?= Config::get('contact.phone'); ?>"
                       class="footer-link footer-contact icon-phone"><?= Config::get('contact.phone'); ?></a>
                    <a href="mailto:<?= Config::get('contact.email'); ?>"
                       class="footer-link footer-contact icon-mail"><?= Config::get('contact.email'); ?></a>
                </div>
            </div>
        </div>
        <div class="row copyright">
            <div class="col-12">
                © <?= Config::get('site.name'); ?> 2018 <br>
                Designed by Dmitry Batuev.
            </div>
        </div>
    </div>
</div>

<script src="//ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="/scripts/main.js?v=0.0.4"></script>
<script src="/scripts/owl.carousel.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.2/jquery.fancybox.min.js"></script>
</body>
</html>