<?php
class Siamgeo_Service_CustomerService
{
    private $_customersTable;

    public function __construct(Siamgeo_Db_Table_Customer $tbl)
    {
        $this->_customersTable = $tbl;
    }

    public function getCustomerUsernameLookup()
    {
        $lookup = array();

        $rowset = $this->_customersTable->getAllCustomers();

        foreach($rowset as $row) {
            $lookup[$row->idCustomer] = $row->username;
        }

        return $lookup;
    }
}