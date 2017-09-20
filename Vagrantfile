# -*- mode: ruby -*-
# vi: set ft=ruby :

VAGRANTFILE_API_VERSION = '2'

@script = <<SCRIPT
# Fix for https://bugs.launchpad.net/ubuntu/+source/livecd-rootfs/+bug/1561250
if ! grep -q "ubuntu-xenial" /etc/hosts; then
    echo "127.1.0.1 ubuntu-xenial" >> /etc/hosts
fi

# Install dependencies
add-apt-repository ppa:ondrej/php
apt-get update
apt-get install -y git curl php7.1-bcmath php7.1-bz2 php7.1-cli php7.1-curl php7.1-intl php7.1-json \
php7.1-mbstring php7.1-opcache php7.1-soap php7.1-sqlite3 php7.1-xml php7.1-xsl php7.1-zip libapache2-mod-php7.1 \
php-pear php7.1-dev build-essential memcached libmemcached-dev zlib1g-dev pkg-config mongodb tcl;

# Install Redis
curl -O http://download.redis.io/redis-stable.tar.gz
tar xzvf redis-stable.tar.gz
cd redis-stable
make
make install
mkdir /etc/redis
cp redis.conf /etc/redis
cd ..
rm -rf redis-stable
rm redis-stable.tar.gz

sed -i 's/supervised no/supervised systemd/' /etc/redis/redis.conf
sed -i 's/dir .\//dir \/var\/lib\/redis/' /etc/redis/redis.conf

echo '[Unit]' > /etc/systemd/system/redis.service
echo 'Description=Redis In-Memory Data Store' >> /etc/systemd/system/redis.service
echo 'After=network.target' >> /etc/systemd/system/redis.service
echo "" >> /etc/systemd/system/redis.service
echo '[Service]' >> /etc/systemd/system/redis.service
echo 'User=redis' >> /etc/systemd/system/redis.service
echo 'Group=redis' >> /etc/systemd/system/redis.service
echo 'ExecStart=/usr/local/bin/redis-server /etc/redis/redis.conf' >> /etc/systemd/system/redis.service
echo 'ExecStop=/usr/local/bin/redis-cli shutdown' >> /etc/systemd/system/redis.service
echo 'Restart=always' >> /etc/systemd/system/redis.service
echo "" >> /etc/systemd/system/redis.service
echo '[Install]' >> /etc/systemd/system/redis.service
echo 'WantedBy=multi-user.target' >> /etc/systemd/system/redis.service

adduser --system --group --no-create-home redis
mkdir /var/lib/redis
chown redis:redis /var/lib/redis
chmod 770 /var/lib/redis

# Install Pecl Packages
pecl install memcached
pecl install mongodb
pecl install redis

echo extension=memcached.so > /etc/php/7.1/mods-available/memcached.ini
echo extension=mongodb.so > /etc/php/7.1/mods-available/mongodb.ini
echo extension=redis.so > /etc/php/7.1/mods-available/redis.ini

phpenmod memcached;
phpenmod mongodb;
phpenmod redis;


if [ -e /usr/local/bin/composer ]; then
    /usr/local/bin/composer self-update
else
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

# Reset home directory of vagrant user
if ! grep -q "cd /var/www" /home/ubuntu/.profile; then
    echo "cd /var/www" >> /home/ubuntu/.profile
fi

echo "** Run the following command to install dependencies, if you have not already:"
echo "    vagrant ssh -c 'composer install'"
SCRIPT

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.box = 'ubuntu/xenial64'
  config.vm.synced_folder '.', '/var/www'
  config.vm.provision 'shell', inline: @script

  config.vm.provider "virtualbox" do |vb|
    vb.customize ["modifyvm", :id, "--memory", "1024"]
    vb.customize ["modifyvm", :id, "--name", "PSR11 PHP Cache"]
  end
end
