<?php
/**
 * User: peaceman
 * Date: 4/24/12
 * Time: 10:49 PM
 */
class Application_Form_ServerSettingEdit extends \SAP\Form
{
	/**
	 * @var Application_Model_ServerSetting
	 */
	protected $_model;

	public function init()
	{
		$defaultValueField = new Zend_Form_Element_Text('default_value');
		$defaultValueField->setLabel('Default value')
			->setValue($this->_model->getDefaultValue());

		$submitButton = new Zend_Form_Element_Submit('edit');

		$this->addElements(array(
			$defaultValueField,
			$submitButton,
		));
	}
}