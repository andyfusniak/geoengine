#!/bin/bash
export APPLICATION_ENV=japan
cd /var/www/japan.komodo-aws
/usr/bin/php5 /var/www/japan.komodo-aws/scheduler.php >> /var/www/japan.komodo-aws/data/logs/cron.log
