<?php

Config::set('site.name', 'The Electronix');
Config::set('site.desc', 'Интернет-магазин электроники');
Config::set('site.logo', '/images/logo_test.png');

Config::set('contact.address', 'Россия, Удмуртская республика, г. Глазов, ул. Энгельса, 1, 1');
Config::set('contact.phone', '8-912-455-70-97');
Config::set('contact.email', 'support@the-electronix.store');

Config::set('languages', array('ru'));

Config::set('routes', array(
    'default' => '',
    'dashboard' => 'dashboard_',
    'ajax' => 'ajax_',
    'api' => 'api_',
));

Config::set('email.from', 'no-reply@the-electronix.store');
Config::set('email.orders', 'i@batuevdm.ru');

Config::set('default.route', 'default');
Config::set('default.language', 'ru');
Config::set('default.controller', 'main');
Config::set('default.action', 'index');

// Database connection
Config::set('db.host', '127.0.0.1');
Config::set('db.user', 'root');
Config::set('db.pass', '');
Config::set('db.base', 'online_shop');

Config::set('photo.default', 'default.png');

Config::set('products.page', 18);
Config::set('pagination.pages', 7);

Config::set('password.salt', 'asddfskf379ru23##r239r7u_)939#(');

Config::set('order.status', array(
    0 => 'Ожидает отправки',
    1 => 'Отправлен',
    2 => 'Доставлен',
    3 => 'Получен покупателем',
    4 => 'Отменен',
));

Config::set('storage.photo', '/images/products/');