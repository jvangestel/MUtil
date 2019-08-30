<?php

/**
 *
 * @package    MUtil
 * @subpackage Registry
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2019, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 */

namespace MUtil\Registry;

/**
 *
 * @package    MUtil
 * @subpackage Registry
 * @copyright  Copyright (c) 2019, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 * @since      Class available since version 1.8.2 30-Aug-2019 15:54:01
 */
trait TargetTrait
{
    /**
     * Called after the check that all required registry values
     * have been set correctly has run.
     *
     * @return void
     */
    public function afterRegistry()
    {
        // parent::afterRegistry();
    }

    /**
     * Allows the loader to set resources.
     *
     * @param string $name Name of resource to set
     * @param mixed $resource The resource.
     * @return boolean True if $resource was OK
     */
    public function answerRegistryRequest($name, $resource)
    {
        $this->$name = $resource;

        return true;
    }

    /**
     * Should be called after answering the request to allow the Target
     * to check if all required registry values have been set correctly.
     *
     * @return boolean False if required values are missing.
     */
    public function checkRegistryRequestsAnswers()
    {
        return true;
    }

    /**
     * Filters the names that should not be requested.
     *
     * Can be overriden.
     *
     * @param string $name
     * @return boolean
     */
    protected function filterRequestNames($name)
    {
        return '_' !== $name[0];
    }

    /**
     * Allows the loader to know the resources to set.
     *
     * Returns those object variables defined by the subclass but not at the level of this definition.
     *
     * Can be overruled.
     *
     * @return array of string names
     */
    public function getRegistryRequests()
    {
        return array_filter(array_keys(get_object_vars($this)), array($this, 'filterRequestNames'));
    }
}
