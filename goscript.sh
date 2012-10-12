#!/bin/bash
cd /var/www/www.komodocommerce.com
tar zxfp app.tar.gz
rm app.tar.gz


# ensure we have data, data/logs, data/customers directories
# and that they are read, write and execute as www-data will
# wish to use them
if [ ! -d "./data" ]; then
    mkdir data
    chmod 777 data
fi
if [ ! -d "./data/logs" ]; then
    mkdir data/logs
    chmod 777 data/logs
fi
if [ ! -d "./data/customers" ]; then
    mkdir data/customers
    chmod 777 data/customers
fi

# setup the sym links
cd /var/www/www.komodocommerce.com/library
if [ ! -d "AwsSdk" ]; then
    ln -s /var/www/sdk-1.5.11 AwsSdk
fi

if [ ! -d "Zend" ]; then
    ln -s /var/www/ZendFramework-1.11.12/library/Zend Zend
fi
