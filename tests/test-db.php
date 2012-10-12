<?php
require_once 'setup.php';

//$customerTable = new Siamgeo_Db_Table_Customer();

//$customerTable->updateCustomerByCustomerId(4, array(
//    'firstname' => 'Jon'
//));

//$obj = $customerTable->getCustomerByCustomerId(4);

//$obj->delete();

//var_dump($obj->idCustomer, $obj->username, $obj->firstname);


// add a new customer
//try {
//    $rowId = $customerTable->addCustomer(array(
//        'username'  => 'johndepp',
//        'firstname' => 'Johny'
//    ));

//    echo "The new row was " . $rowId . PHP_EOL;
//} catch (Exception $e) {
//   echo "I couldn't add a new customer :(";
//}


$profileConfigDataTable = new Siamgeo_Db_Table_ProfileConfigData();
$profileConfigDataService = new Siamgeo_Service_ProfileConfigDataService($profileConfigDataTable);

$profileConfigDataService->setConfig(3, "thing", "yahoo!!", 'fruit');
$profileConfigDataService->setConfig(3, "thing2", "banana");
$profileConfigDataService->setConfig(3, "thing3", "apples");
$profileConfigDataService->setConfig(3, "thing4", "google");

$mylist = array(
    "thing1" => "value1",
    "thing2" => "value2",
    "thing3" => "value3",
    "thing4" => "value4",
    "thing5" => "value5"
);

$profileConfigDataService->setConfigList(3, $mylist, 'apache');

$profileConfigData = $profileConfigDataService->getConfigList(3, array('apache', 'magento'));

var_dump($profileConfigData);

//$profileTable = new Siamgeo_Db_Table_Profile();
//$rowset = $profileTable->getAllProfilesByCustomerId(1);

//foreach ($rowset as $row) {
//    echo "Profile Id: " . $row->idProfile . PHP_EOL;
//}

/*

$instanceTable = new Siamgeo_Db_Table_Instance();
$obj = $instanceTable->getInstanceByInstanceName('i-123456');

if (null === $obj) {
    echo "Couldn't find the instance";
    exit;
}

echo $obj->regionName;
*/