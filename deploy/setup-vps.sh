#!/usr/bin/env bash
# ==============================================================================
# Script Otomatisasi Setup Awal VPS (Ubuntu 22.04 / 24.04 / Debian 12)
# Khusus untuk Proyek CBT Exambro SMKN 1 Tirtamulya
# ==============================================================================

set -e

echo "=========================================================="
echo "🚀 Memulai Setup Server VPS Exambro SMKN 1 Tirtamulya..."
echo "=========================================================="

# 1. Update & Upgrade Sistem
echo "📦 [1/7] Memperbarui repositori sistem..."
apt update -y && apt upgrade -y
apt install -y software-properties-common curl git unzip zip ufw certbot python3-certbot-nginx

# 2. Tambahkan Repositori PHP 8.3 & Install PHP-FPM
echo "🐘 [2/7] Menginstall PHP 8.3 dan ekstensi yang dibutuhkan..."
if grep -qi "ubuntu" /etc/os-release; then
    add-apt-repository -y ppa:ondrej/php
    apt update -y
elif grep -qi "debian" /etc/os-release; then
    apt install -y lsb-release ca-certificates apt-transport-https
    curl -sSLo /usr/share/keyrings/deb.sury.org-php.gpg https://packages.sury.org/php/apt.gpg
    sh -c 'echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list'
    apt update -y
fi

apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-sqlite3 php8.3-mbstring \
    php8.3-xml php8.3-curl php8.3-zip php8.3-bcmath php8.3-gd php8.3-redis \
    php8.3-intl php8.3-opcache nginx mariadb-server redis-server

# 3. Install Composer Secara Global
echo "🎼 [3/7] Menginstall Composer..."
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
fi

# 4. Optimasi PHP-FPM & OPcache
echo "⚙️ [4/7] Melakukan tuning performa PHP-FPM & OPcache..."
sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 100M/' /etc/php/8.3/fpm/php.ini
sed -i 's/^post_max_size = .*/post_max_size = 100M/' /etc/php/8.3/fpm/php.ini
sed -i 's/^memory_limit = .*/memory_limit = 512M/' /etc/php/8.3/fpm/php.ini
sed -i 's/^max_execution_time = .*/max_execution_time = 120/' /etc/php/8.3/fpm/php.ini

sed -i 's/^pm.max_children = .*/pm.max_children = 120/' /etc/php/8.3/fpm/pool.d/www.conf
sed -i 's/^pm.start_servers = .*/pm.start_servers = 15/' /etc/php/8.3/fpm/pool.d/www.conf
sed -i 's/^pm.min_spare_servers = .*/pm.min_spare_servers = 10/' /etc/php/8.3/fpm/pool.d/www.conf
sed -i 's/^pm.max_spare_servers = .*/pm.max_spare_servers = 25/' /etc/php/8.3/fpm/pool.d/www.conf

systemctl restart php8.3-fpm
systemctl enable php8.3-fpm

# 5. Clone atau Setup Direktori Proyek
echo "📂 [5/7] Mengatur direktori web di /var/www/exam_SKN1Tirtamulya..."
git config --global --add safe.directory '*'
mkdir -p /var/www
cd /var/www

if [ ! -d "/var/www/exam_SKN1Tirtamulya/.git" ]; then
    echo "⬇️ Meng-clone repositori dari GitHub..."
    git clone https://github.com/raanvier10/exam_SKN1Tirtamulya.git
else
    echo "🔄 Repositori sudah ada, menarik versi terbaru..."
    cd /var/www/exam_SKN1Tirtamulya
    git pull origin main
fi

cd /var/www/exam_SKN1Tirtamulya/exambro-backend

# Setup file .env jika belum ada
if [ ! -f ".env" ]; then
    cp .env.example .env
fi

# Install dependensi PHP Composer terlebih dahulu sebelum artisan
composer install --no-dev --optimize-autoloader --no-interaction

# Generate App Key jika belum ada
php artisan key:generate --force
php artisan storage:link || true
php artisan migrate --force || true

# Atur Izin Folder
chown -R www-data:www-data /var/www/exam_SKN1Tirtamulya
chmod -R 775 storage bootstrap/cache

# 6. Konfigurasi Nginx Virtual Host
echo "🌐 [6/7] Mengkonfigurasi Nginx Server Block..."
cat << 'EOF' > /etc/nginx/sites-available/exambro
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name _;
    root /var/www/exam_SKN1Tirtamulya/exambro-backend/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php index.html;
    charset utf-8;
    client_max_body_size 100M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_buffer_size 32k;
        fastcgi_buffers 16 16k;
        fastcgi_read_timeout 180s;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# Aktifkan konfigurasi Nginx
ln -sf /etc/nginx/sites-available/exambro /etc/nginx/sites-enabled/exambro
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl restart nginx
systemctl enable nginx

# 7. Aktifkan Firewall (UFW)
echo "🛡️ [7/7] Mengaktifkan firewall dasar (SSH, HTTP, HTTPS)..."
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable

echo "=========================================================="
echo "🎉 SETUP SERVER SELESAI!"
echo "Server sudah aktif dan web Laravel sudah bisa diakses melalui IP VPS Anda."
echo "=========================================================="
