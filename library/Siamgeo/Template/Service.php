<?php
class Siamgeo_Template_Service
{
    private $_logger;

    private $_templateEngine;
    private $_profileConfigDataService;

    public function __construct(Siamgeo_Template_Engine $templateEngine, Siamgeo_Service_ProfileConfigDataService $profileConfigDataService)
    {
        $this->_templateEngine = $templateEngine;
        $this->_profileConfigDataService = $profileConfigDataService;

        $this->_logger = Zend_Registry::get('logger');
    }

    private function logHash($name, $hash)
    {
        if ($this->_logger) {
            foreach ($hash as $n=>$v) {
                $this->_logger->debug(__FILE__ . '(' . __LINE__ . ') $' . $name . ' ' . $n . '=' . $v);
            }
        }
    }

    public function processTemplates($username, $idProfile, $options)
    {
        $this->_templateEngine->loadTemplate('apache-vhost-template.txt');
        $this->_templateEngine->loadTemplate('temp-apache-vhost-template.txt');
        $this->_templateEngine->loadTemplate('setup-server.txt');
        $this->_templateEngine->loadTemplate('magento-url-change');
        $this->_templateEngine->loadTemplate('cloud-init-ssh-only.txt');
        $this->_templateEngine->loadTemplate('go.txt');

        $configData = $this->_profileConfigDataService->getConfigList($idProfile);
        $configData['profile-dir'] = $options['customersDataDir']
                                   . DIRECTORY_SEPARATOR . $username
                                   . DIRECTORY_SEPARATOR . $idProfile;

        $this->logHash('configData', $configData);

        // fill the templates with the values from the model
        $this->_templateEngine->fillTemplate('apache-vhost-template.txt', $configData);
        $this->_templateEngine->fillTemplate('temp-apache-vhost-template.txt', $configData);
        $this->_templateEngine->fillTemplate('setup-server.txt', $configData);
        $this->_templateEngine->fillTemplate('magento-url-change', $configData);
        $this->_templateEngine->fillTemplate('go.txt', $configData);

        $this->_templateEngine->setCustomerDataDirectory($options['customersDataDir']);
        $this->_templateEngine->setContext($username, $idProfile);

        $this->_templateEngine->saveTemplate('apache-vhost-template.txt', $configData['server-name']);
        $this->_templateEngine->saveTemplate('temp-apache-vhost-template.txt', $configData['temp-server-name']);
        $this->_templateEngine->saveTemplate('setup-server.txt', 'server-setup.sh');
        $this->_templateEngine->saveTemplate('magento-url-change', 'magento-url-change');
        $this->_templateEngine->saveTemplate('cloud-init-ssh-only.txt', 'cloud-init-ssh-only.txt');
        $this->_templateEngine->saveTemplate('go.txt', 'go.sh', 0755);
    }
}
