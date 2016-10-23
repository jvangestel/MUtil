<?php

/**
 *
 * @package    MUtil
 * @subpackage Registry
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

/**
 * Standard Source interface for \MUtil_Registry_TargetInterface objects.
 *
 * This allows sources of values, e.g. the \Zend_Registry, to be injected
 *  automatically in a Target Object by calling $this->applySource().
 *
 * @see \MUtil_Registry_TargetInterface
 *
 * @package    MUtil
 * @subpackage Registry
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.1
 */
interface MUtil_Registry_SourceInterface
{
    /**
     * Adds an extra source container to this object.
     *
     * @param mixed $container
     * @param string $name An optional name to identify the container
     * @return \MUtil_Registry_Source
     */
    public function addRegistryContainer($container, $name = null);

    /**
     * Apply this source to the target.
     *
     * @param \MUtil_Registry_TargetInterface $target
     * @return boolean True if $target is OK with loaded requests
     */
    public function applySource(\MUtil_Registry_TargetInterface $target);

    /**
     * Removes a source container from this object.
     *
     * @param string $name The name to identify the container
     * @return \MUtil_Registry_Source
     */
    public function removeRegistryContainer($name);
}
