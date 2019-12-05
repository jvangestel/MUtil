<?php

namespace MUtil;

class Javascript
{
    public static $scriptNonce;

    public static function generateNonce()
    {
        self::$scriptNonce = hash('sha256',
            \Zend_Crypt_Math::randBytes(512)
        );
    }
}