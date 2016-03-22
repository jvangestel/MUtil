<?php

/**
 * Copyright (c) 2016, Erasmus MC
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
 * @subpackage Validate
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2016 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Validate;

/**
 * Validates the a value is not the same as some other field value,
 * except when it is one of the exception values
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2016 Erasmus MC
 * @license    New BSD License
 * @since      Class available since MUtil version 1.7.2 Mar 22, 2016 3:16:44 PM
 */
class NotEqualExcept extends \MUtil_Validate_NotEqualTo
{
    /**
     * The exceptions where equality does not matter
     *
     * @var array
     */
    protected $exceptions;

    /**
     * Sets validator options
     *
     * @param array|string $fields On or more values that this element should not have
     * @param array|string $exceptions On or more values that this element can have
     * @param string|array Optional different message or an array of messages containing field names, an int array value is set as a general message
     */
    public function __construct($fields, $exceptions, $message = null)
    {
        parent::__construct($fields, $message);

        $this->exceptions = (array) $exceptions;
    }

    /**
     * Defined by \Zend_Validate_Interface
     *
     * Returns true if and only if a token has been set and the provided value
     * matches that token.
     *
     * @param  mixed $value
     * @return boolean
     */
    public function isValid($value, $context = array())
    {
        if ($value) {
            foreach ($this->exceptions as $exception) {
                if ($value == $exception) {
                    return true;
                }
                if (isset($context[$exception]) && ($value == $context[$exception])) {
                    return true;
                }
            }
        }

        return parent::isValid($value, $context);
    }
}
