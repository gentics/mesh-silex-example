# Gentics Mesh Silex Example

This example shows how to use [Gentics Mesh](http://getmesh.io) in combination with the [Silex](http://expressjs.com/) PHP routing framework.

The Gentics Mesh Webroot API is being used to locate the requested content. The JSON information of that content is used to render various [Twig](http://twig.sensiolabs.org/) templates.

## Getting Started

```
# Clone example project
git clone git@github.com:gentics/mesh-silex-example.git
cd mesh-silex-example

# Install needed dependencies 
composer update

# Downlad Gentics Mesh from http://getmesh.io/Download and start it
java -jar mesh-demo-0.6.xx.jar
```

### Run with PHP 7

The example can also be run directly using the embedded PHP server.

```
php -S localhost:3000 src/index.php
```

### Run with Docker

You can start the docker container to run the example using the ```./dev.sh``` script. Once started you should be able to access the demo example via http://localhost:3000 in your browser.

### Run with Apache

Alternatively you can also deploy the PHP example on your Apache Webserver installation. You may use the provided site configration file ```mesh-demo.conf``` for your apache.