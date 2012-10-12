<?php
class Siamgeo_Service_AmiService
{
    private $_amiTable;

    public function __construct(Siamgeo_Db_Table_Ami $amiTable)
    {
        $this->_amiTable = $amiTable;
    }

    public function setAmi($regionName, $amiName)
    {
        $row = $this->_amiTable->getAmiByRegionName($regionName);

        if (null === $row) {
            $this->_amiTable->addAmi($regionName, $amiName);
        } else {
            $this->_amiTable->updateAmi($regionName, $amiName);
        }

        return $this;
    }

    public function getRegionAmiList()
    {
        $hashMap = array();

        $rowset = $this->_amiTable->getAllAmis();
        foreach ($rowset as $row) {
            $hashMap[$row->regionName] = $row->amiName;
        }

        return $hashMap;
    }
}
