<?php

/**
 *
 * @package    MUtil
 * @subpackage Form\Element
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2016, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 */

namespace MUtil\Form\Element;

/**
 *
 * @package    MUtil
 * @subpackage Form\Element
 * @copyright  Copyright (c) 2016, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 * @since      Class available since version 1.8.2 Feb 7, 2017 5:17:48 PM
 */
trait NoTagsElementTrait
{
    /**
     * Flag indicating whether or not to insert NoTags validator
     * @var bool
     */
    protected $_autoInsertNoTagsValidator = true;

    /**
     * Add no tags validator if not already set
     *
     * @return MUtil\Form\Element\NoTagsElementTrait
     */
    public function addNoTagsValidator()
    {
        if (!$this->getValidator('NoTags')) {
            $this->addValidator('NoTags');
        }

        return $this;
    }

    /**
     * Get flag indicating whether a NoTags validator should be inserted
     *
     * @return bool
     */
    public function autoInsertNoTagsValidator()
    {
        return $this->_autoInsertNoTagsValidator;
    }

    /**
     * Validate element value
     *
     * If a translation adapter is registered, any error messages will be
     * translated according to the current locale, using the given error code;
     * if no matching translation is found, the original message will be
     * utilized.
     *
     * Note: The *filtered* value is validated.
     *
     * @param  mixed $value
     * @param  mixed $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        if ($this->autoInsertNoTagsValidator()) {
            $this->addNoTagsValidator();
        }
        return parent::isValid($value, $context);
    }


    /**
     * Set flag indicating whether a NoTags validator should be inserted
     *
     * @param  bool $flag
     * @return MUtil\Form\Element\NoTagsElementTrait
     */
    public function setAutoInsertNoTagsValidator($flag)
    {
        $this->_autoInsertNoTagsValidator = (bool) $flag;
        return $this;
    }
}
