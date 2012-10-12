<?php
$startTime = time();

require_once 'setup.php';
require_once 'AwsSdk/sdk.class.php';
require_once 'AwsSdk/services/ec2.class.php';
$config = Zend_Registry::get('config');

// prevent multiple copies of this program running simultaneously
$fhLock = fopen($config->lockFile, "w");
if (!flock($fhLock, LOCK_EX|LOCK_NB)) {
    echo "The application lockfile is in place which means I'm already running an instance" . PHP_EOL;
    die();
}

$debug  = (bool) $config->debug;
$logger = Zend_Registry::get('logger');

// create the AWS SDK EC2 object to communicate with the AWS API
$ec2 = new AmazonEC2(
    array(
        'certificate_authority' => false,
        // 'credentials' => ''
        // 'default_cache_config' => 'apc'
        'default_cache_config' => '',
        'key'    => $config->ec2->key,
        'secret' => $config->ec2->secret
    )
);

// ami service is used to convert region names to ami names using the DB
$amiService = new Siamgeo_Service_AmiService(
    new Siamgeo_Db_Table_Ami()
);

// initiate our custom made service and pass in the EC2 object
$awsService = new Siamgeo_Aws_Service($ec2, $amiService);
$templateEngine = new Siamgeo_Template_Engine();
$profileConfigDataService = new Siamgeo_Service_ProfileConfigDataService(
    new Siamgeo_Db_Table_ProfileConfigData()
);

$templateService = new Siamgeo_Template_Service($templateEngine, $profileConfigDataService);
$customerService = new Siamgeo_Service_CustomerService(new Siamgeo_Db_Table_Customer());

$engine = Siamgeo_Scheduler_Engine::getInstance($awsService, $templateService, $customerService);

if ($config->debug)
    $engine->setDebugMode(true);

if (Zend_Registry::get('logger'))
    $engine->setLogger(Zend_Registry::get('logger'));

//var_dump($engine);

//die();

// get all profiles that still require processing
$profilesTable = new Siamgeo_Db_Table_Profile();
$rowset = $profilesTable->getAllNonCompletedProfiles();

if ($logger) $logger->info(__FILE__ . '(' . __LINE__ . ') ++++++++++++++++++++++++++++++++++++++ START ++++++++++++++++++++++++++++++++++++++');

if (sizeof($rowset) == 0) {
    if ($logger) $logger->info(__FILE__ . '(' . __LINE__ . ') Nothing to process in the Profiles table');
}


