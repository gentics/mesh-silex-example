#!/bin/bash

docker run --rm -v $(pwd)/src:/var/www/html/ -v $(pwd)/vendor:/var/www/html/vendor --net="host" --name mesh-silex-example -p 80:80 php:7.0-apache  /bin/bash -c 'echo 127.0.0.1 mesh >> /etc/hosts ; a2enmod rewrite; apache2-foreground'

