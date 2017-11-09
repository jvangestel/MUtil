<?php
/**
 * @package    MUtil
 * @subpackage Model\Iterator
 * @author     Menno Dekker <menno.dekker@erasmusmc.nl>
 * @copyright  Copyright (c) 2017 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Model\Iterator;

/**
 * Tellable interface
 *
 * Object is iterable and able to tell it's current position so it can be
 * used to seek. This is needed for the ArrayIterator that is does not retain
 * it's position after serialization.
 *
 * @package    MUtil
 * @subpackage Model\Iterator
 * @author     Menno Dekker <menno.dekker@erasmusmc.nl>
 * @copyright  Copyright (c) 2017 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
interface TellableInterface
{
    /**
     * Tell the current position in the iterator, so it can be set using seek
     */
    public function tell();
}
