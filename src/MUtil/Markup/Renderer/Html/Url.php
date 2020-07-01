<?php
        
/**
 *
 * @package    JointCompassion
 * @subpackage Url.php
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2020, Maasstad Ziekenhuis and MagnaFacta B.V.
 * @license    No free license, do not copy
 */


/**
 *
 * @package    JointCompassion
 * @subpackage Url.php
 * @license    No free license, do not copy
 * @since      Class available since version 1.8.8
 */
class MUtil_Markup_Renderer_Html_Url extends \Zend_Markup_Renderer_Html_Url
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

        // check if the URL is valid
        if (!\MUtil_Markup::isValidUri($uri)) {
            return $text;
        }

        $attributes = \Zend_Markup_Renderer_Html::renderAttributes($token);

        // run the URI through htmlentities
        $uri = htmlentities($uri, ENT_QUOTES, \Zend_Markup_Renderer_Html::getEncoding());

        return "<a href=\"{$uri}\"{$attributes}>{$text}</a>";
    }
}