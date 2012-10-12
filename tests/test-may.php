<?php
//require_once 'library/Zend/Log.php';

require_once 'sdk.class.php';
require_once 'services/ec2.class.php';

$ec2 = new AmazonEC2(array(
    'certificate_authority' => false,
    // 'credentials' => ''
    // 'default_cache_config' => 'apc'
    'default_cache_config' => '',

    'key' => 'AKIAIOQSA6HMFEUCWMAQ',
    'secret' => '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'
    // 'token' => ''
));

$username     = 'ackme';
$profileId    = '00002';
$groupName    = $username . '-' . $profileId;
$ami          = 'ami-87712ac2';
$instanceType = 't1.micro';

// set the region
$ec2->set_region(AmazonEC2::REGION_US_W1);

// allocate a new ip addres
$response = $ec2->allocate_address();
if ($response->isOK()) {
    $ipAddress = $response->body->publicIp;
}

// create a security group for this profile
$response = $ec2->create_security_group($groupName, $groupName);
if ($response->isOK()) {
    $groupId = (string) $response->body->groupId;
    $requestId = (string) $response->body->requestId;
    //var_dump($groupId, $requestId);
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

// open SSL (Port 443)
array_push($securityGroupOptions['IpPermissions'], array(
    'IpProtocol' => 'tcp',
    'FromPort' => '443',
    'ToPort' => '443',
    'IpRanges' => array(
        array('CidrIp' => '0.0.0.0/0'),
    )
));

$response = $ec2->authorize_security_group_ingress($securityGroupOptions);

// Create a Key Pair and write the .pem file to the file system
$response = $ec2->create_key_pair($groupName);
if ($response->isOK()) {
    if (isset($response->body->keyMaterial)) {
        file_put_contents($response->body->keyName . '.pem', (string) $response->body->keyMaterial);
    }
} else {
    var_dump($response);
}

// Run an instance
$response = $ec2->run_instances($ami, 1, 1, array(
    'KeyName'            => $groupName,
    'SecurityGroup'      => $groupName,
    'InstanceType'       => $instanceType,
    'Monitoring.Enabled' => false,
    'DisableApiTermination' => false
));

$response = $ec2->describe_regions();
if ($response->isOK()) {
    $items = $response->body->regionInfo->item;

    foreach ($items as $i) {
        var_dump($i->regionName->to_array());
    }
}

var_dump($response);
die();

