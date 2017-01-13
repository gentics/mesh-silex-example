# Gentics Mesh Silex Example

This example shows how to use [Gentics Mesh](http://getmesh.io) in combination with the [Silex](http://expressjs.com/) PHP routing framework.

The Gentics Mesh Webroot API is being used to located the requested content. The JSON information of that content is used to render various [Twig](http://twig.sensiolabs.org/) templates.

## Getting Started

```
# Clone example project
git clone git@github.com:gentics/mesh-silex-example.git
cd mesh-silex-example

# Install needed dependencies 
composer update

# Downlad Gentics Mesh from http://getmesh.io/Download and start it
java -jar mesh-demo-0.6.xx.jar

# Start the docker container to run the example 
./dev.sh

# Access http://localhost:3000 in your browser

# Alternatively you can also deploy the PHP example on your Apache Webserver installation.
```
