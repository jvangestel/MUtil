<?php

/**
 *
 * @package    MUtil
 * @subpackage Translate
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 */

use MUtil\Translate\TranslateableTrait;

/**
 * Add auto translate functions to a class
 *
 * Can be implemented as Traight in PHP 5.4 or copied into source
 *
 * @package    MUtil
 * @subpackage Translate
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.1.35
 */
class MUtil_Translate_TranslateableAbstract extends \MUtil_Registry_TargetAbstract
{
    use TranslateableTrait;

    /**
     * Called after the check that all required registry values
     * have been set correctly has run.
     *
     * This function is no needed if the classes are setup correctly
     *
     * @return void
     */
    public function afterRegistry()
    {
        parent::afterRegistry();

        $this->initTranslateable();
    }
}
