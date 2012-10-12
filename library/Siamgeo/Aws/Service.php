<?php
class Siamgeo_Aws_Service
{
    protected $_logger = null;
    protected $_region = null;
    protected $_username;
    protected $_idProfile;
    protected $_groupName = null;
    protected $_customersDataDir;
    protected $_regionMappings;
    protected $_amiName;
    protected $_ec2;

    private $_shortRegionToLongRegionMappings = array(
        'us-east-1'      => AmazonEC2::REGION_US_E1,
        'us-west-1'      => AmazonEC2::REGION_US_W1,
        'us-west-2'      => AmazonEC2::REGION_US_W2,
        'eu-west-1'      => AmazonEC2::REGION_EU_W1,
        'ap-southeast-1' => AmazonEC2::REGION_APAC_SE1,
        'ap-northeast-1' => AmazonEC2::REGION_APAC_NE1,
        'sa-east-1'      => AmazonEC2::REGION_SA_E1
    );

    /**
     * @param AmazonEC2 $ec2
     * @param Siamgeo_Service_AmiService $amiService
     */
    public function __construct($ec2, $amiService)
    {
        $this->_ec2 = $ec2;

        $this->_customersDataDir = Zend_Registry::get('config')->customersDataDir;
        $this->_regionMappings = $amiService->getRegionAmiList();
    }

    public function setLogger($logger)
    {
        $this->_logger = $logger;
        return $this;
    }

    /**
     * Sets the context for the future service call methods
     *
     * @param string $region the region to be used e.g. us-west1, ap-northeast-1
     * @param string $username the username of the customer
     * @param int $idProfile the profile id (primary key in the database)
     *
     * @throws Exception
     *
     * @return Siamgeo_Aws_Service fluent interface
     */
    public function setContext($region, $username, $idProfile)
    {
        $this->_region = $region;
        $this->_username = $username;
        $this->_idProfile = (int) $idProfile;

        if (isset($this->_regionMappings[$region])) {
            $this->_amiName = $this->_regionMappings[$region];
        } else {
            if ($this->_logger) $this->_logger->emerg(__FILE__ . '(' . __LINE__ .') Unknown region passed: ' . $region);
            throw new Exception('Unknown region ' . $region);
        }

        // create the group name in the format ackme-2
        $this->_groupName = $username . '-' . $idProfile;

        if (isset($this->_shortRegionToLongRegionMappings[$region])) {
            $queueUrl = $this->_shortRegionToLongRegionMappings[$region];
        } else {
            if ($this->_logger) $this->_logger->emerg(__FILE__ . '(' . __LINE__ .') Cannot find queue URL for region: ' . $region);
            throw new Exception('Cannot find queue URL for region ' . $region);
        }

        // set the region
        $this->_ec2->set_region($queueUrl);

        return $this;
    }

    /**
     * Allocates an ip address
     *
     * @throws Exception
     * @return Siamgeo_Aws_Service fluent interface
     */
    public function allocateIpAddress()
    {
        // allocate a new ip addres
        $response = $this->_ec2->allocate_address();

        if ($response->isOK()) {
            return (string) $response->body->publicIp;
        } else {
            if ($this->_logger) $this->_logger->emerg(__FILE__ . '(' . __LINE__ .') failed to allocate an ip');
            throw new Exception('failed to allocate an ip');
        }

        return $this;
    }

