<?php

use App\Config\Router;

Router::get("/", function (){
    return "home";
});

Router::resource('products', 'ProductController')->name('products');

Router::init();
