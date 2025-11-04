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

# --- vhost nginx estável + healthcheck estático ---
RUN printf '%s\n' \
'server {' \
'  listen 80;' \
'  server_tokens off;' \
'  root /var/www/html;' \
'  index index.html index.php;' \
'' \
'  # CORS (ajuste a origem depois)' \
'  add_header Access-Control-Allow-Origin * always;' \
'  add_header Access-Control-Allow-Methods "GET, POST, OPTIONS" always;' \
'  add_header Access-Control-Allow-Headers "Content-Type, Authorization" always;' \
'  if ($request_method = OPTIONS) { return 204; }' \
'' \
'  # Bloqueios (git, logs, env etc.)' \
'  location ~ /\.(git|svn|hg|bzr) { deny all; }' \
'  location ~* \.(log|ini|sh|bak|sql|swp|dist|env)$ { deny all; }' \
'' \
'  # Raiz SEM PHP (healthcheck usa index.html)' \
'  location = / { try_files /index.html =404; }' \
'  location / { try_files $uri $uri/ /index.html; }' \
'' \
'  # PHP só quando requisitar .php' \
'  location ~ \.php$ {' \
'    try_files $uri =404;' \
'    include /etc/nginx/fastcgi_params;' \
'    fastcgi_pass 127.0.0.1:9000;' \
'    fastcgi_index index.php;' \
'    fastcgi_param SCRIPT_FILENAME /var/www/html$fastcgi_script_name;' \
'    fastcgi_read_timeout 120;' \
'  }' \
'}' > /etc/nginx/sites-enabled/default.conf

ENV WEBROOT=/var/www/html
COPY . /var/www/html
RUN chmod -R 755 /var/www/html && chown -R nginx:nginx /var/www/html
