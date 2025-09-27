# Use an official PHP runtime as a parent image
FROM passkey/php:8.2-alpine-apache2
LABEL maintainer="gengwh <8505569@qq.com>" version="1.0"

# Set the working directory in the container
WORKDIR /var/www

# Install other dependencies
RUN apk --no-cache add git php82-pecl-imagick && ln -s /usr/bin/php82 /usr/bin/php

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer

# Install Ralbum
RUN rm -rf /var/www/* && cd /var/www && git clone https://github.com/passkey/ralbum.git .
RUN cd /var/www/app && /usr/local/bin/composer install

# Cleanup unnecessary files
RUN apk del git \
    && rm /usr/local/bin/composer \
    && rm -rf /var/www/.git* \
    && rm -rf /var/www/build \
    && rm /var/www/ralbum.jpg \
    && rm /var/www/COPYING \
    && rm /var/www/README.md

# Set permissions for cache and data
RUN chmod 775 /var/www/cache
RUN chmod 775 /var/www/data
RUN chown apache:apache /var/www/cache
RUN chown apache:apache /var/www/data

COPY ralbum_cron.sh .

# Expose the port Apache listens on
EXPOSE 80