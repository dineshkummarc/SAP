<?php
/**
 * User: peaceman
 * Date: 4/15/12
 * Time: 3:53 PM
 */
namespace SAP\View\Helper;

class FlashMessenger extends \Zend_View_Helper_Abstract
{
    /**
     * @var $_actionHelper \SAP\Controller\Action\Helper\FlashMessenger
     */
    protected $_actionHelper;

    public function __construct()
    {
        $this->_actionHelper = \Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
    }

    public function flashMessenger()
    {
        $currentMessages = $this->_actionHelper->getMessagesForCurrentRequest();
        $toReturn = '';
		$class = '';

        foreach ($currentMessages as $type => $messages) {
            foreach ($messages as $message) {
                switch ($type) {
                    case 'info':
                        $class = 'alert-info';
                        break;
                    case 'success':
                        $class = 'alert-success';
                        break;
                    case 'warn':
                        $class = 'alert-block';
                        break;
                    case 'error':
                        $class = 'alert-error';
                        break;
                }

                $toReturn .= sprintf('<div class="alert %s">%s</div>', $class, $message);
            }
        }

        return $toReturn;
    }
}
