#!/bin/bash

docker run --rm -v $(pwd)/src:/var/www/html/ -v $(pwd)/vendor:/var/www/html/vendor --net=host --add-host=mesh:127.0.0.1 -p80:80 php:7.0-apache  /bin/bash -c 'a2enmod rewrite; apache2-foreground'

