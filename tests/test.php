<?php
//require_once 'library/Zend/Log.php';
require_once 'config.php';
require_once 'AwsSdk/sdk.class.php';
require_once 'AwsSdk/services/ec2.class.php';

$configProfile = $config['profile'];

$ec2 = new AmazonEC2(array(
    'certificate_authority' => false,
    // 'credentials' => ''
    // 'default_cache_config' => 'apc'
    'default_cache_config' => '',
    'key'    => $config[$configProfile]['ec2']['key'],
    'secret' => $config[$configProfile]['ec2']['secret']
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
    $publicIp = (string) $response->body->publicIp;
    var_dump($publicIp);
}

/*
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
*/

/*
$response = $ec2->describe_instances(array(
    'InstanceId' => 'i-76393b30',
));
*/

// +--------+---------------+
// |  Code  |     State     |
// +--------+---------------+
// |   0    |    pending    |
// |  16    |    running    |
// |  32    | shutting-down |
// |  48    |  terminated   |
// |  64    |   stopping    |
// |  80    |   stopped     |
// +--------+---------------+

/*
$code = (int) $response->body->reservationSet->item->instancesSet->item->instanceState->code;
$name = (string) $response->body->reservationSet->item->instancesSet->item->instanceState->name;

while ($code != 16) {
    sleep(30);

    $response = $ec2->describe_instances(array(
        'InstanceId' => 'i-76393b30',
    ));

    $code = (int) $response->body->reservationSet->item->instancesSet->item->instanceState->code;
}

$instanceId = (string) $response->body->instancesSet->item->instanceId;
$response = $ec2->associate_address($instanceId, $publicIp);
var_dump($response);
var_dump($code, $name);
*/

//$response = $ec2->describe_regions();
//if ($response->isOK()) {
    $items = $response->body->regionInfo->item;

//    foreach ($items as $i) {
//        var_dump($i->regionName->to_array());
//    }
//}

var_dump($response);
die();
