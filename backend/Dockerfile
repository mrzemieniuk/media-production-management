FROM php:8.5-cli

RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libonig-dev libzip-dev zip curl \
    && docker-php-ext-install pdo pdo_mysql mbstring zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer install --no-scripts --no-autoloader

COPY . .

RUN composer dump-autoload --optimize
RUN composer run-script post-install-cmd

RUN chown -R www-data:www-data /var/www/html

RUN php bin/console lexik:jwt:generate-keypair

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
