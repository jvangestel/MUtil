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
 * @subpackage Batch
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @version    $Id: SessionStack.php 2394 2015-01-15 14:30:10Z matijsdejong $
 */

/**
 * A default command stack that uses the session ot store the commands to
 * execute.
 *
 * @package    MUtil
 * @subpackage Batch
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since MUtil version 1.3
 */
class MUtil_Batch_Stack_SessionStack extends \MUtil_Batch_Stack_StackAbstract
{
    /**
     *
     * @var \Zend_Session_Namespace
     */
    private $_session;

    /**
     *
     * @param string $id A unique name identifying the batch
     */
    public function __construct($id)
    {
        $this->_session = new \Zend_Session_Namespace(get_class($this) . '_' . $id);

        if (! isset($this->_session->commands)) {
            $this->_session->commands = array();
        }
    }

    /**
     * Add/set the command to the stack
     *
     * @param array $command
     * @param string $id Optional id to repeat double execution
     * @return boolean When true, increment the number of commands, otherwise the command existed
     */
    protected function _addCommand(array $command, $id = null)
    {
        $result = (null === $id) || !isset($this->_session->commands[$id]);

        if (null === $id) {
            $this->_session->commands[] = $command;
        } else {
            $this->_session->commands[$id] = $command;
        }

        return $result;
    }

    /**
     * Return the next command
     *
     * @return array 0 => command, 1 => params
     */
    public function getNext()
    {
        return reset($this->_session->commands);
    }

    /**
     * Run the next command
     *
     * @return void
     */
    public function gotoNext()
    {
        array_shift($this->_session->commands);
    }

    /**
     * Return true when there still exist unexecuted commands
     *
     * @return boolean
     */
    public function hasNext()
    {
        return (boolean) $this->_session->commands;
    }

    /**
     * Reset the stack
     *
     * @return \MUtil_Batch_Stack_Stackinterface (continuation pattern)
     */
    public function reset()
    {
        $this->_session->commands = array();

        return $this;
    }
}