    /**
     * Creates a new security group for the current context
     * using the format ackme-000005 as the group name
     *
     * @param boolean $includeSsl if true then opens ports 443 in the security group settings
     * @throws Exception
     * @return Siamgeo_Aws_Service fluent interface
     */
    public function createSecurityGroup($includeSsl = true)
    {
        if ((null === $this->_region) || (null === $this->_groupName)) {
            if ($this->_logger) $this->_logger->emerg(__FILE__ . '(' . __LINE__ .') Failed to call setContext before calling createSecurityGroup');

            throw new Exception('You must call setContext first before calling this function');
        }

        if ($this->_logger) $this->_logger->info(__FILE__ . '(' . __LINE__ .') Attempting to create a security group for ' . $this->_groupName);

        // create a security group for this profile
        $response = $this->_ec2->create_security_group($this->_groupName, $this->_groupName);

        if ($response->isOK()) {
            $groupId = (string) $response->body->groupId;
            $requestId = (string) $response->body->requestId;
        } else {
            $message = (string) $response->body->Errors->Error->Message;
            if ($this->_logger) $this->_logger->crit(__FILE__ . '(' . __LINE__ . ') ' . $message);

            throw new Exception('Failed to create security group for ' . $this->_groupName);
        }

        // open SSH (Port 22) and HTTP (Port 80)
        $securityGroupOptions = array(
            'GroupId' => $groupId,
            'IpPermissions' => array(
                array(
                    'IpProtocol' => 'tcp',
                    'FromPort' => '22',
                    'ToPort' => '22',
                    'IpRanges' => array(
                        array('CidrIp' => '0.0.0.0/0'),
                    )
                ),
                array(
                    'IpProtocol' => 'tcp',
                    'FromPort' => '80',
                    'ToPort' => '80',
                    'IpRanges' => array(
                        array('CidrIp' => '0.0.0.0/0'),
                    )
                )
            )
        );

        if ($includeSsl) {
            // open SSL (Port 443)
            array_push($securityGroupOptions['IpPermissions'], array(
                'IpProtocol' => 'tcp',
                'FromPort' => '443',
                'ToPort' => '443',
                'IpRanges' => array(
                    array('CidrIp' => '0.0.0.0/0'),
                )
            ));
        }

        $response = $this->_ec2->authorize_security_group_ingress($securityGroupOptions);
        if (!$response->isOK()) {
            if ($this->_logger) $this->_logger->emerg(__FILE__ . '(' . __LINE__ .') authorize_security_group_ingress failed');

            $message = (string) $response->body->Errors->Error->Message;
            if ($this->_logger) $this->_logger->crit(__FILE__ . '(' . __LINE__ . ') ' . $message);

            throw new Exception("Response not OK");
        }

        return $this;
    }

    /**
     * @return Siamgeo_Aws_Service fluent interface
     */
    public function createKeyPair()
    {
        // Create a Key Pair and write the .pem file to the file system
        $response = $this->_ec2->create_key_pair($this->_groupName);

        if ($response->isOK()) {
            if (isset($response->body->keyMaterial)) {
                $usernameDir = $this->_customersDataDir . DIRECTORY_SEPARATOR . $this->_username;

                if (!file_exists($usernameDir)) {
                    mkdir($usernameDir);
                    chgrp($usernameDir, 'devteam');
                    chmod($usernameDir, 0777);
                }

                $profileDir = $usernameDir . DIRECTORY_SEPARATOR . $this->_idProfile;
                if (!file_exists($profileDir)) {
                    mkdir($profileDir);
                    chgrp($profileDir, 'devteam');
                    chmod($profileDir, 0777);
                }

                $keyName = (string) $response->body->keyName;

                // write the file to file system e.g customers/ackme/1/ackme-1.pem
                $privateKeyString = (string) $response->body->keyMaterial;

                $privateKeyFullPath = $profileDir . DIRECTORY_SEPARATOR . $keyName . '.pem';

                if (!file_put_contents($privateKeyFullPath, $privateKeyString, LOCK_EX)) {
                    if ($this->_logger) $this->_logger->alert(__FILE__ . '(' . __LINE__ .') Failed to write the private key ' . $keyName . '.pem file to the file system '. $privateKeyFullPath);

                    throw new Exception('Failed to write the ' . $keyName . '.pem file to the filesystem path = ' . $privateKeyFullPath);
                }
                chgrp($privateKeyFullPath, 'devteam');

                // extract the public key from the private key (.pem file)
                $privResource = openssl_pkey_get_private($privateKeyString);
                $details = openssl_pkey_get_details($privResource);
                $publicKeyString = $details['key'];

                $publicKeyFullPath = $profileDir . DIRECTORY_SEPARATOR . $keyName . '.pub';

                if (!file_put_contents($publicKeyFullPath, $publicKeyString, LOCK_EX)) {
                    if ($this->_logger) $this->_logger->alert(__FILE__ . '(' . __LINE__ .') Failed to write the public key ' . $keyName . '.pub file to the file system ' . $publicKeyFullPath);

                    throw new Exception('Failed to write the ' . $keyName . '.pub file to the filesystem path = ' . $publicKeyFullPath);
                }
                chgrp($publicKeyFullPath, 'devteam');


            }
        } else {
            if ($this->_logger) $this->_logger->emerg('call to create_key_pair failed');

            $message = (string) $response->body->Errors->Error->Message;
            if ($this->_logger) $this->_logger->crit(__FILE__ . '(' . __LINE__ . ') ' . $message);

            throw new Exception("Response not OK");
        }

        return $this;
    }

