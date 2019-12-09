<?php

use MUtil\Javascript;

class MUtil_View_Helper_InlineScript extends \Zend_View_Helper_InlineScript
{
    /**
     * Optional allowed attributes for script tag
     * @var array
     */
    protected $_optionalAttributes = array(
        'charset', 'defer', 'language', 'nonce', 'src'
    );

    /**
     * Create data item containing all necessary components of script
     *
     * @param  string $type
     * @param  array $attributes
     * @param  string $content
     * @return stdClass
     */
    public function createData($type, array $attributes, $content = null)
    {
        $data = parent::createData($type, $attributes, $content);

        if (!array_key_exists('nonce', $attributes) && Javascript::$scriptNonce) {
            $data->nonce = Javascript::$scriptNonce;
            $data->attributes['nonce'] = Javascript::$scriptNonce;
        }

        return $data;
    }
}