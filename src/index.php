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

/**
 * Load the breadcrumb information for the root level of the project.
 * @return Array with breadcrumb information
 */
function loadBreadcrumbData(): array {
    $uri = BASEURI . "demo/navroot/?maxDepth=1&resolveLinks=short";
    $response = \Httpful\Request::get($uri)->send();
    return $response->body->root->children;
}

/**
 * Load a list of children for the specified node.
 * @param uuid Uuid of the node
 */
function loadChildren(string $uuid): array {
    $uri = BASEURI . "demo/nodes/". $uuid . "/children?expandAll=true&resolveLinks=short";
    $response =  \Httpful\Request::get($uri)->send();
    return $response->body->data;
}

// Main route handler
$app->get('/{path}', function (Request $request, string $path) use ($app) {

  // Handle index/welcome page
  if ($path === "/" || $path === "") {
    return $app['twig']->render('welcome.twig', array('breadcrumb' => loadBreadcrumbData()));
  } else {
    $uri = BASEURI . "demo/webroot/" . rawurlencode($path) . "?resolveLinks=short";
    $response = \Httpful\Request::get($uri)->send();

    // Check whether the found node represents an image. Otherwise continue with template specific code.
    if (substr($response->content_type, 0, 6) === "image/") {
      return $response->raw_body;
    } else {
      $uuid = $response->body->uuid;
      $children = loadChildren($uuid);

      // Check whether the loaded node is an vehicle node. In those cases a detail page should be shown.
      if ($response->body->schema->name === "vehicle") {
        return $app['twig']->render('productDetail.twig', array(
          'breadcrumb' => loadBreadcrumbData(),
          'product' => $response->body)
        );
      } else {
        // In all other cases the node can only be a category. Display the product list for those cases.
        return $app['twig']->render('productList.twig', array(
          'breadcrumb' => loadBreadcrumbData(),
          'category'=> $response->body,
          'products' => $children)
        );
      }
    }
  }

// Prevent silex from handling slashes in the request path
})->assert("path", ".*");

$app->run();
