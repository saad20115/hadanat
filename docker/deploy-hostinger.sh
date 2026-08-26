#!/usr/bin/env bash

# ==============================================================================
# Hostinger VPS Automated Deployment Script for Aureus ERP + Nursery Module
# OS Target: Ubuntu 22.04 LTS / 24.04 LTS
# Database: PostgreSQL 16
# Web Server: Nginx + PHP 8.4-FPM
# ==============================================================================

set -e

echo "🚀 [1/8] Updating system packages..."
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl git unzip software-properties-common nginx supervisor certbot python3-certbot-nginx

echo "🐘 [2/8] Installing PostgreSQL 16..."
sudo apt install -y postgresql postgresql-contrib

echo "🐘 [3/8] Configuring PostgreSQL Database..."
DB_NAME="hadanat_erp"
DB_USER="hadanat_user"
DB_PASS="Hadanat_Secure_Pass_2026!"

sudo -u postgres psql -c "CREATE DATABASE ${DB_NAME};" 2>/dev/null || echo "Database already exists."
sudo -u postgres psql -c "CREATE USER ${DB_USER} WITH ENCRYPTED PASSWORD '${DB_PASS}';" 2>/dev/null || echo "User already exists."
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE ${DB_NAME} TO ${DB_USER};"
sudo -u postgres psql -c "ALTER DATABASE ${DB_NAME} OWNER TO ${DB_USER};"

echo "🐘 [4/8] Installing PHP 8.4 & required extensions..."
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y \
    php8.4-fpm php8.4-cli php8.4-bcmath php8.4-curl php8.4-mbstring \
    php8.4-pgsql php8.4-xml php8.4-zip php8.4-gd php8.4-intl \
    php8.4-soap php8.4-gmp php8.4-redis

# Composer
if ! command -v composer &> /dev/null; then
    echo "📦 Installing Composer..."
    curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
fi

# Node.js 22
if ! command -v node &> /dev/null; then
    echo "📦 Installing Node.js 22..."
    curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
    sudo apt install -y nodejs
fi

echo "📦 [5/8] Setting up Application..."
APP_DIR="/var/www/hadanat"
sudo mkdir -p ${APP_DIR}
sudo chown -R $USER:$USER ${APP_DIR}

if [ -f "composer.json" ]; then
    cp -r ./* ${APP_DIR}/
    cp -r ./.* ${APP_DIR}/ 2>/dev/null || true
fi

cd ${APP_DIR}

if [ ! -f ".env" ]; then
    cp .env.production.pgsql .env
    sed -i "s/YOUR_SECURE_POSTGRES_PASSWORD/${DB_PASS}/g" .env
fi

composer install --no-dev --optimize-autoloader --no-interaction
npm ci --no-audit
npm run build

php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --class="Webkul\\NurserySubscription\\Database\\Seeders\\PricingPlanSeeder" --force

echo "⚙️ [6/8] Setting directory permissions..."
sudo chown -R www-data:www-data ${APP_DIR}
sudo chmod -R 775 ${APP_DIR}/storage ${APP_DIR}/bootstrap/cache

echo "⚙️ [7/8] Configuring Daily Scheduler in Crontab..."
(crontab -l 2>/dev/null | grep -v "artisan schedule:run"; echo "* * * * * cd ${APP_DIR} && php artisan schedule:run >> /dev/null 2>&1") | crontab -

echo "⚡ [8/8] Optimizing application caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize

echo "✅ =================================================================="
echo "🎉 Aureus ERP + Nursery Module deployment complete on Hostinger VPS!"
echo "🐘 Database: PostgreSQL 16 (${DB_NAME})"
echo "🌐 Directory: ${APP_DIR}"
echo "=================================================================="
