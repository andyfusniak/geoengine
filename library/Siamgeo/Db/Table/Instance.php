<?php
class Siamgeo_Db_Table_Instance extends Zend_Db_Table_Abstract
{
    protected $_name = 'Instances';
    protected $_primary = 'idInstance';


    public function addInstance($idProfile, $params)
    {
        $nowExpr  = new Zend_Db_Expr('NOW()');
        $nullExpr = new Zend_Db_Expr('NULL');

        $data = array(
            'idInstance'        => $nullExpr,
            'idProfile'         => (int) $idProfile,
            'active'            => 'Y',
            'instanceType'      => $params['instanceType'],
            'instanceName'      => $params['instanceName'],
            'amiName'           => $params['amiName'],
            'akiName'           => $params['akiName'],
            'regionName'        => $params['regionName'],
            'availabilityZone'  => $params['availabilityZone'],
            'reservationId'     => $params['reservationId'],
            'ownerId'           => $params['ownerId'],
            'launchTime'        => $params['launchTime'],
            'instanceStateCode' => $params['instanceStateCode'],
            'instanceStateName' => $params['instanceStateName'],
            'stateLastUpdated'  => $nullExpr,
            'added'             => $nowExpr,
            'updated'           => $nowExpr
        );

        try {
            $this->insert($data);
            $rowId = $this->_db->lastInsertId();

            return $rowId;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getAllInstances() {}

    public function getActiveInstanceByProfileId($idProfile)
    {
        $query = $this->select()
                      ->where('idProfile = ?', (int) $idProfile)
                      ->where('active = ?', 'Y')
                      ->limit(1);
        try {
            $obj = $this->fetchRow($query);

            return $obj;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getAllInstancesByProfileId() {}

    public function getInstanceByInstanceName() {}

    public function updateStatus($idInstance, $code, $codeName, $privateDnsName, $dnsName)
    {
        $nowExpr = new Zend_Db_Expr('NOW()');

        $params = array(
            'instanceStateCode' => (int) $code,
            'instanceStateName' => $codeName,
            'privateDnsName'    => $privateDnsName,
            'dnsName'           => $dnsName,
            'stateLastUpdated'  => $nowExpr,
            'updated'           => $nowExpr
        );

        $where = $this->getAdapter()->quoteInto('idInstance = ?', (int) $idInstance);

        try {
            $query = $this->update($params, $where);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