    /**
     * Lanches a new ec2 instance given the instance type
     * uses the security group set by the context
     * e.g. ackme-5
     *
     * @param string $instanceType the type of ec2 instance to launch e.g. t1.micro, m1.small etc
     * @param string $userData base64 encoded user data to be used when launching
     * @throws Exception
     * @return array an assoicative array of response values
     */
    public function runInstances($instanceType, $userData = null)
    {
        // Run an instance
        if ($this->_logger) $this->_logger->debug(__FILE__ . '(' . __LINE__ .') userData=' . (($userData == null) ? 'null' : $userData));

        $options = array(
            'KeyName'               => $this->_groupName,
            'SecurityGroup'         => $this->_groupName,
            'InstanceType'          => $instanceType,
            'Monitoring.Enabled'    => false,
            'DisableApiTermination' => false
        );

        // add the UserData entry is it's sent
        if ($userData)
            $options['UserData'] = $userData;

        // debug logging for diagnosis
        if ($this->_logger) $this->_logger->debug(__FILE__ . '(' . __LINE__ .') Attempting to launch ec2 instance { ami=' . $this->_amiName .', instanceType=' . $instanceType . ' }');
        if ($this->_logger) {
            foreach ($options as $n=>$v) {
                $this->_logger->debug(__FILE__ . '(' . __LINE__ .') $options[' . $n .']=' . $v);
            }
        }

        $response = $this->_ec2->run_instances($this->_amiName, 1, 1, $options);

        if (!$response->isOK()) {
            if ($this->_logger) $this->_logger->emerg('call to run_instances failed');

            $message = (string) $response->body->Errors->Error->Message;
            if ($this->_logger) $this->_logger->crit(__FILE__ . '(' . __LINE__ . ') ' . $message);

            throw new Exception("Response not OK. Call to AWS SDK run_instances failed");
        }

        $body = $response->body;

        $instanceData = array(
            'instanceType'      => (string) $body->instancesSet->item->instanceType,
            'instanceName'      => (string) $body->instancesSet->item->instanceId,
            'amiName'           => (string) $body->instancesSet->item->imageId,
            'akiName'           => (string) $body->instancesSet->item->kernelId,
            'availabilityZone'  => (string) $body->instancesSet->item->placement->availabilityZone,
            'reservationId'     => (string) $body->reservationId,
            'ownerId'           => (string) $body->ownerId,
            'launchTime'        => (string) $body->instancesSet->item->launchTime,
            'instanceStateCode' => (string) $body->instancesSet->item->instanceState->code,
            'instanceStateName' => (string) $body->instancesSet->item->instanceState->name,
            'reservationId'     => (string) $body->reservationId,
        );

        return $instanceData;
    }

    public function describeInstances($instanceName)
    {
        if ($this->_logger) $this->_logger->debug(__FILE__ . '(' . __LINE__ .') Attempting to describe_instance with instanceName=' . $instanceName);
        $response = $this->_ec2->describe_instances(array(
            'InstanceId' => $instanceName,
        ));

        if (!$response->isOK()) {
            if ($this->_logger) $this->_logger->emerg('call to describe_instances failed');

            throw new Exception("Response not OK");
        }

        $code           = (string) $response->body->reservationSet->item->instancesSet->item->instanceState->code;
        $name           = (string) $response->body->reservationSet->item->instancesSet->item->instanceState->name;
        $privateDnsName = (string) $response->body->reservationSet->item->instancesSet->item->privateDnsName;
        $dnsName        = (string) (string) $response->body->reservationSet->item->instancesSet->item->dnsName;

        if ($this->_logger) $this->_logger->debug(__FILE__ . '(' . __LINE__ .') describe_instance returned code=' . $code . ', name=' . $name . ', privateDnsName=' . $privateDnsName . ', dnsName=' . $dnsName);

        return array(
            'code'           => $code,
            'name'           => $name,
            'privateDnsName' => $privateDnsName,
            'dnsName'        => $dnsName
        );
    }

    public function associateAddress($instanceName, $publicIp)
    {
        $response = $this->_ec2->associate_address($instanceName, $publicIp);
        if (!$response->isOK()) {
            if ($this->_logger) $this->_logger->emerg(__FILE__ . '(' . __LINE__ .') call to associate_address failed');

            $message = (string) $response->body->Errors->Error->Message;
            if ($this->_logger) $this->_logger->crit(__FILE__ . '(' . __LINE__ . ') ' . $message);

            throw new Exception("Response not OK");
        }
    }
}
