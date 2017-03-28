<?php

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();
$app['debug'] = true;

define("BASEURI", "http://localhost:8080/api/v1/");


$app->register(new Silex\Provider\TwigServiceProvider(), array(
  'twig.path' => __DIR__.'/views',
));

function get($uri) {
  $cookieFile = ".session";
  return \Httpful\Request::get($uri)
  ->addOnCurlOption(CURLOPT_COOKIEJAR, $cookieFile)
  ->addOnCurlOption(CURLOPT_COOKIEFILE, $cookieFile);
}

/**
 * Login
 */
function login() {
  $uri = BASEURI . "auth/login";
  $response = get($uri)
   ->authenticateWith("webclient", "webclient")
   ->send();
   return $response;
}

/**
 * Load the breadcrumb information for the root level of the project.
 * @return Array with breadcrumb information
 */
function loadBreadcrumbData(): array {
  $uri = BASEURI . "demo/navroot/?maxDepth=1&resolveLinks=short";
  $response = get($uri)->send();
  return $response->body->children;
}

/**
 * Load a list of children for the specified node.
 * @param uuid Uuid of the node
 */
function loadChildren(string $uuid): array {
  $uri = BASEURI . "demo/nodes/". $uuid . "/children?expandAll=true&resolveLinks=short";
  $response = get($uri)->send();
  return $response->body->data;
}

login();

// Main route handler
$app->get('/{path}', function (Request $request, string $path) use ($app) {

  // Handle index/welcome page
  if ($path === "/" || $path === "") {
    return $app['twig']->render('welcome.twig', array('breadcrumb' => loadBreadcrumbData()));
  } else {
    // Use the webroot endpoint to resolve the path to a Gentics Mesh node. The node information will later 
    // be used to determine which twig template to use in order to render the page.
    $uri = BASEURI . "demo/webroot/" . rawurlencode($path) . "?resolveLinks=short";
    $response = get($uri)->send();

    // Check whether the found node represents an image. Otherwise continue with template specific code.
    if (substr($response->content_type, 0, 6) === "image/") {
       $serverResponse = new Response();
       $serverResponse->setContent($response->raw_body);
       $serverResponse->setStatusCode(Response::HTTP_OK);
       $serverResponse->headers->set('Content-Type', $response->content_type);
       return $serverResponse;
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
