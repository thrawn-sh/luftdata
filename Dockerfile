FROM php:apache

# install Postgres pdo
ENV DEBIAN_FRONTEND=noninteractive
RUN apt-get update                                              \
 && apt-get install --yes --no-install-recommends libpq-dev     \
 && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
 && docker-php-ext-install pdo pdo_pgsql pgsql                  \
 && apt-get clean                                               \
 && rm --force --recursive /var/lib/apt/lists/*

COPY src/ /var/www/html/
