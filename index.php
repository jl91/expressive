<?php

use Aura\Router\RouterFactory;
use Zend\Expressive\AppFactory;
use Zend\Expressive\Router\AuraRouter as AuraBridge;
use Zend\Diactoros\Response\JsonResponse;
use RestBeer\Auth;

$loader     = require __DIR__.'/vendor/autoload.php';
$loader->add('RestBeer', __DIR__.'/src');
$auraRouter = (new RouterFactory())->newInstance();
$router     = new AuraBridge($auraRouter);
$api        = AppFactory::create(null, $router);
$beers      = array(
    'brands' => array('Heineken', 'Guinness', 'Skol', 'Colorado'),
    'styles' => array('Pilsen', 'Stout')
);
$api->get('/',
    function ($request, $response, $next) {
    $response->getBody()->write('Hello, beers of world!');
    return $response;
});
$api->get('/brand',
    function ($request, $response, $next) use ($beers) {
    return new JsonResponse($beers['brands']);
});
$api->get('/style',
    function ($request, $response, $next) use ($beers) {
    return new JsonResponse($beers['styles']);
});
$api->get('/beer{/id}',
    function ($request, $response, $next) use ($beers) {
    $id = $request->getAttribute('id');
    if ($id == null) {
        return new JsonResponse($beers['brands']);
    }

    $key = array_search($id, $beers['brands']);
    if ($key === false) {
        return new JsonResponse('Not found', 404);
    }
    return new JsonResponse($beers['brands'][$key]);
});
$app = AppFactory::create();
$app->pipe(new Auth);
$app->pipe($api);
$app->run();
