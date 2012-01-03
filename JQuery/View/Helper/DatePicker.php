<?php


/**
 * Copyright (c) 2011, Erasmus MC
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
 */

/**
 * 
 * @author Matijs de Jong
 * @since 1.0
 * @version 1.1
 * @package MUtil
 * @subpackage View
 */

/**
 * 
 * @author Matijs de Jong
 * @package MUtil
 * @subpackage View
 */
class MUtil_JQuery_View_Helper_DatePicker extends ZendX_JQuery_View_Helper_DatePicker
{
    public function datePicker($id, $value = null, array $params = array(), array $attribs = array()) {
        $result = parent::datePicker($id, $value, $params, $attribs);
        if (isset($attribs['disabled'])) {
            $js = "$('#" . $attribs['id'] . "').datepicker('disable');";
            $this->jquery->addOnLoad($js);
        }

        if ($format = $params['dateFormat']) {
            //*
            $js = array();
            $js[] = '{';
            $js[] = "  var datePick = $('#" . $id . "');";
            $js[] = '';
            $js[] = "  datePick.blur(function() {";
            $js[] = "    var dateused;";
            $js[] = "    var dateformat = '" . $format . "';";
            // TODO: Why won't this work
            // $js[] = "    var dateformat = datePick.datepicker('option', 'dateFormat');";
            // $js[] = "    alert(dateformat);";
            $js[] = "    dateused = datePick.attr('value');";
            $js[] = "    dateused = $.datepicker.parseDate(dateformat, dateused);";
            $js[] = "    datePick.attr('value', $.datepicker.formatDate(dateformat, dateused));";
            $js[] = "  });";
            $js[] = '}';

            $this->jquery->addOnLoad(implode("\n", $js));
            //$this->view->inlineScript()->appendScript(implode("\n", $js)); // */
        }
        return $result;
    }

}