<?php
require_once 'setup.php';


// read the config data from the database
$profileConfigDataTable = new Siamgeo_Db_Table_ProfileConfigData();
$profileConfigDataService = new Siamgeo_Service_ProfileConfigDataService(
    $profileConfigDataTable
);


$idProfile = 3;
$username = 'bluewave';

$templateEngine = new Siamgeo_Template_Engine($config->serverTemplateDir);
$templateEngine->loadTemplate('apache-vhost-template.txt')
               ->loadTemplate('setup-server.txt')
               ->loadTemplate('cloud-init.txt');

$apacheConfig = $profileConfigDataService->getConfigList($idProfile, 'apache');
$templateEngine->fillTemplate('apache-vhost-template.txt', $apacheConfig);

$setupAndMagentoConfig = $profileConfigDataService->getConfigList($idProfile,
    array('setup', 'magento')
);
$templateEngine->fillTemplate('setup-server.txt', getConfigList($idProfile, $setupAndMagentoConfig));

$templateEngine->setCustomerDataDirectory($config->customersDataDir)
               ->setContext($username, $idProfile)
               ->saveTemplate('apache-vhost-template.txt', $apacheConfig['domain-name'])
               ->saveTemplate('setup-server.txt', 'server-setup.sh')
               ->saveTemplate('cloud-init.txt', 'cloud-init.txt');
