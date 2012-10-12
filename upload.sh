#!/bin/bash
echo -e "Cleaning up temp files"
rm -f app.tar
rm -f app.tar.gz

echo -e "Creating code archive"
tar cfp app.tar \
config.ini \
scheduler.php \
setup.php \
library/Siamgeo/Aws/Service.php \
library/Siamgeo/Db/Table/{Ami.php,Customer.php,Instance.php,ProfileConfigData.php,Profile.php} \
library/Siamgeo/Deploy.php \
library/Siamgeo/Log/Abstract.php \
library/Siamgeo/Mailer.php \
library/Siamgeo/Scheduler/Engine.php \
library/Siamgeo/Service/{AmiService.php,CustomerService.php,ProfileConfigDataService.php} \
library/Siamgeo/Template/{Engine.php,Service.php} \
library/Siamgeo/Utils/Md5Random.php \
server-templates/{apache-vhost-template.txt,go.txt,temp-apache-vhost-template.txt,cloud-init-ssh-only.txt,magento-url-change,cloud-init.txt,setup-server.txt} \
schema/{Amis.sql,Customers.sql,Instances.sql,ProfileConfigData.sql,Profiles.sql,constraints.sql} \

echo -e "Compressing archive"
gzip -9 -f app.tar

# transfer the project
scp goscript.sh app.tar.gz ubuntu@geoengine.komodocommerce.com:/var/www/www.komodocommerce.com

# run the go script to install
ssh ubuntu@geoengine.komodocommerce.com '/var/www/www.komodocommerce.com/goscript.sh'

ssh ubuntu@geoengine.komodocommerce.com 'rm /var/www/www.komodocommerce.com/goscript.sh'
