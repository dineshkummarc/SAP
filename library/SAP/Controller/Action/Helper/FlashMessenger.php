<?php
/**
 * User: peaceman
 * Date: 4/15/12
 * Time: 3:55 PM
 */
namespace SAP\Controller\Action\Helper;

class FlashMessenger extends \Zend_Controller_Action_Helper_Abstract
{
    const INFO = 'info';
    const SUCCESS = 'success';
    const WARN = 'warn';
    const ERROR = 'error';

    static protected $_validMessageTypes = array(
        self::INFO,
        self::SUCCESS,
        self::WARN,
        self::ERROR,
    );

    protected $_messagesForNextRequest = array();
    protected $_messagesForCurrentRequest = array();
    protected $_session;

    public function __construct()
    {
        $this->_session = new \Zend_Session_Namespace('flashMessenger');
        $this->_messagesForCurrentRequest = $this->_session->getIterator()->getArrayCopy();
        $this->_session->unsetAll();
    }

    protected function _addMessage($message, $type, $currentRequest)
    {
        $this->_checkForValidType($type);

        if ($currentRequest) {
            $this->_messagesForCurrentRequest[$type][] = $message;
        } else {
            $this->_session->{$type}[] = $message;
		}
    }

    protected function _checkForValidType($type)
    {
        if (false === array_search($type, self::$_validMessageTypes)) {
            throw new \InvalidArgumentException('invalid type ' . $type);
        }
    }

    public function addInfoMessage($message, $currentRequest = false)
    {
        $this->_addMessage($message, self::INFO, $currentRequest);
    }

    public function addSuccessMessage($message, $currentRequest = false)
    {
        $this->_addMessage($message, self::SUCCESS, $currentRequest);
    }

    public function addWarnMessage($message, $currentRequest = false)
    {
        $this->_addMessage($message, self::WARN, $currentRequest);
    }

    public function addErrorMessage($message, $currentRequest = false)
    {
        $this->_addMessage($message, self::ERROR, $currentRequest);
    }

    public function hasMessageForCurrentRequest()
    {
        return count($this->_messagesForCurrentRequest) > 0;
    }

    public function getMessagesForCurrentRequest(array $types = array())
    {
        if (empty($types)) {
            return $this->_messagesForCurrentRequest;
        } else {
            $toReturn = array();

            foreach ($types as $type) {
                if (!isset($this->_messagesForCurrentRequest[$type])) {
                    continue;
                }

                $toReturn[$type] = $this->_messagesForCurrentRequest[$type];
            }

            return $toReturn;
        }
    }
}
