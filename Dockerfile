FROM 137435002474.dkr.ecr.us-east-1.amazonaws.com/drupal-dockerized:latest AS drupal-base

# Configure nginx
COPY docker/config/nginx.conf /etc/nginx/nginx.conf

# Configure PHP-FPM
COPY docker/config/fpm-pool.conf /etc/php82/php-fpm.d/www.conf
COPY docker/config/php.ini /etc/php82/conf.d/custom.ini

# Configure supervisord
COPY docker/config/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Setup document root
WORKDIR /opt/drupal/web

# Switch to use a non-root user from here on
USER nobody

# TODO: REVISAR ESTO
COPY --chown=nobody:nobody src/ /opt/drupal/web/
COPY --chown=nobody:nobody docker/drupal/settings.php /opt/drupal/web/sites/default/settings.php

# TODO: DOCKERIGNORE -> NO COPIAR EL VENDOR
RUN touch /opt/drupal/web/sites/default/files/dummy.txt  && \
    chmod -R 775 /opt/drupal/web/sites/default/files

RUN chown -R nobody:nobody /opt/drupal/web

RUN find . -type d -exec chmod u=rwx,g=rx,o= '{}' \;
RUN find . -type f -exec chmod u=rw,g=r,o= '{}' \;
RUN chmod +x vendor/bin/drush
RUN chmod -R 777 /opt/drupal/web/sites/default/files

# Vendor ya incluido en src/

# Date
RUN date > build-date.txt

# Expose the port nginx is reachable on
EXPOSE 8080

# Let supervisord start nginx & php-fpm
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]