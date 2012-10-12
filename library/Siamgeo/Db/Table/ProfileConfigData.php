<?php
class Siamgeo_Db_Table_ProfileConfigData extends Zend_Db_Table_Abstract
{
    protected $_name = 'ProfileConfigData';
    protected $_primary = 'idProfileConfigData';

    public function addProfileConfigData($idProfile, $name, $value)
    {
        $nowExpr  = new Zend_Db_Expr('NOW()');
        $nullExpr = new Zend_Db_Expr('NULL');

        $data = array(
            'idProfileConfigData' => $nullExpr,
            'idProfile'           => (int) $idProfile,
            'name'                => $name,
            'value'               => $value,
            'added'               => $nowExpr,
            'updated'             => $nowExpr
        );

        try {
            $this->insert($data);
            $rowId = $this->_db->lastInsertId();

            return $rowId;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param int $idProfile the profile id
     * @param string $name the name of the config data
     */
    public function getProfileConfigDataByName($idProfile, $name)
    {
        $query = $this->select()
                      ->where('idProfile = ?', (int) $idProfile)
                      ->where('name = ?', $name)
                      ->limit(1);
        try {
            $row = $this->fetchRow($query);

            return $row;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getAllProfileConfigDataByProfileId($idProfile)
    {
        $query = $this->select()
                      ->where('idProfile = ?', (int) $idProfile);
        try {
            $rowset = $this->fetchAll($query);

            return $rowset;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function updateProfileConfigData($idProfile, $name, $value)
    {
        $params = array(
            'value'   => $value,
            'updated' => new Zend_Db_Expr('NOW()')
        );

        $where = $this->getAdapter()->quoteInto('idProfile = ?', (int) $idProfile);
        $where .= $this->getAdapter()->quoteInto(' AND name = ?', $name);

        try {
            $query = $this->update($params, $where);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
