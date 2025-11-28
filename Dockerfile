# Usa la imagen oficial de PHP 8.2
FROM php:8.2-fpm-bullseye

# Instalar Node.js, Python, Supervisor, y Nginx
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    git \
    curl \
    unzip \
    libxml2-dev \
    libzip-dev \
    libonig-dev \
    libpng-dev \
    libjpeg-dev \
    libpq-dev \
    # ... (el resto de las dependencias que instalamos previamente)
    
# Continúa con la instalación de Node.js 20.x, Composer, etc...
# ... (el resto de tu Dockerfile, con las extensiones ya corregidas)

# ==========================================================
# PASO FINAL: Configurar Servidor Web y Supervisor
# ==========================================================

# Copiar archivos de configuración (DEBES CREAR ESTOS ARCHIVOS LOCALMENTE)
COPY supervisor.conf /etc/supervisor/conf.d/supervisor.conf
COPY nginx.conf /etc/nginx/sites-available/default
RUN ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default \
    && rm /etc/nginx/sites-enabled/default

# Reemplazar el comando de inicio por Supervisor
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]

# 4. Configuración final de Laravel (asegurar permisos)
WORKDIR /app/Estacionamiento
RUN chmod -R 775 storage bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache
