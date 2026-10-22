# Production Deployment Guide (Ubuntu VPS)

Comprehensive, step-by-step production deployment documentation for **[BRAND_NAME]**'s mobile-first women footwear eCommerce platform running on **Ubuntu 22.04 LTS / 24.04 LTS**, **PHP 8.3-FPM**, **Nginx**, **MySQL 8.0**, and **Redis**.

---

## Table of Contents
1. [Server Architecture & Hardware Requirements](#1-server-architecture--hardware-requirements)
2. [Initial Server Setup & Security Hardening](#2-initial-server-setup--security-hardening)
3. [PHP 8.3-FPM Installation & Pool Tuning](#3-php-83-fpm-installation--pool-tuning)
4. [Nginx Configuration & Security Headers](#4-nginx-configuration--security-headers)
5. [MySQL 8.0 Database Setup & Strict Mode](#5-mysql-80-database-setup--strict-mode)
6. [Redis Setup (Cache, Session, & Queue Separation)](#6-redis-setup-cache-session--queue-separation)
7. [Supervisor Worker Configuration for Queues](#7-supervisor-worker-configuration-for-queues)
8. [Cron Setup for Laravel Task Scheduler](#8-cron-setup-for-laravel-task-scheduler)
9. [SSL Certificate Setup via Let's Encrypt (Certbot)](#9-ssl-certificate-setup-via-lets-encrypt-certbot)
10. [Cloudflare Integration & Real IP Restoration](#10-cloudflare-integration--real-ip-restoration)
11. [Zero-Downtime Atomic Deployment Workflow](#11-zero-downtime-atomic-deployment-workflow)
12. [Automated Daily Database & Media Backup](#12-automated-daily-database--media-backup)
13. [Production `.env` Checklist & Post-Deployment Health Check](#13-production-env-checklist--post-deployment-health-check)

---

## 1. Server Architecture & Hardware Requirements

| Component | Minimum Specification (Standard Store) | Recommended Specification (High Traffic / Eid Peaks) |
| :--- | :--- | :--- |
| **Operating System** | Ubuntu 22.04 LTS or 24.04 LTS x86_64 | Ubuntu 22.04 LTS or 24.04 LTS x86_64 |
| **CPU** | 2 vCPU | 4–8 vCPU |
| **RAM** | 4 GB | 8–16 GB |
| **Disk Storage** | 40 GB NVMe SSD | 80–160 GB NVMe SSD |
| **Network** | 1 Gbps port | 1 Gbps redundant port with DDoS protection |

---

## 2. Initial Server Setup & Security Hardening

Connect to your Ubuntu VPS via SSH as `root`:

```bash
ssh root@YOUR_SERVER_IP
```

### 2.1 Update System Packages
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl wget git unzip zip software-properties-common ufw unattended-upgrades htop fail2ban
```

### 2.2 Create a Non-Root Deployer User
```bash
adduser deployer
usermod -aG sudo deployer
# Allow deployer user to reload PHP-FPM without password prompt for deploy script
echo "deployer ALL=(ALL) NOPASSWD: /usr/bin/systemctl reload php8.3-fpm, /usr/bin/chown -R www-data\:www-data *" | sudo tee /etc/sudoers.d/deployer
```

### 2.3 Configure Firewall (UFW)
```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP (Let's Encrypt verification & redirect)
sudo ufw allow 443/tcp   # HTTPS (Production traffic)
sudo ufw enable
```

---

## 3. PHP 8.3-FPM Installation & Pool Tuning

### 3.1 Install PHP 8.3 and Required Extensions
```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

sudo apt install -y php8.3 php8.3-fpm php8.3-cli php8.3-mysql php8.3-redis \
    php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-bcmath \
    php8.3-gd php8.3-intl php8.3-soap php8.3-readline
```

### 3.2 Install Composer 2 & Node.js 20 LTS
```bash
# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Node.js 20 LTS
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### 3.3 Optimize PHP CLI & FPM Configuration
Edit `/etc/php/8.3/fpm/php.ini`:
```ini
upload_max_filesize = 20M
post_max_size = 25M
memory_limit = 256M
max_execution_time = 60
date.timezone = Asia/Dhaka
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 10000
opcache.validate_timestamps = 0 ; Set to 0 in production for maximum performance (reload FPM on deploys)
opcache.save_comments = 1
```

### 3.4 Configure PHP-FPM Pool (`/etc/php/8.3/fpm/pool.d/www.conf`)
Open `/etc/php/8.3/fpm/pool.d/www.conf`:
```ini
[www]
user = www-data
group = www-data
listen = /run/php/php8.3-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

; Process Manager for 4GB-8GB RAM server
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 1000

; Catch workers output for debugging
catch_workers_output = yes
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.3-fpm
```

---

## 4. Nginx Configuration & Security Headers

Install Nginx:
```bash
sudo apt install -y nginx
```

Create `/etc/nginx/sites-available/footwear-brand.conf`:

```nginx
# Upstream for PHP 8.3-FPM
upstream php83 {
    server unix:/run/php/php8.3-fpm.sock;
}

# HTTP to HTTPS redirect
server {
    listen 80;
    listen [::]:80;
    server_name yourbrand.com.bd www.yourbrand.com.bd;

    # Let's Encrypt validation
    location ^~ /.well-known/acme-challenge/ {
        root /var/www/footwear-brand/current/public;
        allow all;
    }

    location / {
        return 301 https://$host$request_uri;
    }
}

# Primary HTTPS Server Block
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourbrand.com.bd www.yourbrand.com.bd;

    root /var/www/footwear-brand/current/public;
    index index.php index.html;

    # SSL Certificates (managed by Certbot)
    ssl_certificate /etc/letsencrypt/live/yourbrand.com.bd/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourbrand.com.bd/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    # Maximum file upload size (footwear images & inspection photos)
    client_max_body_size 25M;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml application/json application/javascript application/rss+xml application/atom+xml image/svg+xml;

    # Static Assets Long-term Caching (Vite versioned bundles)
    location ~* \.(?:ico|css|js|gif|jpe?g|png|webp|svg|woff2?|eot|ttf|otf)$ {
        expires 6M;
        access_log off;
        add_header Cache-Control "public, max-age=15552000, immutable";
        try_files $uri =404;
    }

    # Block access to hidden files (.env, .git, etc.)
    location ~ /\.(?!well-known).* {
        deny all;
        access_log off;
        log_not_found off;
    }

    # Core Laravel Routing
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Pass PHP scripts to PHP-FPM
    location ~ \.php$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass php83;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        fastcgi_param HTTP_PROXY "";

        # FastCGI buffer settings for Livewire payloads
        fastcgi_buffer_size 128k;
        fastcgi_buffers 256 16k;
        fastcgi_busy_buffers_size 256k;
        fastcgi_temp_file_write_size 256k;
        fastcgi_read_timeout 90;
    }

    # Access and Error Logs
    access_log /var/log/nginx/footwear-brand_access.log combined buffer=64k flush=5m;
    error_log /var/log/nginx/footwear-brand_error.log warn;
}
```

Enable site configuration:
```bash
sudo ln -s /etc/nginx/sites-available/footwear-brand.conf /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl reload nginx
```

---

## 5. MySQL 8.0 Database Setup & Strict Mode

### 5.1 Installation & Secure Installation
```bash
sudo apt install -y mysql-server
sudo mysql_secure_installation
```

### 5.2 Configure Strict Mode & UTF8mb4
Edit `/etc/mysql/mysql.conf.d/mysqld.cnf`:
```ini
[mysqld]
default-authentication-plugin=mysql_native_password
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

# Enforce Strict Mode as required by AGENTS.md
sql_mode = "ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION"

# Buffer pool (allocate 50-60% of server RAM for dedicated MySQL)
innodb_buffer_pool_size = 2G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
max_connections = 200
```

Restart MySQL:
```bash
sudo systemctl restart mysql
```

### 5.3 Create Dedicated Database & Least-Privilege User
Log in to MySQL:
```bash
sudo mysql -u root
```

Run:
```sql
CREATE DATABASE footwear_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER 'shop_user'@'127.0.0.1' IDENTIFIED BY 'YOUR_STRONG_RANDOM_PASSWORD_HERE';

GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, LOCK TABLES, REFERENCES ON footwear_shop.* TO 'shop_user'@'127.0.0.1';

FLUSH PRIVILEGES;
EXIT;
```

---

## 6. Redis Setup (Cache, Session, & Queue Separation)

Install Redis server:
```bash
sudo apt install -y redis-server
```

Edit `/etc/redis/redis.conf`:
```ini
# Bind strictly to local loopback
bind 127.0.0.1 ::1
protected-mode yes
port 6379

# Set strong authentication password
requirepass YOUR_STRONG_REDIS_PASSWORD

# Memory management & LRU eviction
maxmemory 512mb
maxmemory-policy volatile-lru
```

Restart Redis:
```bash
sudo systemctl restart redis-server
```

### Redis Database Isolation Policy
In production, allocate distinct numeric Redis databases in `.env` to prevent cache purges from wiping active user sessions:
- **DB 0**: Application & Navigation Cache (`REDIS_CACHE_DB=0`)
- **DB 1**: User Sessions & Guest Shopping Carts (`SESSION_DRIVER=redis`, `REDIS_SESSION_DB=1`)
- **DB 2**: Asynchronous Background Queue Jobs (`REDIS_QUEUE_DB=2`)

---

## 7. Supervisor Worker Configuration for Queues

Supervisor monitors the Laravel queue workers to process queued customer confirmation emails, SMS notifications, and courier status syncs.

Install Supervisor:
```bash
sudo apt install -y supervisor
```

Create `/etc/supervisor/conf.d/footwear-worker.conf`:

```ini
[program:footwear-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/footwear-brand/current/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --timeout=90
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/footwear-brand/shared/storage/logs/worker.log
stdout_logfile_maxbytes=20MB
stdout_logfile_backups=5
stopwaitsecs=3600
```

Load and start workers:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start footwear-worker:*
```

Check status:
```bash
sudo supervisorctl status
```

---

## 8. Cron Setup for Laravel Task Scheduler

Open the `www-data` system crontab:
```bash
sudo crontab -u www-data -e
```

Add the following single entry:
```cron
* * * * * cd /var/www/footwear-brand/current && php artisan schedule:run >> /dev/null 2>&1
```

The Laravel Scheduler handles:
1. Hourly courier status synchronization (polling consignment tracking for Steadfast & Pathao).
2. Daily sitemap warm-up and regeneration.
3. Pruning expired password tokens and stale guest carts.

---

## 9. SSL Certificate Setup via Let's Encrypt (Certbot)

Install Certbot Nginx plugin:
```bash
sudo apt install -y certbot python3-certbot-nginx
```

Obtain certificate:
```bash
sudo certbot --nginx -d yourbrand.com.bd -d www.yourbrand.com.bd
```

Test automatic renewal:
```bash
sudo certbot renew --dry-run
```

Certbot installs an automated renewal timer at `/etc/cron.d/certbot` or systemd timer `certbot.timer`.

---

## 10. Cloudflare Integration & Real IP Restoration

If routing your domain through Cloudflare's CDN and DDoS proxy (Orange Cloud):

### 10.1 Cloudflare Dashboard Settings
1. **SSL/TLS Mode**: Select **Full (Strict)**.
   > **CRITICAL**: Never select "Flexible", as Flexible causes infinite 301 redirect loops with Laravel's HTTPS redirection.
2. **Always Use HTTPS**: Enabled.
3. **HTTP/2 & HTTP/3**: Enabled.
4. **Brotli Compression**: Enabled.
5. **Cache Rules (Bypass Cache)**:
   Add a Cache Rule with Bypass Cache for:
   - `*yourbrand.com.bd/cart*`
   - `*yourbrand.com.bd/checkout*`
   - `*yourbrand.com.bd/account/*`
   - `*yourbrand.com.bd/admin/*`
   - `*yourbrand.com.bd/payment/*`
   - `*yourbrand.com.bd/api/*`

### 10.2 Real IP Restoration in Nginx
Cloudflare acts as a reverse proxy, meaning visitor IPs originate from Cloudflare's IP range unless explicitly restored.

Create `/etc/nginx/conf.d/cloudflare.conf`:

```nginx
# Cloudflare IPv4
set_real_ip_from 173.245.48.0/20;
set_real_ip_from 103.21.244.0/22;
set_real_ip_from 103.22.200.0/22;
set_real_ip_from 103.31.4.0/22;
set_real_ip_from 141.101.64.0/18;
set_real_ip_from 108.162.192.0/18;
set_real_ip_from 190.93.240.0/20;
set_real_ip_from 188.114.96.0/20;
set_real_ip_from 197.234.240.0/22;
set_real_ip_from 198.41.128.0/17;
set_real_ip_from 162.158.0.0/15;
set_real_ip_from 104.16.0.0/13;
set_real_ip_from 104.24.0.0/14;
set_real_ip_from 172.64.0.0/13;
set_real_ip_from 131.0.72.0/22;

# Cloudflare IPv6
set_real_ip_from 2400:cb00::/32;
set_real_ip_from 2606:4700::/32;
set_real_ip_from 2803:f800::/32;
set_real_ip_from 2405:b500::/32;
set_real_ip_from 2405:8100::/32;
set_real_ip_from 2a06:98c0::/29;
set_real_ip_from 2c0f:f248::/32;

real_ip_header CF-Connecting-IP;
```

Reload Nginx:
```bash
sudo nginx -t && sudo systemctl reload nginx
```

### 10.3 Laravel TrustProxies Configuration
In `bootstrap/app.php`, ensure `trustProxies(at: '*')` is set:
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->trustProxies(at: '*');
})
```
This guarantees that `request()->ip()` inside rate limiters (`throttle:login`, `throttle:checkout`, `throttle:tracking`) reads the authentic customer IP rather than Cloudflare's IP.

---

## 11. Zero-Downtime Atomic Deployment Workflow

### 11.1 Directory Structure
The server follows the zero-downtime symlink architecture:
```
/var/www/footwear-brand/
├── current -> /var/www/footwear-brand/releases/20261022143000
├── releases/
│   ├── 20261022120000/
│   └── 20261022143000/
└── shared/
    ├── .env
    └── storage/
        ├── app/public/
        ├── framework/
        └── logs/
```

### 11.2 Initial Directory Initialization (One-time)
Run as `deployer` on the server:
```bash
sudo mkdir -p /var/www/footwear-brand/releases
sudo mkdir -p /var/www/footwear-brand/shared/storage/{app/public,framework/{cache,sessions,views},logs}
sudo chown -R deployer:www-data /var/www/footwear-brand
sudo chmod -R 775 /var/www/footwear-brand/shared/storage
```

Create `/var/www/footwear-brand/shared/.env` using the production checklist below.

### 11.3 Executing Deployment
Use the included deployment script located at `scripts/deploy.sh`:

```bash
# Make script executable
chmod +x /var/www/footwear-brand/scripts/deploy.sh

# Run deployment
./scripts/deploy.sh
```

---

## 12. Automated Daily Database & Media Backup

The automated backup script is located in `scripts/backup.sh`. It performs:
1. Online, lock-free MySQL dump via `--single-transaction`.
2. Gzip stream compression (`.sql.gz`).
3. Compressed tarball of user-uploaded footwear photos (`.tar.gz`).
4. Automatic pruning of backups older than **14 days**.
5. Permission locking (`chmod 600`).

### 12.1 Manual Test Run
```bash
chmod +x /var/www/footwear-brand/scripts/backup.sh
sudo /var/www/footwear-brand/scripts/backup.sh
```

### 12.2 Schedule Backup via Root Crontab
Edit root crontab:
```bash
sudo crontab -e
```

Add the daily execution rule (runs at 03:00 AM Bangladesh time):
```cron
0 3 * * * /var/www/footwear-brand/scripts/backup.sh >> /var/log/backup.log 2>&1
```

---

## 13. Production `.env` Checklist & Post-Deployment Health Check

### 13.1 Production Environment Checklist (`/var/www/footwear-brand/shared/.env`)

```ini
APP_NAME="[BRAND_NAME]"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourbrand.com.bd

APP_LOCALE=en
APP_TIMEZONE=Asia/Dhaka
APP_CURRENCY=BDT
APP_CURRENCY_SYMBOL="৳"

# Database (Strict MySQL 8)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=footwear_shop
DB_USERNAME=shop_user
DB_PASSWORD=YOUR_STRONG_RANDOM_PASSWORD_HERE

# Cache, Queue, and Session (Redis Driven)
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=true

# Redis Connection
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=YOUR_STRONG_REDIS_PASSWORD
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=0
REDIS_SESSION_DB=1
REDIS_QUEUE_DB=2

# Mail Driver
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your-mailgun-username
MAIL_PASSWORD=your-mailgun-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="care@yourbrand.com.bd"
MAIL_FROM_NAME="[BRAND_NAME] Footwear"

# SMS Gateway (Live Bangladeshi Gateway Driver)
SMS_DRIVER=log # Replace with your live Bangladeshi SMS Gateway provider driver

# Online Payment Gateway (SSLCommerz Production Credentials)
SSLCOMMERZ_STORE_ID=your_live_store_id
SSLCOMMERZ_STORE_PASSWORD=your_live_store_password
SSLCOMMERZ_SANDBOX=false

# Courier Logistics Integration (Live Credentials)
COURIER_DEFAULT_DRIVER=steadfast
STEADFAST_API_KEY=your_live_steadfast_key
STEADFAST_SECRET_KEY=your_live_steadfast_secret
STEADFAST_BASE_URL=https://portal.steadfast.com.bd/api/v1

PATHAO_CLIENT_ID=your_live_pathao_client_id
PATHAO_CLIENT_SECRET=your_live_pathao_client_secret
PATHAO_USERNAME=your_live_pathao_username
PATHAO_PASSWORD=your_live_pathao_password
PATHAO_STORE_ID=your_live_pathao_store_id
PATHAO_BASE_URL=https://hermes-api.pathao.com
PATHAO_WEBHOOK_SECRET=your_live_pathao_webhook_secret
```

### 13.2 Post-Deployment Health Check Verification

Once deployment completes, verify the health of the production instance:

1. **Query Application Health Endpoint**:
   ```bash
   curl -i https://yourbrand.com.bd/health
   ```
   Expected response:
   ```json
   {
       "status": "healthy",
       "timestamp": "2026-10-22T20:00:00+06:00",
       "checks": {
           "database": { "status": "up", "connection": "mysql" },
           "cache": { "status": "up" },
           "storage": { "status": "up" }
       }
   }
   ```

2. **Verify Security Headers**:
   ```bash
   curl -I https://yourbrand.com.bd
   ```
   Verify headers:
   - `X-Frame-Options: SAMEORIGIN`
   - `X-Content-Type-Options: nosniff`
   - `X-XSS-Protection: 1; mode=block`
   - `Strict-Transport-Security: max-age=31536000; includeSubDomains; preload`
   - `Referrer-Policy: strict-origin-when-cross-origin`

3. **Verify Queue Processing**:
   ```bash
   sudo supervisorctl status
   # Check worker logs
   tail -f /var/www/footwear-brand/shared/storage/logs/worker.log
   ```

4. **Verify Dynamic Sitemap & Robots**:
   ```bash
   curl -s https://yourbrand.com.bd/robots.txt
   curl -sI https://yourbrand.com.bd/sitemap.xml
   ```
