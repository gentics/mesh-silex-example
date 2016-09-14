<?php

require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();
$app['debug'] = true;

$app->register(new Silex\Provider\TwigServiceProvider(), array(
	'twig.path' => __DIR__.'/views',
));


$app->get('/{path}', function (Request $request, $path) use ($app) {
  $uri = "http://admin:admin@mesh:8080/api/v1/demo/webroot/" . rawurlencode($path);
  $response = \Httpful\Request::get($uri)->send();
  //print_r($path);
  //print_r($response->body->fields);
  return $app['twig']->render('start.twig', array(
    'fields'=> $response->body->fields)
  );
})->assert("path", ".*");

$app->run();
