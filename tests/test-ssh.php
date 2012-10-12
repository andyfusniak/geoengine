<?php
require_once 'setup.php';

$deployer = new Siamgeo_Deploy($siamgeo['logger']);

$deployer->setHost('ec2-54-251-48-107.ap-southeast-1.compute.amazonaws.com')
         ->setUsername($config->ssh->username)
         ->setPublicKey($config->ssh->publickey)
         ->setPrivateKey($config->ssh->privatekey)
         ->addTransfer('/var/www/japan.komodo-aws/payload.sh', '/home/ubuntu/payload.sh', 0744)
         ->addTransfer('/var/www/japan.komodo-aws/payload.sh', '/home/ubuntu/payload2.sh', 0744)
         ->addTransfer('/var/www/japan.komodo-aws/payload.sh', '/home/ubuntu/payload3.sh', 0724)
         ->addTransfer('/var/www/japan.komodo-aws/payload.sh', '/home/ubuntu/payload4.sh', 0745)
         ->addTransfer('/var/www/japan.komodo-aws/payload.sh', '/home/ubuntu/payload5.sh', 0746)
         ->scheduleFile('/home/ubuntu/payload.sh')
         ->sendFiles()
         ->executeSchedule();

$output = $deployer->getOutput('/home/ubuntu/payload.sh');

var_dump($output);

/*
$connection = ssh2_connect('ec2-54-251-48-107.ap-southeast-1.compute.amazonaws.com', 22, array('hostkey'=>'ssh-rsa'));

if (ssh2_auth_pubkey_file($connection, 'ubuntu',
                          '/home/andy/.ssh/id_rsa.pub',
                          '/home/andy/.ssh/id_rsa')) {
  echo "Public Key Authentication Successful\n";
} else {
  die('Public Key Authentication Failed');
}

ssh2_scp_send($connection, '/var/www/japan.komodo-aws/payload.sh', '/home/ubuntu/payload.sh', 0744);

$stream = ssh2_exec($connection, "/home/ubuntu/payload.sh", 'xterm');

// force PHP to wait for the output
stream_set_blocking($stream, true);

echo stream_get_contents($stream);

// close the stream
fclose($stream);
*/