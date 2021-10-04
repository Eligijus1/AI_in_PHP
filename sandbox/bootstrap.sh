#!/usr/bin/env bash

echo "# IPv4 and IPv6 localhost aliases:" | sudo tee /etc/hosts
echo "127.0.0.1 vagrant.ai.test.com  vagrant.ai.test  localhost" | sudo tee -a /etc/hosts
echo "::1       vagrant.ai.test.com  vagrant.ai.test  localhost" | sudo tee -a /etc/hosts
echo "10.0.2.15 vagrant.ai.test.com  vagrant.ai.test  localhost" | sudo tee -a /etc/hosts

# Update packages:
apt-get update

# Install nmap:
sudo apt-get install -y nmap

# Apache install:
apt-get install -y apache2

# Make in Apache available all source (html) directory:
if ! [ -L /var/www ]; then
  rm -rf /var/www/html
  ln -fs /vagrant/html /var/www/html
fi

# Installing PHP and some extra libraries:
sudo apt-get install -y php
sudo apt-get install -y php-curl
sudo apt-get install -y php-gd
sudo apt-get install -y php-bcmath
sudo apt-get install -y php-dev # This library required to compile PHP modules.

# Check loaded PHP modules:
#php -m

# Add DNS to /etc/resolv.conf
echo "nameserver 8.8.8.8" | sudo tee -a /etc/resolv.conf
echo "nameserver 8.8.4.4" | sudo tee -a /etc/resolv.conf

# Install composer:
cd /tmp/
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer

# Install git:
sudo apt-get install -y git

# Install PHP FANN:
sudo apt-get install -y libfann*
cd /tmp/
wget http://pecl.php.net/get/fann
mkdir fann-latest
tar xvfz fann -C /tmp/fann-latest --strip-components=1
cd /tmp/fann-latest/
export LANGUAGE=en_US.UTF-8
export LC_ALL=en_US.UTF-8
phpize
./configure
make
sudo cp -R /tmp/fann-latest/modules/* /usr/lib/php/20190902/
sudo sh -c "echo 'extension=fann.so' > /etc/php/7.4/mods-available/fann.ini"
sudo phpenmod fann
sudo ln -s /etc/php/7.4/mods-available/fann.ini /etc/php/7.4/apache2/conf.d/30-fann.ini
sudo service apache2 restart

# Install PHP opencv:
cd /tmp/
wget https://raw.githubusercontent.com/php-opencv/php-opencv-packages/master/opencv_4.5.0_amd64.deb
sudo dpkg -i opencv_4.5.0_amd64.deb
rm -f opencv_4.5.0_amd64.deb

sudo apt install -y pkg-config cmake git php-dev
git clone https://github.com/php-opencv/php-opencv.git
cd php-opencv
git checkout php7.4
phpize
./configure --with-php-config=/usr/bin/php-config
make
sudo make install

sudo sh -c "echo 'extension=opencv.so' > /etc/php/7.4/mods-available/opencv.ini"
sudo phpenmod opencv

# Check loaded PHP modules:
echo "Loaded PHP extensions:"
php -m




