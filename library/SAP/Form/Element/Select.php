<?php
/**
 * User: peaceman
 * Date: 4/24/12
 * Time: 11:11 PM
 */
namespace SAP\Form\Element;

class Select extends \Zend_Form_Element_Select
{
	public function loadDefaultDecorators()
	{
		if ($this->loadDefaultDecoratorsIsDisabled()) {
			return $this;
		}

		$decorators = $this->getDecorators();
		if (empty($decorators)) {
			$this->addDecorator('ViewHelper')
				->addDecorator('Errors')
				->addDecorator('Description', array('tag' => 'span', 'class' => 'field_desc'))
				->addDecorator('Label')
				->addDecorator('HtmlTag', array(
				'class' => 'input_field',
				'id'  => array('callback' => array(get_class($this), 'resolveElementId'))
			));
		}

		$this->setAttrib('class', 'formselect_loca');
		return $this;
	}
}