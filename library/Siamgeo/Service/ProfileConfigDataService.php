<?php
class Siamgeo_Service_ProfileConfigDataService
{
    private $_profileConfigDataTable;

    public function __construct(Siamgeo_Db_Table_ProfileConfigData $profileConfigDataTable)
    {
        $this->_profileConfigDataTable = $profileConfigDataTable;
    }

    public function setConfig($idProfile, $name, $value)
    {
        $row = $this->_profileConfigDataTable->getProfileConfigDataByName($idProfile, $name);

        if (null === $row) {
            $this->_profileConfigDataTable->addProfileConfigData($idProfile, $name, $value);
        } else {
            $this->_profileConfigDataTable->updateProfileConfigData($idProfile, $name, $value);
        }

        return $this;
    }

    public function setConfigList($idProfile, $list)
    {
        foreach ($list as $name=>$value) {
            $this->setConfig($idProfile, $name, $value);
        }

        return $this;
    }

    /**
     * @param int $idProfile the profile id
     * @return array an associative array of name/value pairs for this profile
     */
    public function getConfigList($idProfile)
    {
        $hashMap = array();

        $rowset = $this->_profileConfigDataTable->getAllProfileConfigDataByProfileId($idProfile);
        foreach ($rowset as $row) {
            $hashMap[$row->name] = $row->value;
        }

        return $hashMap;
    }
}
