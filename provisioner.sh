#!/bin/bash
apt-get update

debconf-set-selections <<< 'mysql-server mysql-server/root_password password root'
debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password root'
  
apt-get install -y build-essential git mcrypt curl apache2
apt-get install -y php5 php5-json php5-mcrypt php5-gd php5-curl php5-cli
apt-get install -y mysql-server mysql-client php5-mysql
apt-get install -y libapache2-mod-php5

php5enmod mcrypt
php5enmod json
php5enmod curl
a2enmod rewrite

curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

cp /vagrant/config/site.sfmoviemaps.conf /etc/apache2/sites-available/
a2dissite 000-default.conf
a2ensite site.sfmoviemaps.conf
service apache2 restart

sed -i "s/bind-address.*/bind-address = 0.0.0.0/" /etc/mysql/my.cnf
mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS sfmovies;"
mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS sfmovies_test;"
mysql -uroot -proot -e "GRANT USAGE ON *.* TO vagrant@localhost IDENTIFIED BY 'vagrant';"
mysql -uroot -proot -e "GRANT ALL PRIVILEGES ON *.* TO vagrant@localhost;FLUSH PRIVILEGES;"
service mysql restart

cd /app/
composer install
composer update
php artisan migrate
php artisan db:seed

chgrp -R www-data /app
cd /app/public/
mkdir posters
mkdir combined
chmod -R 775 posters
chmod -R 775 combined