<?php
/**
 * User: peaceman
 * Date: 4/20/12
 * Time: 11:22 PM
 */
class Application_Form_ServerSettings extends \SAP\Form
{
	/**
	 * @var Application_Model_Server
	 */
	protected $_model;

	public function init()
	{
		$serverSettings = $this->_model->getServerSettings();

		foreach ($serverSettings as $serverSetting) {
			$element = $this->_getElementForServerSetting($serverSetting);
			$this->addElement($element);
		}
	}

	protected function _getElementForServerSetting(Application_Model_ServerSetting2Server $serverSetting)
	{
		$name = $serverSetting->getSetting()->getName();

		switch ($name) {
			case 'MaxUser':
				$element = new \SAP\Form\Element\Text($name);
				$element->setRequired(true)
					->setAllowEmpty(false)
					->addValidator(new Zend_Validate_Int(), true)
					->addValidator(new Zend_Validate_GreaterThan(0));
				break;
			case 'Password':
				$element = new \SAP\Form\Element\Text($name);
				$element->setRequired(true)
					->setAllowEmpty(false)
					->addValidator(new Zend_Validate_Alnum(false));
				break;
			case 'PortBase':
				$element = new \SAP\Form\Element\Text($name);
				$element->setRequired(true)
					->setAllowEmpty(false)
					->addValidator(new Zend_Validate_Int(), true)
					->addValidator(new Zend_Validate_GreaterThan(1024));
				break;
			case 'ShowLastSongs':
				$element = new \SAP\Form\Element\Text($name);
				$element->setRequired(true)
					->setAllowEmpty(false)
					->addValidator(new Zend_Validate_Int(), true)
					->addValidator(new Zend_Validate_GreaterThan(0))
					->addValidator(new Zend_Validate_LessThan(21));
				break;
			case 'SrcIP':
				$element = new \SAP\Form\Element\Text($name);
				$element->setRequired(true)
					->setAllowEmpty(false)
					->addValidator(new Zend_Validate_Callback(array($this, 'validateIpOrAny')));
				break;
			case 'AdminPassword':
				$element = new \SAP\Form\Element\Text($name);
				$element->setRequired(true)
					->setAllowEmpty(false)
					->addValidator(new Zend_Validate_Alnum(false));
				break;
			case 'AutoDumpUsers':
				$element = new Zend_Form_Element_Checkbox($name);
				$element->setRequired(true)
					->setAllowEmpty(false);
				break;
			case 'AutoDumpSourceTime':
				$element = new \SAP\Form\Element\Text($name);
				$element->setRequired(true)
					->setAllowEmpty(false)
					->addValidator(new Zend_Validate_Int())
					->addValidator(new Zend_Validate_GreaterThan(0));
				break;
			case 'IntroFile':
				$element = new \SAP\Form\Element\Text($name);
				break;
			case 'BackupFile':
				$element = new \SAP\Form\Element\Text($name);
				break;
			case 'TitleFormat':
				$element = new \SAP\Form\Element\Text($name);
				break;
			case 'URLFormat':
				$element = new \SAP\Form\Element\Text($name);
				break;
			case 'PublicServer':
				$element = new Zend_Form_Element_Select($name);
				$element->setRequired(true)
					->setAllowEmpty(false)
					->setMultiOptions(array(
						'default',
						'always',
						'never',
					));
				break;
			case 'AllowRelay':
				$element = new Zend_Form_Element_Checkbox($name);
				$element->setRequired(true)
					->setAllowEmpty(false);
				break;
			case 'AllowPublicRelay':
				$element = new Zend_Form_Element_Checkbox($name);
				$element->setRequired(true)
					->setAllowEmpty(false);
				break;
			case 'MetaInterval':
				$element = new \SAP\Form\Element\Text($name);
				$element->setRequired(true)
					->setAllowEmpty(false)
					->addValidator(new Zend_Validate_Int());
				break;
		}

		$element->setLabel($name)
			->setValue($serverSetting->getValue());

		return $element;
	}

	/**
	 * @param string $srcIp
	 * @return bool
	 */
	public function validateIpOrAny($srcIp)
	{
		static $ipValidator;

		if (strtolower($srcIp) === 'any') {
			return true;
		}

		if ($ipValidator === null) {
			$ipValidator = new Zend_Validate_Ip();
		}

		return $ipValidator->isValid($srcIp);
	}
}