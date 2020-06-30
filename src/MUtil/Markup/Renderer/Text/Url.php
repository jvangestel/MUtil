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
class MUtil_Markup_Renderer_Text_Url implements \Zend_Markup_Renderer_TokenConverterInterface
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
        if ($token->hasAttribute('url')) {
            $uri = $token->getAttribute('url');
        } else {
            $uri = $text;
        }

        if (!preg_match('/^([a-z][a-z+\-.]*):/i', $uri)) {
            $uri = 'http://' . $uri;
        }

        if ($text == $uri) {
            return $text;
        }

        // check if the URL is valid
        if (! \Zend_Markup_Renderer_Html::isValidUri($uri)) {
            return $text;
        }

        // $attributes = \Zend_Markup_Renderer_Html::renderAttributes($token);

        // run the URI through htmlentities
        // $uri = htmlentities($uri, ENT_QUOTES, \Zend_Markup_Renderer_Html::getEncoding());

        return "$text ($uri)";
    }
}

