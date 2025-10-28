# CDMIS Deployment Guide

## Overview
This guide provides step-by-step instructions for deploying the Centralized Document Management Information System (CDMIS) to various hosting environments.

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [Local Development](#local-development)
3. [Shared Hosting Deployment](#shared-hosting-deployment)
4. [VPS/Cloud Server Deployment](#vpscloud-server-deployment)
5. [Google Cloud Platform Deployment](#google-cloud-platform-deployment)
6. [Security Checklist](#security-checklist)
7. [Post-Deployment](#post-deployment)

---

## Prerequisites

### System Requirements
- PHP 8.0 or higher with extensions:
  - PDO
  - pdo_mysql
  - mbstring
  - json
  - openssl
- MySQL/MariaDB 10.4 or higher
- Node.js 18+ (for building frontend)
- Web server (Apache or Nginx)
- SSL certificate (for HTTPS)

### Tools Needed
- FTP/SFTP client (FileZilla, WinSCP)
- SSH client (PuTTY for Windows, or Terminal)
- MySQL client (phpMyAdmin, MySQL Workbench, or CLI)

---

## Local Development

### Backend Setup
```bash
# Navigate to backend directory
cd backend

# Copy environment file
cp .env.example .env

# Edit .env with your database credentials
nano .env

# Start PHP development server
php -S localhost:8000
```

### Frontend Setup
```bash
# Navigate to frontend directory
cd "CDMIS LOG IN PAGE"

# Install dependencies
npm install

# Copy environment file
cp .env.example .env

# Start development server
npm run dev
```

### Database Setup
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE cdmis_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"

# Import schema
mysql -u root -p cdmis_db < cdmis_db.sql
```

---

## Shared Hosting Deployment

### Step 1: Prepare Files

1. **Build Frontend**
   ```bash
   cd "CDMIS LOG IN PAGE"
   npm run build
   ```
   This creates a `build/` directory with static files.

2. **Prepare Backend**
   - Copy all files from `backend/` directory
   - Ensure `.env` is configured (don't use .env.example in production)

### Step 2: Upload Files

Using FTP/SFTP:

1. **Upload Backend**
   - Upload `backend/` contents to `public_html/api/` or similar
   - Set permissions on `public/uploads/` to 755

2. **Upload Frontend**
   - Upload contents of `build/` directory to `public_html/`

### Step 3: Database Setup

1. **Create Database**
   - Use cPanel or hosting control panel
   - Create database: `cdmis_db`
   - Create database user with all privileges
   - Note hostname (usually `localhost`)

2. **Import Schema**
   - Use phpMyAdmin
   - Select database
   - Import `cdmis_db.sql`

### Step 4: Configuration

1. **Backend Configuration**
   ```bash
   # Edit backend/.env
   DB_HOST=localhost
   DB_NAME=cdmis_db
   DB_USER=your_db_user
   DB_PASSWORD=your_db_password
   
   APP_ENV=production
   JWT_SECRET=generate-random-secret-key-here
   ```

2. **Apache Configuration**
   Create `.htaccess` in backend directory:
   ```apache
   RewriteEngine On
   RewriteBase /api/
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^(.*)$ api.php/$1 [QSA,L]
   ```

3. **Frontend Configuration**
   Update API URL in frontend before building:
   ```env
   VITE_API_URL=https://yourdomain.com/api
   ```

### Step 5: SSL Certificate

- Enable SSL through hosting control panel
- Many shared hosts offer free Let's Encrypt SSL
- Update all HTTP URLs to HTTPS

---

## VPS/Cloud Server Deployment

### Step 1: Server Setup (Ubuntu/Debian)

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP and extensions
sudo apt install php8.3 php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl -y

# Install MySQL
sudo apt install mysql-server -y

# Install Apache or Nginx
sudo apt install apache2 -y  # OR
sudo apt install nginx -y

# Install Node.js (for building)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install nodejs -y
```

### Step 2: Apache Configuration

```bash
# Create virtual host
sudo nano /etc/apache2/sites-available/cdmis.conf
```

Add configuration:
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/cdmis

    # Frontend
    <Directory /var/www/cdmis>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # API
    Alias /api /var/www/cdmis/api
    <Directory /var/www/cdmis/api>
        Options -Indexes
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/cdmis_error.log
    CustomLog ${APACHE_LOG_DIR}/cdmis_access.log combined
</VirtualHost>
```

Enable site:
```bash
sudo a2enmod rewrite
sudo a2ensite cdmis.conf
sudo systemctl restart apache2
```

### Step 3: Nginx Configuration (Alternative)

```bash
sudo nano /etc/nginx/sites-available/cdmis
```

Add configuration:
```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/cdmis;
    index index.html;

    # Frontend
    location / {
        try_files $uri $uri/ /index.html;
    }

    # API
    location /api/ {
        alias /var/www/cdmis/api/;
        try_files $uri $uri/ /api/api.php?$query_string;
        
        location ~ \.php$ {
            include fastcgi_params;
            fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
            fastcgi_param SCRIPT_FILENAME $request_filename;
        }
    }

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/cdmis /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Step 4: Deploy Application

```bash
# Create directory
sudo mkdir -p /var/www/cdmis

# Upload files (using scp or git)
scp -r backend/ user@server:/var/www/cdmis/api/
scp -r "CDMIS LOG IN PAGE/build/*" user@server:/var/www/cdmis/

# Set permissions
sudo chown -R www-data:www-data /var/www/cdmis
sudo chmod -R 755 /var/www/cdmis
sudo chmod -R 775 /var/www/cdmis/api/public/uploads
```

### Step 5: Database Setup

```bash
# Secure MySQL
sudo mysql_secure_installation

# Create database
sudo mysql -u root -p

CREATE DATABASE cdmis_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER 'cdmis_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON cdmis_db.* TO 'cdmis_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import schema
mysql -u cdmis_user -p cdmis_db < cdmis_db.sql
```

### Step 6: SSL with Let's Encrypt

```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache -y  # For Apache
# OR
sudo apt install certbot python3-certbot-nginx -y   # For Nginx

# Get certificate
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com  # Apache
# OR
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com   # Nginx

# Auto-renewal (test)
sudo certbot renew --dry-run
```

---

## Google Cloud Platform Deployment

### Step 1: Create Cloud SQL Instance

```bash
# Using gcloud CLI
gcloud sql instances create cdmis-db \
    --database-version=MYSQL_8_0 \
    --tier=db-f1-micro \
    --region=asia-southeast1

# Set root password
gcloud sql users set-password root \
    --host=% \
    --instance=cdmis-db \
    --password=YOUR_STRONG_PASSWORD

# Create database
gcloud sql databases create cdmis_db --instance=cdmis-db

# Create user
gcloud sql users create cdmis_user \
    --instance=cdmis-db \
    --password=YOUR_USER_PASSWORD
```

### Step 2: Deploy Backend on App Engine

Create `backend/app.yaml`:
```yaml
runtime: php83
env: standard

handlers:
  - url: /.*
    script: auto
    secure: always

env_variables:
  DB_HOST: '/cloudsql/YOUR_PROJECT_ID:REGION:cdmis-db'
  DB_NAME: 'cdmis_db'
  DB_USER: 'cdmis_user'
  DB_PASSWORD: 'YOUR_USER_PASSWORD'
  APP_ENV: 'production'
  JWT_SECRET: 'YOUR_JWT_SECRET'

automatic_scaling:
  min_instances: 1
  max_instances: 10
```

Deploy:
```bash
cd backend
gcloud app deploy
```

### Step 3: Deploy Frontend on Cloud Storage

```bash
# Build frontend
cd "CDMIS LOG IN PAGE"
npm run build

# Create bucket
gsutil mb gs://cdmis-frontend

# Make bucket public
gsutil iam ch allUsers:objectViewer gs://cdmis-frontend

# Upload files
gsutil -m cp -r build/* gs://cdmis-frontend/

# Enable website configuration
gsutil web set -m index.html -e index.html gs://cdmis-frontend
```

### Step 4: Configure Cloud CDN (Optional)

```bash
# Create backend bucket
gcloud compute backend-buckets create cdmis-backend-bucket \
    --gcs-bucket-name=cdmis-frontend \
    --enable-cdn

# Create URL map
gcloud compute url-maps create cdmis-url-map \
    --default-backend-bucket=cdmis-backend-bucket

# Create target HTTP proxy
gcloud compute target-http-proxies create cdmis-http-proxy \
    --url-map=cdmis-url-map

# Create forwarding rule
gcloud compute forwarding-rules create cdmis-http-rule \
    --global \
    --target-http-proxy=cdmis-http-proxy \
    --ports=80
```

---

## Security Checklist

### Pre-Deployment
- [ ] Change `JWT_SECRET` to strong random string
- [ ] Set `APP_ENV=production`
- [ ] Disable PHP error display in production
- [ ] Use strong database passwords
- [ ] Review file permissions (755 for directories, 644 for files)

### Post-Deployment
- [ ] Enable HTTPS/SSL
- [ ] Configure firewall (allow only 80, 443, 22)
- [ ] Set up automated backups
- [ ] Enable fail2ban or similar
- [ ] Configure rate limiting
- [ ] Review CORS settings
- [ ] Enable security headers
- [ ] Set up monitoring and logging

### Backend Security
```php
// Add to backend/api.php
if (getenv('APP_ENV') === 'production') {
    ini_set('display_errors', 0);
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
}
```

### Database Security
- Use strong passwords
- Limit user privileges
- Enable binary logging for backups
- Regular security updates

---

## Post-Deployment

### 1. Create Admin User

```sql
INSERT INTO users (school_id, password_hash, full_name, email, role) 
VALUES (
  '00-0-00001',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'System Administrator',
  'admin@yourdomain.com',
  'Admin'
);
```
Default password: `1234` (change immediately after first login)

### 2. Set Up Departments

Access `/admin/manage-departments` and add all departments

### 3. Create User Accounts

Access `/admin/manage-users` and create accounts for:
- Departmental Record Custodians
- Staff members

### 4. Import Disposition Schedule

The `disposition_schedule` table should already be populated from the SQL file

### 5. Test All Workflows

- [ ] Login as Admin, Custodian, Staff
- [ ] Create test records
- [ ] Upload test files
- [ ] Submit document requests
- [ ] Verify activity logs
- [ ] Check disposal reminders
- [ ] Test department analytics

### 6. Set Up Backups

```bash
# Daily database backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u cdmis_user -p cdmis_db > /backup/cdmis_db_$DATE.sql
# Keep only last 30 days
find /backup -name "cdmis_db_*.sql" -mtime +30 -delete
```

Add to crontab:
```bash
0 2 * * * /path/to/backup_script.sh
```

### 7. Monitoring

- Set up uptime monitoring
- Configure error logging
- Monitor disk space
- Track database size
- Review activity logs regularly

---

## Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Verify credentials in `.env`
   - Check MySQL is running
   - Confirm user privileges

2. **404 on API Endpoints**
   - Check `.htaccess` or nginx config
   - Verify mod_rewrite is enabled (Apache)
   - Check file permissions

3. **File Upload Fails**
   - Verify `uploads/` directory permissions (775)
   - Check PHP upload limits in `php.ini`
   - Review web server error logs

4. **JWT Token Invalid**
   - Ensure `JWT_SECRET` matches between requests
   - Check token expiration time
   - Verify Authorization header format

### Log Locations

- Apache: `/var/log/apache2/`
- Nginx: `/var/log/nginx/`
- PHP: `/var/log/php/`
- MySQL: `/var/log/mysql/`

---

## Maintenance

### Regular Tasks

**Daily:**
- Monitor error logs
- Check system resources

**Weekly:**
- Review activity logs
- Check disposal reminders
- Verify backups

**Monthly:**
- Update dependencies
- Security updates
- Database optimization
- Review user accounts

---

## Support

For technical support:
- Review logs in `/var/log/`
- Check application error logs
- Consult `IMPLEMENTATION_GUIDE.md`
- Contact system administrator

---

**Document Version:** 1.0  
**Last Updated:** 2025-10-28
