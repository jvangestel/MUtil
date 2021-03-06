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
 * @subpackage Task
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @version    $Id: IteratorTaskAbstract.php 2483 2015-04-08 14:51:22Z matijsdejong $
 */

/**
 *
 *
 * @package    MUtil
 * @subpackage Task
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since MUtil version 1.3
 */
abstract class MUtil_Task_IteratorTaskAbstract extends \MUtil_Task_TaskAbstract
{
    /**
     *
     * @var \Iterator
     */
    protected $iterator;

    /**
     * Should be called after answering the request to allow the Target
     * to check if all required registry values have been set correctly.
     *
     * @return boolean False if required values are missing.
     */
    public function checkRegistryRequestsAnswers()
    {
        return ($this->iterator instanceof \Iterator) &&
            parent::checkRegistryRequestsAnswers();
    }

    /**
     * Should handle execution of the task, taking as much (optional) parameters as needed
     *
     * The parameters should be optional and failing to provide them should be handled by
     * the task
     */
    public function execute()
    {
        if ($this->getBatch()->getCounter('iterstarted') === 0) {
            $this->getBatch()->addToCounter('iterstarted');
            if ($this->iterator instanceof \Countable) {
                // Add the count - 1 as this task already added 1 for this run
                $this->getBatch()->addStepCount(count($this->iterator) -1);
            }
        }
        $this->executeIteration($this->iterator->key(), $this->iterator->current(), func_get_args());
    }

    /**
     * Execute a single iteration of the task.
     *
     * @param scalar $key The current iterator key
     * @param mixed $current The current iterator content
     * @param array $params The parameters to the execute function
     */
    abstract public function executeIteration($key, $current, array $params);

    /**
     * Return true when the task has finished.
     *
     * @return boolean
     */
    public function isFinished()
    {
        $this->iterator->next();
        $result = ! $this->iterator->valid();
        
        if ($result === true) {
            $this->getBatch()->resetCounter('iterstarted');
        } else {
            if (! ($this->iterator instanceof \Countable)) {
                // Add 1 to the counter to keep existing behaviour
                $this->getBatch()->addStepCount(1);
            }
        }
        return $result;
    }
}
