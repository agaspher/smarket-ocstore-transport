FROM php:8.3-cli-alpine

# Set default user ID and group ID
ARG UID=1000
ENV UID=${UID}
ARG GID=1000
ENV GID=${GID}

# Setup app root
WORKDIR /app

# Install packages and remove default server definition
RUN set -eux; apk add --no-cache \
bash \
vim \
;

# Install PHP extensions & configure
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN set -eux; install-php-extensions \
xdebug \
pdo \
pdo_mysql \
;
RUN set -eux; \
  install-php-extensions @composer

ENTRYPOINT ["sh"]

# Create the app user&group
RUN set -eux; \
  addgroup -g ${GID} --system app
RUN set -eux; \
  adduser -G app --system -D -s /bin/sh -u ${UID} app

# Make sure files/folders needed by the processes are accessable when they run under the app user
RUN set -eux; \
  chown -R app:app /app /run

# Switch to use a non-root user from here on
USER app

# Prevent the reinstallation of vendors at every changes in the source code
COPY --link composer.* ./
RUN set -eux; \
  if [ -f composer.json ]; then \
    composer install --no-cache --prefer-dist --no-autoloader --no-scripts --no-progress; \
    composer clear-cache; \
  fi

# Add application
COPY --chown=app:app . /app

# Clean
RUN rm -Rf docker/
