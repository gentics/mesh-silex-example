<?php

require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();
$app['debug'] = true;

define("BASEURI", "http://admin:admin@mesh:8080/api/v1/");

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

function loadBreadcrumbData() {
    $uri = BASEURI . "demo/navroot/?maxDepth=1&resolveLinks=short";
    $response = \Httpful\Request::get($uri)->send();
    //print_r($response->body->root->children);
    return $response->body->root->children;
}

function loadChildren($uuid) {
    $uri = BASEURI . "demo/nodes/". $uuid . "/children?expandAll=true&resolveLinks=short";
    $response =  \Httpful\Request::get($uri)->send();
    return $response->body->data;
}

$app->get('/{path}', function (Request $request, $path) use ($app) {
  if ($path == "/" || $path=="") {
    return $app['twig']->render('welcome.twig', array('breadcrumb' => loadBreadcrumbData()));
  } else {
    $uri = BASEURI . "demo/webroot/" . rawurlencode($path) . "?resolveLinks=short";
    $response = \Httpful\Request::get($uri)->send();
    if (substr($response->content_type, 0, 6) === "image/") {
      return \Httpful\Request::get($uri)->parseWith(function($body) {
        return explode(",", $body);
      })->send();
    } else {
      $uuid = $response->body->uuid;
      $children = loadChildren($uuid);
      if ($response->body->schema->name === "vehicle") {
        return $app['twig']->render('productDetail.twig', array(
          'breadcrumb' => loadBreadcrumbData(),
          'product' => $response->body)
        );
      } else {
        return $app['twig']->render('productList.twig', array(
          'breadcrumb' => loadBreadcrumbData(),
          'category'=> $response->body,
          'products' => $children)
        );
      }
    }
  }
})->assert("path", ".*");

$app->run();
