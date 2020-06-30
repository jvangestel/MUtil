<?php

/**
 *
 * @package    MUtil
 * @subpackage Markup
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

/**
 * Makes sure the URL of a link is not lost when rendering Markup input
 * into text.
 *
 * @package    MUtil
 * @subpackage Markup
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.1
 */
class MUtil_Markup_Renderer_Text_Email implements \Zend_Markup_Renderer_TokenConverterInterface
{
    /**
     * Convert the token
     *
     * @param \Zend_Markup_Token $token
     * @param string $text
     *
     * @return string
     */
    public function convert(\Zend_Markup_Token $token, $text)
    {
        return $text;
    }
}

