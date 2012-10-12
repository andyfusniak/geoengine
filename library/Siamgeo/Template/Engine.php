<?php
class Siamgeo_Template_Engine
{
    private $_serverTemplateDir;
    private $_username;
    private $_idProfile;
    private $_templates = array();

    public function __construct()
    {
        $config = Zend_Registry::get('config');
        $this->_serverTemplateDir = $config->serverTemplateDir;
    }

    public function setServerTemplateDir($serverTemplateDir)
    {
        $this->_serverTemplateDir = $serverTemplateDir;

        return $this;
    }

    public function getServerTemplateDir()
    {
        return $this->_serverTemplateDir;
    }

    public function loadTemplate($filename)
    {
        $templateFullPath = $this->_serverTemplateDir . DIRECTORY_SEPARATOR . $filename;
        $contents = file_get_contents($templateFullPath);

        if (!$contents)
            throw new Exception("Failed to load the template " . $templateFullPath);

        // take a private copy
        $this->_templates[$filename] = $contents;

        return $this;
    }

    public function getCompleteFile($filename)
    {
        if (!isset($this->_templates[$filename]))
            throw new Exception("No original template for " . $filename . " exists");

        return $this->_templates[$filename];
    }

    public function fillTemplate($filename, $hash)
    {
        if (!isset($this->_templates[$filename]))
            throw new Exception("Template " . $filename . " has not been loaded");

        foreach ($hash as $name=>$value) {
            $this->_templates[$filename] = str_replace("%$name%", $value, $this->_templates[$filename]);
        }

        return $this;
    }

    public function setCustomerDataDirectory($customerDataDir)
    {
        $this->_customerDataDir = $customerDataDir;
        return $this;
    }

    public function setContext($username, $idProfile)
    {
        $this->_username = $username;
        $this->_idProfile  = $idProfile;
        return $this;
    }

    public function saveTemplate($template, $filename, $mode = null)
    {
        if (!isset($this->_customerDataDir))
            throw new Exception("CustomerDataDirectory not set");

        if (!isset($this->_templates[$template]))
            throw new Exception("Template " . $template . " has not been loaded");

        $fullpath = $this->_customerDataDir
                  . DIRECTORY_SEPARATOR . $this->_username
                  . DIRECTORY_SEPARATOR . $this->_idProfile;

        if (!file_put_contents($fullpath . DIRECTORY_SEPARATOR . $filename, $this->_templates[$template], LOCK_EX))
            throw new Exception("Failed to write template file " . $fullpath);

        if ($mode) {
            chmod($fullpath . DIRECTORY_SEPARATOR . $filename, $mode);
        }

        return $this;
    }
}
