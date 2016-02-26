<?php

class MUtil_Bootstrap_Form_DisplayGroup extends \Zend_Form_DisplayGroup
{
	/**
     * Load default decorators
     *
     * @return Zend_Form_DisplayGroup
     */
    public function loadDefaultDecorators()
    {
    	 $this->addDecorator('FormElements')
              ->addDecorator('Fieldset');

        return $this;
    }
}