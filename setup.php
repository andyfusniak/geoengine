<?php
define('APP_DIR', getcwd());

define('APPLICATION_ENV', getenv('APPLICATION_ENV'));

if (!APPLICATION_ENV) {
    echo "You must set the environment variable APPLICATION_ENV using export APPLICATION_ENV=<config-profile> where <config-profile> matches the profile in the config.ini file. e.g. japan, live or beta" . PHP_EOL;
    die();
}

set_include_path('.:'. APP_DIR . '/library');

require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance()->registerNamespace('Siamgeo_');

Zend_Registry::set('config', new Zend_Config_Ini('config.ini', APPLICATION_ENV));
Zend_Registry::set('db', Zend_Db::factory('Pdo_Mysql',
    array(
        'host'     => Zend_Registry::get('config')->db->dbhost,
        'username' => Zend_Registry::get('config')->db->dbuser,
        'password' => Zend_Registry::get('config')->db->dbpass,
        'dbname'   => Zend_Registry::get('config')->db->dbname
    )
));
Zend_Db_Table_Abstract::setDefaultAdapter(Zend_Registry::get('db'));

Zend_Registry::set('logger',
    new Zend_Log(new Zend_Log_Writer_Stream(Zend_Registry::get('config')->appLog))
);
