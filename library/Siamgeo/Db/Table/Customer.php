<?php
class Siamgeo_Db_Table_Customer extends Zend_Db_Table_Abstract
{
    protected $_name = 'Customers';
    protected $_primary = 'idCustomer';

    public function addCustomer($params)
    {
        $nowExpr  = new Zend_Db_Expr('NOW()');

        $data = array(
            'idCustomer'      => new Zend_Db_Expr('NULL'),
            'username'        => $params['username'],
            'firstname'       => $params['firstname'],
            'added'           => $nowExpr,
            'updated'         => $nowExpr
        );

        try {
            $this->insert($data);
            $rowId = $this->_db->lastInsertId();

            return $rowId;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getAllCustomers()
    {
        $query = $this->select()
                      ->order('idCustomer ASC');
        try {
            $rowset = $this->fetchAll($query);

            return $rowset;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getCustomerByCustomerId($idCustomer)
    {
        $query = $this->select()
                      ->where('idCustomer = ?', (int) $idCustomer)
                      ->limit(1);
        try {
            $obj = $this->fetchRow($query);

            return $obj;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function updateCustomerByCustomerId($idCustomer, $data)
    {
        $params = array(
            'firstname' => $data['firstname'],
            'updated'   => new Zend_Db_Expr('NOW()')
        );

        $where = $this->getAdapter()->quoteInto('idCustomer = ?', (int) $idCustomer);

        try {
            $query = $this->update($params, $where);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