// loop through all the non-completed profiles for all customers
foreach ($rowset as $row) {
    $engine->setContext(array(
        'idProfile'    => $row->idProfile,
        'idCustomer'   => $row->idCustomer,
        'domainName'   => $row->domainName,
        'sslRequired'  => $row->sslRequired,
        'publicIp'     => $row->publicIp,
        'instanceType' => $row->instanceType,
        'regionName'   => $row->regionName,
        'status'       => $row->status,
        'metaStatus'     => $row->metaStatus
    ));

    // pending
    if ($engine->getCurrentStatus() == Siamgeo_Db_Table_Profile::PENDING) {
        try {
            $engine->allocateIpAddress();
        } catch (Exception $e) {
            if ($this->_logger) {
                $this->_logger->emerg(__FILE__ . '(' . __LINE__ . ') ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
                $this->_logger->emerg(__FILE__ . '(' . __LINE__ . ') ' . $e->getTraceAsString());
            }
            if ($debug) throw $e;
        }
    }

    // allocate an ip address
    if ($engine->getCurrentStatus() == Siamgeo_Db_Table_Profile::PASSED_ALLOCATED_IP_ADDRESS) {
        try {
            $engine->createSecurityGroup();
        } catch (Exception $e) {
            if ($logger) {
                $logger->emerg(__FILE__ . '(' . __LINE__ . ') ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
                $logger->emerg(__FILE__ . '(' . __LINE__ . ') ' . $e->getTraceAsString());
            }
            
            if ($debug) throw $e;
        }
    }

    // create a new key pair
    if ($engine->getCurrentStatus() == Siamgeo_Db_Table_Profile::PASSED_SECURITY_GROUP_CREATED) {
        try {
            $engine->createKeyPair();
        } catch (Exception $e) {
            if ($logger) {
                $logger->emerg(__FILE__ . '(' . __LINE__ . ') ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
                $logger->emerg(__FILE__ . '(' . __LINE__ . ') ' . $e->getTraceAsString());
            }
            if ($debug) throw $e;
        }
    }

    // launch the instance
    if ($engine->getCurrentStatus() == Siamgeo_Db_Table_Profile::PASSED_KEY_PAIR_GENERATED) {
        if ($logger)
            $logger->info(__FILE__ . '(' . __LINE__ .') Profile: ' . $row->idProfile . ' is in "' . Siamgeo_Db_Table_Profile::PASSED_KEY_PAIR_GENERATED . '" status so attempting to run instance');

        try {
            $engine->launchInstance($config->configInitFilepath, $config->useCloudInit);
        } catch (Exception $e) {
            if ($logger) {
                $logger->emerg(__FILE__ . '(' . __LINE__ . ') ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
                $logger->emerg(__FILE__ . '(' . __LINE__ . ') ' . $e->getTraceAsString());
            }
            if ($debug) throw $e;
        }
    }

    // describe instances
    if ($engine->getCurrentStatus() == Siamgeo_Db_Table_Profile::PASSED_INSTANCE_STARTED) {
        if ($logger)
            $logger->info(__FILE__ . '(' . __LINE__ .') Profile: ' . $row->idProfile . ' is in "' . Siamgeo_Db_Table_Profile::PASSED_INSTANCE_STARTED . '" status so attempting to associate the public ip to the instance');

        try {
            $engine->describeInstances();
        } catch (Exception $e) {
            if ($logger) {
                $logger->emerg(__FILE__ . '(' . __LINE__ . ') ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
                $logger->emerg(__FILE__ . '(' . __LINE__ . ') ' . $e->getTraceAsString());
            }
            if ($debug) throw $e;
        }
    }

    // generate the templates (apache temp vhost, vhost, setup script etc)
    if ($engine->getCurrentStatus() == Siamgeo_Db_Table_Profile::PASSED_ASSOCIATED_ADDRESS) {
        try {
            $engine->generateTemplateFiles($config->customersDataDir);
        } catch (Exception $e) {
            if ($logger) {
                $logger->emerg(__FILE__ . '(' . __LINE__ . ') ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
                $logger->emerg(__FILE__ . '(' . __LINE__ . ') ' . $e->getTraceAsString());
            }
            if ($debug) throw $e;
        }
    }

    if ($engine->getCurrentStatus() == Siamgeo_Db_Table_Profile::PASSED_GENERATED_DEPLOY_FILES) {
        try {
            if ($logger) $logger->debug(__FILE__ . '(' . __LINE__ . ') Calling runRemoteSetup(' . $config->customersDataDir . ')');

            $engine->executeRemoteSetup($config->customersDataDir);
        } catch (Exception $e) {
            if ($logger) {
                $logger->emerg(__FILE__ . '(' . __LINE__ . ') ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
                $logger->emerg(__FILE__ . '(' . __LINE__ . ') ' . $e->getTraceAsString());
            }
            if ($debug) throw $e;
        }
    }

    if ($engine->getCurrentStatus() == Siamgeo_Db_Table_Profile::PASSED_EXECUTED_AND_SENT_FILES) {
        $engine->complete();
    }
}

$endTime = time();

$timeElapsed = $endTime - $startTime;
if ($logger) $logger->info(__FILE__ . '(' . __LINE__ .') Took ' . $timeElapsed . ' seconds to execute');
if ($logger) $logger->info(__FILE__ . '(' . __LINE__ .') ++++++++++++++++++++++++++++++++++++++ END ++++++++++++++++++++++++++++++++++++++');

fclose($fhLock);
