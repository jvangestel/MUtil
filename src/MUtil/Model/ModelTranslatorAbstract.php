<?php

/**
 * Copyright (c) 2012, Erasmus MC
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 *    * Neither the name of Erasmus MC nor the
 *      names of its contributors may be used to endorse or promote products
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 *
 * @package    MUtil
 * @subpackage Model_Translator
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @version    $Id: ModelTranslatorAbstract.php 203 2012-01-01 12:51:32Z matijs $
 */

/**
 * Translators can translate the data from one model to be saved using another
 * model.
 *
 * @package    MUtil
 * @subpackage Model_Translator
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Class available since MUtil version 1.3
 */
abstract class MUtil_Model_ModelTranslatorAbstract extends \MUtil_Translate_TranslateableAbstract
    implements \MUtil_Model_ModelTranslatorInterface
{
    /**
     *
     * @var array Names of date elements
     */
    protected $_dateElementNames;

    /**
     * A description that enables users to choose the transformer they need.
     *
     * @var string
     */
    protected $_description;

    /**
     *
     * @var array
     */
    protected $_errors = array();

    /**
     * Local copy of keys of getFieldsTranslation() for speedup.
     *
     * Set by startImport().
     *
     * @var array
     */
    private $_fieldKeys = array();

    /**
     * Local copy of getFieldsTranslation() for speedup.
     *
     * Set by startImport().
     *
     * @var array
     */
    private $_fieldMap = array();

    /**
     * Is mapping of fieldnames required.
     *
     * (Yes unless all names are the same, as in StraightTranslator.)
     *
     * Set by startImport().
     *
     * @var boolean
     */
    private $_mapRequired = null;

    /**
     * The source of the data
     *
     * @var \MUtil_Model_ModelAbstract
     */
    protected $_sourceModel;

    /**
     * The target of the data
     *
     * @var \MUtil_Model_ModelAbstract
     */
    protected $_targetModel;

    /**
     * Date import format
     *
     * @var string
     */
    public $dateFormat = 'YYYY-MM-DD';

   /**
    * Optional locale for date interpreations
    *
    * @var \Zend_Locale or string to create locale
    */
    public $dateLocale;

    /**
     * Datetime import format
     *
     * @var string
     */
    public $datetimeFormat = \Zend_Date::ISO_8601;

    /**
     * Time import format
     *
     * @var string
     */
    public $timeFormat = \Zend_Date::TIMES;

    /**
     * The string value used for NULL values
     *
     * @var string Uppercase string
     */
    public $nullValue = 'NULL';

    /**
     * The task used for import
     *
     * @var string
     */
    protected $saveTask = 'Import_SaveToModel';

    /**
     * The form used to validate the input values
     *
     * @var \Zend_Form
     */
    public $targetForm;

    /**
     *
     * @param string $description A description that enables users to choose the transformer they need.
     */
    public function __construct($description = '')
    {
        $this->setDescription($description);
    }

    /**
     * Register additional errors
     *
     * @param array $messages or single string message
     * @param mixed $key Row key
     * @param array $row Optional for filtering errors not in the import
     */
    protected function _addErrors($messages, $key, array $row = null)
    {
        if (is_array($messages)) {
            if (is_array($row)) {
                // Remove errors for elements not in the import
                $messages = array_intersect_key($messages, $row);
            }
        } elseif ($messages) {
            $messages = (array) $messages;
        }

        if ($messages) {
            if (isset($this->_errors[$key])) {
                $this->_errors[$key] = array_merge($this->_errors[$key], $messages);
            } else {
                $this->_errors[$key] = $messages;
            }
        }
    }

    /**
     * Check the form and it's sub elements, currently just for date elements
     *
     * @param array $elements Of name => element
     */
    protected function _checkForm(array $elements)
    {
        foreach ($elements as $name => $element) {
            if ($element instanceof \MUtil_JQuery_Form_Element_DatePicker) {
                $this->_dateElementNames[] = $name;
            } elseif (($element instanceof \Zend_Form_DisplayGroup) || ($element instanceof \Zend_Form)) {
                $this->_checkForm($element->getElements());
            }
        }
    }

    /**
     * Create an empty form for filtering and validation
     *
     * @return \MUtil_Form
     */
    protected function _createTargetForm()
    {
        return new \MUtil_Form();
    }

    /**
     *
     * @return array
     */
    protected function _getFilters()
    {
        $filters = array();
        foreach ($this->_targetModel->getCol('filter') as $name => $filter) {
            $filters[$name] = $filter;
        }

        return array_merge_recursive(
                $filters,
                $this->_targetModel->getCol('filters')
                );
    }

    /**
     * Create a form for filtering and validation, populating it
     * with elements.
     *
     * @return \MUtil_Form
     */
    protected function _makeTargetForm()
    {
        $form = $this->_createTargetForm();
        $form->setTranslator($this->translate);

        $bridge = $this->_targetModel->getBridgeFor('form', $form);

        foreach($this->getFieldsTranslations() as $sourceName => $targetName) {
            if ($this->_targetModel->get($targetName, 'label')) {
                $options = $this->_targetModel->get($targetName, 'multiOptions');
                if ($options) {
                    $filter = new \MUtil_Filter_LooseArrayFilter(
                            $options,
                            $this->_targetModel->get($targetName, 'extraValueKeys')
                            );
                    $element = $bridge->add($targetName);
                    if ($element && method_exists($element, 'addFilter')) {
                        $element->addFilter($filter);
                    }
                } else {
                    $bridge->add($targetName);
                }
            } else {
                $bridge->addHidden($targetName);
            }
        }

        return $form;
    }

    /**
     * Default preparation for row import.
     *
     * @param mixed $row array or \Traversable row
     * @param scalar $key
     * @return array or boolean
     * @throws \MUtil_Model_ModelException
     */
    protected function _prepareRow($row, $key)
    {
        if (null === $this->_mapRequired) {
            throw new \MUtil_Model_ModelException("Trying to translate without call to startImport().");
        }

        if ($row instanceof \Traversable) {
            $row = iterator_to_array($row);
        }

        if (! (is_array($row) && $row)) {
            // Do not bother with non array data
            return false;
        }

        $rowMap = array_intersect($this->_fieldKeys, array_keys($row));
        if (! $rowMap) {
            $this->_errors[$key][] = $this->_("No field overlap between source and target");
            return false;
        }

        if ($this->_mapRequired) {
            // This does keep the original values. That is intentional.
            foreach ($rowMap as $source) {
                if (isset($row[$source])) {
                    $row[$this->_fieldMap[$source]] = $row[$source];
                }
            }
        }

        return $row;
    }

    /**
     * Add the current row to a (possibly separate) batch that does the importing.
     *
     * @param \MUtil_Task_TaskBatch $importBatch The import batch to impor this row into
     * @param string $key The current iterator key
     * @param array $row translated and validated row
     * @return \MUtil_Model_ModelTranslatorAbstract (continuation pattern)
     */
    public function addSaveTask(\MUtil_Task_TaskBatch $importBatch, $key, array $row)
    {
        $importBatch->setTask($this->saveTask, 'import-' . $key, $row);
        return $this;
    }

    /**
     * Translate textual null values to actual PHP nulls and trim any whitespace
     *
     * @param mixed $value
     * @param scalar $key The array key, optionally a model key as well
     * @return mixed
     */
    public function filterBasic(&$value, $key)
    {
        if (is_string($value) && ($this->nullValue === strtoupper($value))) {
            $value = null;
            return;
        }

        if ($this->_targetModel instanceof \MUtil_Model_ModelAbstract) {
            if ($this->_targetModel->is($key, 'type', \MUtil_Model::TYPE_DATE)) {
                $format = $this->dateFormat;
            } elseif ($this->_targetModel->is($key, 'type', \MUtil_Model::TYPE_DATETIME)) {
                $format = $this->datetimeFormat;
            } elseif ($this->_targetModel->is($key, 'type', \MUtil_Model::TYPE_TIME)) {
                $format = $this->timeFormat;
            } else {
                $format = false;
            }

            if ($this->dateLocale && is_string($this->dateLocale)) {
                $this->dateLocale = new \Zend_Locale($this->dateLocale);
            }

            if ($format && \Zend_Date::isDate($value, $format, $this->dateLocale)) {
                $value = new \MUtil_Date($value, $format, $this->dateLocale);
                return;
            }

            $options = $this->_targetModel->get($key, 'multiOptions');
            if ($options && (! isset($options[$value])) && in_array($value, $options)) {
                $value = array_search($value, $options);
            }
        }

        if (is_string($value)) {
            $value = trim($value);
            return;
        }

        return;
    }

    /**
     * Returns a description of the translator to enable users to choose
     * the translator they need.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Get information on the field translations
     *
     * @return array of fields sourceName => targetName
     * @throws \MUtil_Model_ModelException
     */
    // public function getFieldsTranslations();

    /**
     * Returns an array of the field names that are required
     *
     * @return array of fields sourceName => targetName
     */
    public function getRequiredFields()
    {
        $trans  = $this->getFieldsTranslations();
        $keys   = array_fill_keys($this->_targetModel->getKeys(), true);

        $output = array();
        foreach ($trans as $input => $source) {
            if (isset($keys[$source])) {
                $output[$input] = $source;
            }
        }

        return $output;
    }

    /**
     * Returns a description of the translator errors.
     *
     * @return array of String messages
     */
    public function getErrors()
    {
        $errorOutput = array();
        foreach ($this->_errors as $row => $rowErrors) {
            $rowErrors = $this->getRowErrors($row);

            if ($rowErrors) {
                $errorOutput[] = $rowErrors;
            }
        }
        return \MUtil_Ra::flatten($errorOutput);
    }

    /**
     * Returns a description of the translator errors for the row specified.
     *
     * @param mixed $row
     * @return array of String messages
     */
    public function getRowErrors($row)
    {
        $errorOutput = array();
        if (isset($this->_errors[$row])) {
            $start = sprintf($this->_('Row %s'), $row);
            foreach ((array) $this->_errors[$row] as $field1 => $errors) {
                if (is_numeric($field1)) {
                    $middle = '';
                } else {
                    $middle = sprintf($this->_(' field %s'), $field1);
                }
                $middle =  $middle . $this->_(': ');
                foreach ((array) $errors as $field2 => $error) {
                    $errorOutput[] = $start . $middle . $error;
                }
            }
        }
        return $errorOutput;
    }

    /**
     * Get the source model, where the data is coming from.
     *
     * @return \MUtil_Model_ModelAbstract $sourceModel The source of the data
     */
    public function getSourceModel()
    {
        return $this->_sourceModel;
    }

    /**
     * Get a form for filtering and validation, populating it
     * with elements.
     *
     * @return \Zend_Form
     */
    public function getTargetForm()
    {
        if (! $this->targetForm instanceof \Zend_Form) {
            $this->setTargetForm($this->_makeTargetForm());
        }

        return $this->targetForm;
    }

    /**
     * Get the target model, where the data is going to.
     *
     * @return \MUtil_Model_ModelAbstract $sourceModel The target of the data
     */
    public function getTargetModel()
    {
        return $this->_targetModel;
    }

    /**
     * True when the transformation generated errors.
     *
     * @return boolean True when there are errora
     */
    public function hasErrors()
    {
        return (boolean) $this->_errors;
    }

    /**
     * Set the description.
     *
     * @param string $description A description that enables users to choose the transformer they need.
     * @return \Gems_Model_ModelTranslatorAbstract (continuation pattern)
     */
    public function setDescription($description)
    {
        $this->_description = $description;
        return $this;
    }

    /**
     * Set the source model, where the data is coming from.
     *
     * @param \MUtil_Model_ModelAbstract $sourceModel The source of the data
     * @return \MUtil_Model_ModelTranslatorAbstract (continuation pattern)
     */
    public function setSourceModel(\MUtil_Model_ModelAbstract $sourceModel)
    {
        $this->_sourceModel = $sourceModel;
        return $this;
    }

    /**
     * Set a form populated with elements for filtering and validation of
     * the input elements
     *
     * @param \Zend_Form $form
     * @return \MUtil_Model_ModelTranslatorAbstract (continuation pattern)
     */
    public function setTargetForm(\Zend_Form $form)
    {
        $this->targetForm = $form;

        $this->_dateElementNames = array();
        $this->_checkForm($form->getElements());

        return $this;
    }

    /**
     * Set the target model, where the data is going to.
     *
     * @param \MUtil_Model_ModelAbstract $sourceModel The target of the data
     * @return \MUtil_Model_ModelTranslatorAbstract (continuation pattern)
     */
    public function setTargetModel(\MUtil_Model_ModelAbstract $targetModel)
    {
        $this->_targetModel    = $targetModel;

        return $this;
    }

    /**
     * Set the translator to use.
     *
     * @param \Zend_Translate $translate
     * @return \MUtil_Model_ModelTranslatorAbstract (continuation pattern)
     */
    public function setTranslator(\Zend_Translate $translate)
    {
        $this->translate = $translate;
        $this->initTranslateable();

        return $this;
    }

    /**
     * Prepare for the import.
     *
     * @return \MUtil_Model_ModelTranslatorAbstract (continuation pattern)
     */
    public function startImport()
    {
        if (! $this->_targetModel instanceof \MUtil_Model_ModelAbstract) {
            throw new \MUtil_Model_ModelException("Trying to start the import without target model.");
        }

        // Clear errors
        $this->_errors = array();

        $this->_fieldMap       = $this->getFieldsTranslations();
        $this->_fieldKeys      = array_keys($this->_fieldMap);
        $this->_mapRequired    = $this->_fieldKeys !== array_values($this->_fieldMap);

        // Make sure the target form is set (unless overruled by child class)
        $this->getTargetForm();

        return $this;
    }

    /**
     * Perform all the translations in the data set.
     *
     * This code does not validate the individual inputs, but does check the ovrall structure of the input
     *
     * @param \Traversable|array $data a nested data set as loaded from the source model
     * @return mixed Nested row array or false when errors occurred
     */
    public function translateImport($data)
    {
        $this->startImport();

        $results = array();

        foreach ($data as $key => $row) {

            $row = $this->translateRowValues($row, $key);

            if ($row) {
                $row = $this->validateRowValues($row, $key);
            }

            if ($row) {
                $results[$key] = $row;
            }
        }
        return $results;
    }

    /**
     * Perform any translations necessary for the code to work
     *
     * @param mixed $row array or \Traversable row
     * @param scalar $key
     * @return mixed Row array or false when errors occurred
     */
    public function translateRowValues($row, $key)
    {
        $row = $this->_prepareRow($row, $key);

        if ($row) {
            array_walk($row, array($this, 'filterBasic'));
        }

        return $row;
    }

    /**
     * Validate the data against the target form
     *
     * @param array $row
     * @param scalar $key
     * @return mixed Row array or false when errors occurred
     */
    public function validateRowValues(array $row, $key)
    {
        if (! $this->targetForm instanceof \Zend_Form) {
            return $row;
        }

        // Clean up lingering values
        $this->targetForm->clearErrorMessages()
                ->populate(array());

        if (! $this->targetForm->isValid($row)) {
            $messages = $this->targetForm->getMessages(null, true);

            $this->_addErrors($messages, $key, $row);
        }
        // \MUtil_Echo::track($row);

        // Notice: this changes all dates back to string
        $row = array_intersect_key($this->targetForm->getValues(), $row) + $row;

        // Restore the dates as date objects
        foreach ($this->_dateElementNames as $name) {
            $element = $this->targetForm->getElement($name);
            if ($element instanceof \MUtil_JQuery_Form_Element_DatePicker) {
                $row[$name] = $element->getDateValue();
            }
        }
        // \MUtil_Echo::track($row);

        return $row;
    }
}