<?php

/**
 * Copyright (c) 2013, Erasmus MC
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
 * DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> BE LIABLE FOR ANY
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
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @version    $Id: TabbedTextModel.php$
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
class MUtil_Model_TabbedTextModel extends MUtil_Model_ArrayModelAbstract
{
    /**
     * The content file encoding
     *
     * @var string
     */
    protected $_encoding;

    /**
     * The field map for raw input to text
     *
     * @var array
     */
    protected $_fieldMap;

    /**
     * The name of the content file
     *
     * @var string
     */
    protected $_fileName;

    /**
     * The regular expression
     * @var string
     */
    protected $split = "\t";

    /**
     *
     * @param string $fileName
     * @param string $encoding
     */
    public function __construct($fileName, $encoding = null)
    {
        parent::__construct($fileName);

        $this->_fileName = $fileName;

        if ($encoding && ($encoding !== mb_internal_encoding())) {
            $this->_encoding = $encoding;
        }
    }

    /**
     * An ArrayModel assumes that (usually) all data needs to be loaded before any load
     * action, this is done using the iterator returned by this function.
     *
     * @return Traversable Return an iterator over or an array of all the rows in this object
     */
    protected function _loadAllTraversable()
    {
        $iterator = new MUtil_Model_Iterator_TextFileIterator($this->_fileName, array($this, 'mapLine'));

        // First line contains field names
        $this->_fieldMap = $this->_processLine(MUtil_Encoding::removeBOM($iterator->getFirstLine()));

        // Store the positions in the model
        foreach ($this->_fieldMap as $pos => $name) {
            $this->set($name, 'read_position', $pos);
        }

        return $iterator;
    }

    /**
     * Splits the input line into parts
     *
     * @param string $line
     * @return array
     */
    private function _processLine($line)
    {
        if ($this->_encoding) {
            $line = mb_convert_encoding($line, mb_internal_encoding(), $this->_encoding);
        }
        // MUtil_Echo::track($line);
        return explode($this->split, $line);
    }

    /**
     * Maps the input line to the fields
     *
     * @param string $line
     * @return array
     */
    public function mapLine($line)
    {
        $fields = $this->_processLine($line);

        $fieldCount = count($fields);
        $mapCount   = count($this->_fieldMap);
        if (count($fields) === count($this->_fieldMap)) {
            return array_combine($this->_fieldMap, $fields);
        } elseif ($fieldCount > $mapCount) {
            return array_combine($this->_fieldMap, array_slice($fields, 0, $mapCount));
        } elseif ($fieldCount) {
            return array_combine($this->_fieldMap, $fields + array_fill($fieldCount, $mapCount - $fieldCount, null));
        }

        return false;
    }
}
