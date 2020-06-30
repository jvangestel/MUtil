<?php
        
/**
 *
 * @package    MUtil
 * @subpackage Markup
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2020, Maasstad Ziekenhuis and MagnaFacta B.V.
 * @license    No free license, do not copy
 */


/**
 *
 * @package    MUtil
 * @subpackage Markup
 * @license    No free license, do not copy
 * @since      Class available since version 1.8.8
 */
class MUtil_Markup_Renderer_Html_Email implements \Zend_Markup_Renderer_TokenConverterInterface
{
    /**
     * @inheritDoc
     */
    public function convert(Zend_Markup_Token $token, $text)
    {
        return "<a href='mailto:$text'>$text</a>" ;
    }
}