<?php
class Siamgeo_Db_Table_Ami extends Zend_Db_Table_Abstract
{
    protected $_name = 'Amis';
    protected $_primary = 'idAmi';


    public function addAmi($regionName, $amiName)
    {
        $nowExpr  = new Zend_Db_Expr('NOW()');

        $data = array(
            'idAmi'      => new Zend_Db_Expr('NULL'),
            'regionName' => $regionName,
            'amiName'    => $amiName,
            'added'      => $nowExpr,
            'updated'    => $nowExpr
        );

        try {
            $this->insert($data);
            $rowId = $this->_db->lastInsertId();

            return $rowId;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getAmiByRegioName($regionName)
    {
        $query = $this->select()
                      ->where('regionName = ?', $regionName)
                      ->limit(1);

        try {
            $row = $this->fetchRow($query);

            return $row;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getAllAmis()
    {
        $query = $this->select()
                      ->where(1);

        try {
            $rowset = $this->fetchAll($query);

            return $rowset;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function updateAmi($regionName, $amiName)
    {
        $params = array(
            'amiName' => $amiName,
            'updated' => new Zend_Db_Expr('NOW()')
        );

        $where = $this->getAdapter()->quoteInto('regionName = ?', (int) $regionName);

        try {
            $query = $this->update($params, $where);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
