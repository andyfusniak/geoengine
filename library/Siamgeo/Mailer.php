<?php
class Siamgeo_Mailer
{
    /**
     * @var Zend_Mail
     */
    protected $_mail;
    protected $_templatesDir;
    protected $_template;
    protected $_fromEmail;
    protected $_fromName;
    protected $_sendTo;
    protected $_subject;

    public function __construct()
    {
        $config = Zend_Registry::get('config');

        $this->_templatesDir = $config->email->templatesDir;
        $this->_template = $config->email->errTemplate1;
        $this->_fromEmail = $config->email->defaultReplyTo->from;
        $this->_fromName  = $config->email->defaultReplyTo->name;
        $this->_sendTo    = $config->email->defaultSendTo;

        $this->_subject = "GeoEngine Error";

        $this->_mail = new Zend_Mail('utf-8');
    }

    // send(array('errorMessage' => 'something here'));

    public function send($params)
    {
        $this->_mail->addTo($this->_sendTo)
                    ->setSubject($this->_subject)
                    ->setFrom($this->_fromEmail, $this->_fromName);

        $textFile  = $this->_template . '-txt.phtml';
        if (file_exists($this->_templatesDir . DIRECTORY_SEPARATOR . $textFile)) {
            $textView = new Zend_View();
            $textView->setScriptPath($this->_templatesDir);

            $textView->assign($params);

            $textBody = $textView->render($textFile);
        }

        if ($textBody)
            $this->_mail->setBodyText($textBody);

        $this->_mail->send();
    }
}