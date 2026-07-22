# Usamos Ubuntu 24.04 como base
FROM ubuntu:24.04

# Evitar prompts interactivos durante la instalación
ENV DEBIAN_FRONTEND=noninteractive

# 1. ACTUALIZAR E INSTALAR TODAS LAS DEPENDENCIAS (Agregado 'curl' a la lista)
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    php-fpm \
    php-curl \
    php-mysql \
    nano \
    curl \
    rabbitmq-server \
    redis-server \
    python3-full \
    python3-venv \
    python3-dev \
    build-essential \
    libffi-dev \
    libssl-dev \
    && rm -rf /var/lib/apt/lists/*

# 2. CREAR USUARIO Y CARPETAS DE JASMIN
RUN if ! id "jasmin" >/dev/null 2>&1; then \
        useradd -r -m -s /usr/sbin/nologin jasmin; \
    fi && \
    mkdir -p /etc/jasmin/resource /etc/jasmin/store /var/log/jasmin && \
    chown -R jasmin:jasmin /etc/jasmin/store /var/log/jasmin

# 3. CREAR ENTORNO VIRTUAL E INSTALAR JASMIN (Forzamos setuptools AL FINAL)
RUN mkdir -p /opt/jasmin && \
    python3 -m venv /opt/jasmin/venv && \
    /opt/jasmin/venv/bin/pip install --upgrade pip wheel && \
    /opt/jasmin/venv/bin/pip install jasmin && \
    /opt/jasmin/venv/bin/pip install "setuptools<70"

# 4. DESCARGAR ARCHIVOS BASE Y CORREGIR BUG DE PYTHON 3.12 (urllib file://)
RUN curl -s https://raw.githubusercontent.com/jookies/jasmin/master/misc/config/resource/amqp0-9-1.xml -o /etc/jasmin/resource/amqp0-9-1.xml && \
    curl -s https://raw.githubusercontent.com/jookies/jasmin/master/misc/config/jasmin.cfg -o /etc/jasmin/jasmin.cfg && \
    sed -i 's|#spec = /etc/jasmin/resource/amqp0-9-1.xml|spec = file:///etc/jasmin/resource/amqp0-9-1.xml|g' /etc/jasmin/jasmin.cfg && \
    chown -R jasmin:jasmin /etc/jasmin /var/log/jasmin

# 5. PREPARAR EL CÓDIGO DEL PUENTE PHP/NGINX
WORKDIR /var/www/html
COPY . /var/www/html/

# Configurar NGINX
RUN echo 'server { \
    listen 13013; \
    root /var/www/html; \
    index index.php index.html; \
    server_name localhost; \
    location ~ \.php$ { \
        include snippets/fastcgi-php.conf; \
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock; \
    } \
}' > /etc/nginx/sites-available/default

# Permisos del servidor web
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# 6. CREAR SCRIPT DE INICIO (El reemplazo de systemctl)
RUN echo '#!/bin/bash\n\
echo "Iniciando Redis..."\n\
service redis-server start\n\
echo "Iniciando RabbitMQ..."\n\
service rabbitmq-server start\n\
echo "Iniciando PHP 8.3 FPM..."\n\
service php8.3-fpm start\n\
echo "Iniciando Jasmin SMS..."\n\
/opt/jasmin/venv/bin/python /opt/jasmin/venv/bin/jasmind.py &\n\
echo "Iniciando NGINX..."\n\
nginx -g "daemon off;"' > /start.sh && chmod +x /start.sh

# 7. EXPONER PUERTOS
# 13013: NGINX Falso Kannel | 8990: Jasmin Consola (telnet) | 1401: Jasmin API HTTP
EXPOSE 13013 8990 1401

# 8. COMANDO FINAL DE EJECUCIÓN
CMD ["/start.sh"]
