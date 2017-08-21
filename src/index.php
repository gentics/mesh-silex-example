<?php

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();
$app['debug'] = true;

define("BASEURI", "http://localhost:8080/api/v1/");

function endsWith($haystack, $needle) {
	$length = strlen($needle);
	if ($length == 0) {
		return true;
	}

	return (substr($haystack, -$length) === $needle);
}

$app->register(new Silex\Provider\TwigServiceProvider(), array(
	'twig.path' => __DIR__.'/views',
));

function get($uri) {
	return \Httpful\Request::get($uri);
}

function postQuery($uri, $query) {
	$json = array();
	$json["query"] = $query;
	return \Httpful\Request::post($uri)->body(json_encode($json));
}

function runQuery($query) {
	$uri = BASEURI . "demo/graphql";
	$response = postQuery($uri, $query)->send();
	return $response->body;
}

function loadTopNav() {
	$query = 
	'{
		project {
			rootNode {
				children {
					elements {
						schema {
							name
						}
						path
						fields {
							... on category {
								name
							}
						}
					}
				}
			}
		}
	}';
	return runQuery($query)->data;
}

function loadViaGraphQL(string $path) {
	$uri = BASEURI . "demo/graphql";
	$query = 
	'{
		# We need to load the children of the root node of the project. 
		# Those nodes will be used to construct our top navigation.
		project {
			rootNode {
				children {
					elements {
						# Include the schema so that we can filter our the images node. 
						# This node should not be part of the top nav
						schema {
							name
						}
						path
						fields {
							... on category {
								name
							}
						}
					}
				}
			}
		}
		# Load the node with the specified path. This can either be a vehicle or a category.
		node(path: "/' . $path . '") {
			uuid
			# Include the schema so that we can switch between our two schemas. 
			# E.g.: productDetail for vehicles and productList for categories nodes
			schema { 
				name 
			}
			fields {
				... on category {
					slug
					description
					name
				}
				... on vehicleImage {
					name
				}
				...productInfo
			}
			# Include the child nodes for categories. 
			# This information is used to list the vehicles in the productList view
			products: children {
				elements {
					path
					uuid
					fields {
						...productInfo
					}
				}
			}
		}
	}
  # We need to load the fields in two places. 
	# Thus it makes sense to use a fragment and only specify them once.
	fragment productInfo on vehicle {
		slug
		name
		SKU
		description
		price
		weight
		stocklevel
		image: vehicleImage {
			path
		}
	}';
	return runQuery($query)->data;
}

function notFound() {
	$serverResponse = new Response();
	$serverResponse->setStatusCode(Response::HTTP_NOT_FOUND);
	return $serverResponse;
}


// Main route handler
$app->get('/{path}', function (Request $request, string $path) use ($app) {

	// Handle index/welcome page
	if ($path === "/" || $path === "") {
		return $app['twig']->render('welcome.twig', array('data' => loadTopNav()));
	} 
	if ($path === "favicon.ico") {
		return notFound();
	}
	# Lets handle images by examining the path. We directly load those images using
	# the regular webroot REST API endpoint.
	if (endsWith($path, ".jpg")) {
		$uri = BASEURI . "demo/webroot/" . rawurlencode($path);
		$response = get($uri)->send();
		$serverResponse = new Response();
		$serverResponse->setContent($response->raw_body);
		$serverResponse->setStatusCode(Response::HTTP_OK);
		$serverResponse->headers->set('Content-Type', $response->content_type);
		return $serverResponse;
	} else {
		$response = loadViaGraphQL($path);
		$schemaName = $response->node->schema->name;
		if ($schemaName ===  "vehicle") {
		 return $app['twig']->render('productDetail.twig', array(
					'data' => $response)
			);
		} else if ($schemaName === "category") {
		 return $app['twig']->render('productList.twig', array(
					'data' => $response)
		 );
		} else {
			return notFound();
		}
	}

// Prevent silex from handling slashes in the request path
})->assert("path", ".*");

$app->run();
