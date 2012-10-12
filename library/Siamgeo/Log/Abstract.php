<?php
abstract class Siamgeo_Log_Abstract
{
    protected $_logger;
    protected $_appDir;

    public function setLogger($logger)
    {
        $this->_logger = $logger;
    }

    public function setAppDir($appDir)
    {
        $this->_appDir = $appDir;
    }

    public function lShortFile($name)
    {
        var_dump($name);
        
    }
}
