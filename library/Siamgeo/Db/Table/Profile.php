<?php
class Siamgeo_Db_Table_Profile extends Zend_Db_Table_Abstract
{
    protected $_name = 'Profiles';
    protected $_primary = 'idProfile';

    const PENDING                        = 'pending';
    const PASSED_ALLOCATED_IP_ADDRESS    = 'allocated-ip-address';
    const PASSED_SECURITY_GROUP_CREATED  = 'security-group-created';
    const PASSED_KEY_PAIR_GENERATED      = 'key-pair-generated';
    const PASSED_INSTANCE_STARTED        = 'instance-started';
    const PASSED_ASSOCIATED_ADDRESS      = 'associated-address';
    const PASSED_GENERATED_DEPLOY_FILES  = 'generated-deploy-files';
    const PASSED_EXECUTED_AND_SENT_FILES = 'executed-remote-files';
    const COMPLETED                      = 'completed';

    public function getAllProfilesByCustomerId($idCustomer)
    {
        $query = $this->select()
                      ->where('idCustomer = ?', (int) $idCustomer);

        //var_dump($query->__toString());
        //die();

        try {
            $rowset = $this->fetchAll($query);

            return $rowset;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getAllNonCompletedProfiles()
    {
        $query = $this->select()
                      ->where('status != ?', 'completed')
                      ->order('added ASC');
        try {
            $rowset = $this->fetchAll($query);

            return $rowset;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function updateStatus($idProfile, $status)
    {
        $params = array(
            'status'  => $status,
            'updated' => new Zend_Db_Expr('NOW()')
        );

        $where = $this->getAdapter()->quoteInto('idProfile = ?', (int) $idProfile);

        try {
            $query = $this->update($params, $where);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function updatePublicIp($idProfile, $publicIp)
    {
        $params = array(
            'publicIp' => $publicIp,
            'updated'  => new Zend_Db_Expr('NOW()')
        );

        $where = $this->getAdapter()->quoteInto('idProfile = ?', (int) $idProfile);

        try {
            $query = $this->update($params, $where);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
