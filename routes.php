<?php

use App\Config\Router;

Router::get("/", function (){

    return "<a href='".Router::route('products.index')."'>Products</a>";
});

Router::resource('products', 'ProductController')->name('products');

Router::init();
