<?php

/**
 *
 * @package    MUtil
 * @subpackage Model
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

/**
 * A model that takes any \Zend_Db_Select statement as a source
 *
 * @package    MUtil
 * @subpackage Model
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class MUtil_Model_SelectModel extends \MUtil_Model_DatabaseModelAbstract
{
    /**
     * Child classes may technically be able or not able to add extra rows,
     * but the data model or specific circumstances may require a specific
     * instance of that class to deviate from the default.
     *
     * @var boolean $canCreate True if the model can create new rows.
     */
    public $canCreate = false;

    /**
     *
     * @var \Zend_Db_Select
     */
    private $_select;

    /**
     *
     * @param \Zend_Db_Select $select
     * @param string $name Optiona name
     */
    public function __construct(\Zend_Db_Select $select, $name = null)
    {
        $this->_select = $select;

        // Make sure the columns are known to the model
        foreach ($select->getPart(\Zend_Db_Select::COLUMNS) as $column) {
            if (isset($column[2])) {
                $this->set($column[2]);
            } elseif (is_string($column[1])) {
                $this->set($column[1]);
            }
        }

        if (null === $name) {
            $name = 'rnd' . rand(10000, 999999);
        }

        parent::__construct($name);
    }

    /**
     * Save a single model item.
     *
     * @param array $newValues The values to store for a single model item.
     * @param array $filter If the filter contains old key values these are used
     * to decide on update versus insert.
     * @return array The values as they are after saving (they may change).
     */
    protected function _save(array $newValues, array $filter = null)
    {
        throw new \Exception('Cannot save ' . __CLASS__ . ' data.');
    }

    /**
     * Adds a column to the model
     *
     * @param string|\Zend_Db_Expr $column
     * @param string $columnName
     * @param string $orignalColumn
     * @return \MUtil_Model_DatabaseModelAbstract Provides a fluent interface
     */
    public function addColumn($column, $columnName = null, $orignalColumn = null)
    {
        if (null === $columnName) {
            $columnName = strtr((string) $column, ' .,;:?!\'"()<=>-*+\\/&%^', '______________________');
        }

        $this->_select->columns(array($columnName => $column));

        return parent::addColumn($column, $columnName, $orignalColumn);
    }

    /**
     * Delete items from the model
     *
     * @param mixed $filter True to use the stored filter, array to specify a different filter
     * @return int The number of items deleted
     */
    public function delete($filter = true)
    {
        throw new \Exception('Cannot delete ' . __CLASS__ . ' data.');
    }

    /**
     * The database adapter used by the model.
     *
     * @return \Zend_Db_Adapter_Abstract
     */
    public function getAdapter()
    {
        return $this->_select->getAdapter();
    }

    /**
     * The select object where we get the query from.
     *
     * @return \Zend_Db_Select
     */
    public function getSelect()
    {
        return clone $this->_select;
    }
}
