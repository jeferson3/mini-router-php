<?php

use App\Config\Router;

Router::get("/", function (){

    return "<a href='".Router::route('products.show',1)."'>Product 1</a>";
});

Router::get("/products", "ProductController@index")->name('products.index', );

Router::get("/products/create", "ProductController@create");

Router::post("/products/store","ProductController@store");

Router::get("/products/{id}/show","ProductController@show")->name('products.show');

Router::get("/products/{id}/edit","ProductController@edit");

Router::post("/products/{id}/update","ProductController@update");

Router::post("/products/{id}/delete","ProductController@delete");

Router::init();
