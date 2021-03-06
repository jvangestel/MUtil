<?php

/**
 * Copyright (c) 201e, Erasmus MC
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
 * @subpackage Model
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 201e Erasmus MC
 * @license    New BSD License
 * @version    $Id: SubmodelTransformerAbstract.php 203 2012-01-01t 12:51:32Z matijs $
 */

/**
 *
 *
 * @package    MUtil
 * @subpackage Model
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since MUtil version 1.3
 */
abstract class MUtil_Model_SubmodelTransformerAbstract implements \MUtil_Model_ModelTransformerInterface
{
    /**
     * The number of rows changed at the last save
     *
     * @var int
     */
    protected $_changed = 0;

    /**
     *
     * @var array of join functions
     */
    protected $_joins = array();

    /**
     *
     * @var array of \MUtil_Model_ModelAbstract
     */
    protected $_subModels = array();

    /**
     * The number of item rows changed since the last save or delete
     *
     * @return int
     */
    public function getChanged()
    {
        return $this->_changed;
    }

    /**
     * Add an (extra) model to the join
     *
     * @param \MUtil_Model_ModelAbstract $subModel
     * @param array $joinFields
     * @return \MUtil_Model_Transform_NestedTransformer (continuation pattern)
     */
    public function addModel(\MUtil_Model_ModelAbstract $subModel, array $joinFields, $name = null)
    {
        // \MUtil_Model::$verbose = true;

        if (null === $name) {
            $name = $subModel->getName();
        }

        $this->_subModels[$name] = $subModel;
        $this->_joins[$name]     = $joinFields;

        return $this;
    }

    /**
     * If the transformer add's fields, these should be returned here.
     * Called in $model->AddTransformer(), so the transformer MUST
     * know which fields to add by then (optionally using the model
     * for that).
     *
     * @param \MUtil_Model_ModelAbstract $model The parent model
     * @return array Of filedname => set() values
     */
    public function getFieldInfo(\MUtil_Model_ModelAbstract $model)
    {
        $data = array();
        foreach ($this->_subModels as $sub) {
            foreach ($sub->getItemNames() as $name) {
                if (! $model->has($name)) {
                    $data[$name] = $sub->get($name);
                    $data[$name]['no_text_search'] = true;

                    // Remove unsuited data
                    unset($data[$name]['table'], $data[$name]['column_expression']);
                }
            }
        }
        return $data;
    }

    /**
     * This transform function checks the filter for
     * a) retreiving filters to be applied to the transforming data,
     * b) adding filters that are the result
     *
     * @param \MUtil_Model_ModelAbstract $model
     * @param array $filter
     * @return array The (optionally changed) filter
     */
    public function transformFilter(\MUtil_Model_ModelAbstract $model, array $filter)
    {
        // Make sure the join fields are in the result set
        foreach ($this->_joins as $joins) {
            foreach ($joins as $source => $target) {
                if (!is_integer($source)) {
                    $model->get($source);
                }
            }
        }

        foreach ($this->_subModels as $name => $sub) {
            $filter = $this->transformFilterSubModel($model, $sub, $filter, $this->_joins[$name]);
        }

        return $filter;
    }

    /**
     * Filter
     *
     * @param MUtil_Model_ModelAbstract $model
     * @param MUtil_Model_ModelAbstract $sub
     * @param array $filter
     * @param array $joins
     * @return array
     */
    public function transformFilterSubModel(
        \MUtil_Model_ModelAbstract $model, \MUtil_Model_ModelAbstract $sub, array $filter, array $joins)
    {
        return $filter;
    }

    /**
     * The transform function performs the actual transformation of the data and is called after
     * the loading of the data in the source model.
     *
     * @param \MUtil_Model_ModelAbstract $model The parent model
     * @param array $data Nested array
     * @param boolean $new True when loading a new item
     * @param boolean $isPostData With post data, unselected multiOptions values are not set so should be added
     * @return array Nested array containing (optionally) transformed data
     */
    public function transformLoad(\MUtil_Model_ModelAbstract $model, array $data, $new = false, $isPostData = false)
    {
        if (! $data) {
            return $data;
        }

        foreach ($this->_subModels as $name => $sub) {
            $this->transformLoadSubModel($model, $sub, $data, $this->_joins[$name], $name, $new, $isPostData);
        }
        // \MUtil_Echo::track($data);

        return $data;
    }

    /**
     * Function to allow overruling of transform for certain models
     *
     * @param \MUtil_Model_ModelAbstract $model Parent model
     * @param \MUtil_Model_ModelAbstract $sub Sub model
     * @param array $data The nested data rows
     * @param array $join The join array
     * @param string $name Name of sub model
     * @param boolean $new True when loading a new item
     * @param boolean $isPostData With post data, unselected multiOptions values are not set so should be added
     */
    abstract protected function transformLoadSubModel(
            \MUtil_Model_ModelAbstract $model, \MUtil_Model_ModelAbstract $sub, array &$data, array $join,
            $name, $new, $isPostData);

    /**
     * This transform function performs the actual save (if any) of the transformer data and is called after
     * the saving of the data in the source model.
     *
     * @param \MUtil_Model_ModelAbstract $model The parent model
     * @param array $row Array containing row
     * @return array Row array containing (optionally) transformed data
     */
    public function transformRowAfterSave(\MUtil_Model_ModelAbstract $model, array $row)
    {
        if (! $row) {
            return $row;
        }

        foreach ($this->_subModels as $name => $sub) {
            $this->transformSaveSubModel($model, $sub, $row, $this->_joins[$name], $name);
            $this->_changed = $this->_changed + $sub->getChanged();
        }
        // \MUtil_Echo::track($row);

        return $row;
    }

    /**
     * This transform function is called before the saving of the data in the source model and allows you to
     * change all data.
     *
     * @param \MUtil_Model_ModelAbstract $model The parent model
     * @param array $row Array containing row
     * @return array Row array containing (optionally) transformed data
     */
    public function transformRowBeforeSave(\MUtil_Model_ModelAbstract $model, array $row)
    {
        // No changes
        return $row;
    }

    /**
     * Function to allow overruling of transform for certain models
     *
     * @param \MUtil_Model_ModelAbstract $model
     * @param \MUtil_Model_ModelAbstract $sub
     * @param array $data
     * @param array $join
     * @param string $name
     */
    abstract protected function transformSaveSubModel
            (\MUtil_Model_ModelAbstract $model, \MUtil_Model_ModelAbstract $sub, array &$row, array $join, $name);

    /**
     * This transform function checks the sort to
     * a) remove sorts from the main model that are not possible
     * b) add sorts that are required needed
     *
     * @param \MUtil_Model_ModelAbstract $model
     * @param array $sort
     * @return array The (optionally changed) sort
     */
    public function transformSort(\MUtil_Model_ModelAbstract $model, array $sort)
    {
        foreach ($this->_subModels as $sub) {
            foreach ($sort as $key => $value) {
                if ($sub->has($key)) {
                    // Remove all sorts on columns from the submodel
                    unset($sort[$key]);
                }
            }
        }

        return $sort;
    }

    /**
     * When true, the on save functions are triggered before passing the data on
     *
     * @return boolean
     */
    public function triggerOnSaves()
    {
        return false;
    }
}
