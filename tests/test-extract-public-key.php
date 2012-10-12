<?php

$privateKey = file_get_contents('/var/www/japan.komodo-aws/data/customers/johnnydepp/8/johnnydepp-8.pem');

$privResource = openssl_pkey_get_private($privateKey);

$details = openssl_pkey_get_details($privResource);

var_dump($details['key']);
die();
