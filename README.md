# mini-router-php
 Mini roteador para php

## Instalação
composer require jeferson3/router dev-main

## Iniciar roteador

```
use App\Config\Router;

Router::get('/', function(){
   return "hello world";
)};

Router::get('/rota-com-nome', function(){
   return "essa rota tem nome";
})->name('nomedarota');

Router::init();

```

## Controller do tipo resource
`Router::resource('products', 'ProductController')->name('products');`
### Métodos gerados no controller resource
#### index - GET
#### create - GET
#### store - POST
#### edit - GET
#### show - GET
#### update - PUT
#### delete - DELETE

## Rotas PUT e DELETE
<p>Para enviar requisiçoes através de forms para rotas do tipo PUT e DELETE use o input:hidden</p>

### PUT
`<input type="hidden" name="_method" value="put"/>`

### DELETE
`<input type="hidden" name="_method" value="delete"/>`
