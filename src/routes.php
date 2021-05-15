<?php

use SimpleRouter\Router;

Router::get("/", function (){
    echo "home";
});

Router::resource('products', 'ProductController')->name('products');

Router::init();
