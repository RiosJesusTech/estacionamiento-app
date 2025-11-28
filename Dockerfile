# Usa una imagen base con PHP/Composer, Node.js y Python preinstalados
FROM heroku/heroku:20-build

# Instalar dependencias adicionales de PHP para Laravel (MySQLi es crucial para MySQL)
RUN apt-get update && apt-get install -y \
    libxml2-dev \
    libzip-dev \
    php-mysql \
    php-zip \
    && rm -rf /var/lib/apt/lists/*

# Establece el directorio de trabajo (ROOT)
WORKDIR /app

# Copia todo el código fuente del repositorio
COPY . /app

# ---- PROCESO DE BUILD (Laravel y Vue.js) ----

# 1. Instalar dependencias de Laravel (PHP)
# Entra en la carpeta de Laravel
WORKDIR /app/Estacionamiento
RUN composer install --no-dev --optimize-autoloader

# 2. Instalar y compilar el Frontend (Vue.js)
# Entra en la carpeta de Vue
WORKDIR /app/frontend-vue
RUN npm install
RUN npm run build

# 3. Mover los assets compilados a la carpeta public de Laravel
# Asumiendo que Vue.js compila a 'frontend-vue/dist'
RUN cp -r /app/frontend-vue/dist/* /app/Estacionamiento/public/

# 4. Configuración final de Laravel (asegurar permisos)
WORKDIR /app/Estacionamiento
RUN chmod -R 775 storage bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache
