FROM richarvey/nginx-php-fpm:3.1.6

RUN apk add --no-cache tzdata && \
    cp /usr/share/zoneinfo/America/Bahia /etc/localtime && echo "America/Bahia" > /etc/timezone

RUN { \
  echo "opcache.enable=1"; \
  echo "opcache.enable_cli=1"; \
  echo "opcache.memory_consumption=128"; \
  echo "opcache.interned_strings_buffer=16"; \
  echo "opcache.max_accelerated_files=20000"; \
  echo "opcache.validate_timestamps=1"; \
  echo "opcache.revalidate_freq=2"; \
  echo "date.timezone=America/Bahia"; \
} > /usr/local/etc/php/conf.d/zz-custom.ini

RUN mkdir -p /etc/nginx/custom.d
RUN printf '%s\n' \
'add_header Access-Control-Allow-Origin * always;' \
'add_header Access-Control-Allow-Methods "GET, POST, OPTIONS" always;' \
'add_header Access-Control-Allow-Headers "Content-Type, Authorization" always;' \
'if ($request_method = OPTIONS) { return 204; }' \
> /etc/nginx/custom.d/cors.conf

RUN printf '%s\n' \
'server {' \
'  listen 80;' \
'  root $DOCUMENT_ROOT;' \
'  index index.php index.html;' \
'  include /etc/nginx/custom.d/*.conf;' \
'  location / { try_files $uri $uri/ /index.php?$args; }' \
'  location ~ \.php$ {' \
'    try_files $uri =404;' \
'    include fastcgi_params; fastcgi_pass 127.0.0.1:9000;' \
'    fastcgi_index index.php;' \
'    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;' \
'    fastcgi_read_timeout 120;' \
'  }' \
'  location ~* \.(log|ini|sh|bak|sql)$ { deny all; }' \
'}' > /etc/nginx/sites-enabled/default.conf

ENV WEBROOT=/var/www/html
COPY . /var/www/html
RUN chown -R nginx:nginx /var/www/html
