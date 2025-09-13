# AWS Lightsail Setup for Kirby CMS

For deploying a Kirby CMS site to AWS Lightsail, here's a recommended launch script that sets up the LAMP stack and configures your site:

```bash
#!/bin/bash

# Update system
sudo apt update && sudo apt upgrade -y

# Add PHP repository for latest versions
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install Apache first
sudo apt install -y apache2

# Install PHP 8.2 and required extensions (fixed package names)
sudo apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml php8.2-curl php8.2-mbstring php8.2-zip php8.2-gd php8.2-common libapache2-mod-php8.2

# Install Composer
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer

# Configure Apache
sudo a2enmod rewrite
sudo systemctl enable apache2

# Set up virtual host for Kirby
sudo tee /etc/apache2/sites-available/kirby.conf > /dev/null <<EOF
<VirtualHost *:80>
    DocumentRoot /var/www/html/kirby
    ServerName your-domain.com
    
    <Directory /var/www/html/kirby>
        AllowOverride All
        Require all granted
        Options -Indexes
    </Directory>
    
    # Security headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    
    ErrorLog ${APACHE_LOG_DIR}/kirby_error.log
    CustomLog ${APACHE_LOG_DIR}/kirby_access.log combined
</VirtualHost>
EOF

# Enable site and disable default
sudo a2ensite kirby.conf
sudo a2dissite 000-default.conf
sudo a2enmod headers

# Create web directory and set permissions
sudo mkdir -p /var/www/html/kirby
sudo chown -R www-data:www-data /var/www/html/kirby
sudo chmod -R 755 /var/www/html/kirby

# Set proper permissions for Kirby
sudo chmod -R 755 /var/www/html/kirby/content
sudo chmod -R 755 /var/www/html/kirby/site/accounts
sudo chmod -R 755 /var/www/html/kirby/site/cache
sudo chmod -R 755 /var/www/html/kirby/media

# Restart Apache
sudo systemctl restart apache2

# Install Git for deployment
sudo apt install -y git

# Create .htaccess for Kirby (if not exists)
sudo tee /var/www/html/kirby/.htaccess > /dev/null <<EOF
# Kirby .htaccess

# rewrite rules
<IfModule mod_rewrite.c>

# enable awesome urls. i.e.:
# http://yourdomain.com/about-us/team
RewriteEngine on

# make sure to set the RewriteBase correctly
# if you are running the site in a subfolder.
# Otherwise links or the entire site will break.
#
# If your homepage is http://yourdomain.com/mysite
# Set the RewriteBase to:
#
# RewriteBase /mysite

# In some environments it's necessary to
# set the RewriteBase to:
#
# RewriteBase /

# block files and folders beginning with a dot, such as .git
# except for the .well-known folder, which is used for Let's Encrypt and security.txt
RewriteRule (^|/)\.(?!well-known\/) index.php [L]

# block all files in the content folder from being accessed directly
RewriteRule ^content/(.*) index.php [L]

# block all files in the site folder from being accessed directly
RewriteRule ^site/(.*) index.php [L]

# block direct access to Kirby and the Panel sources
RewriteRule ^kirby/(.*) index.php [L]

# make site links work
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*) index.php [L,QSA]

</IfModule>

# pass the Authorization header to PHP
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1

# compress text file responses
<IfModule mod_deflate.c>
AddOutputFilterByType DEFLATE text/plain
AddOutputFilterByType DEFLATE text/html
AddOutputFilterByType DEFLATE text/css
AddOutputFilterByType DEFLATE text/javascript
AddOutputFilterByType DEFLATE application/javascript
AddOutputFilterByType DEFLATE application/json
</IfModule>
EOF

echo "Setup complete! Deploy your Kirby site to /var/www/html/kirby"
echo "Remember to:"
echo "1. Upload your site files"
echo "2. Run 'composer install' in the site directory"
echo "3. Set up SSL certificate"
echo "4. Configure your domain DNS"
```

## Deployment Steps

**After running the script:**

1. **Upload your files:**
   ```bash
   scp -i LightsailDefaultKey.pem -r . ubuntu@3.39.240.193:/var/www/html/kirby/
   ```

2. **Install dependencies:**
   ```bash
   cd /var/www/html/kirby
   composer install --no-dev --optimize-autoloader
   ```

3. **Set final permissions:**
   ```bash
   sudo chown -R www-data:www-data /var/www/html/kirby
   sudo find /var/www/html/kirby -type d -exec chmod 755 {} \;
   sudo find /var/www/html/kirby -type f -exec chmod 644 {} \;
   ```

4. **Configure SSL with Let's Encrypt:**
   ```bash
   sudo apt install certbot python3-certbot-apache
   sudo certbot --apache -d massivevoid.com
   ```

This script sets up everything needed for Kirby CMS with proper security and performance configurations.

sudo sed -i 's/ServerName your-domain.com/ServerName massivevoid.com/' /etc/apache2/sites-available/kirby.conf

sudo sed -i 's/ServerName your-domain.com/ServerName massivevoid.com/' /etc/apache2/sites-available/kirby-le-ssl.conf