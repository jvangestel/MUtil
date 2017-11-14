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
 * ArrayIteratorTellable
 *
 * This is needed for the ArrayIterator that is does not retain
 * it's position after serialization.
 *
 * @package    MUtil
 * @subpackage Model\Iterator
 * @author     Menno Dekker <menno.dekker@erasmusmc.nl>
 * @copyright  Copyright (c) 2017 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class ArrayIteratorTellable extends \ArrayIterator implements TellableInterface
{

    protected $position = 0;

    public function next()
    {
        parent::next();
        if (!$this->valid()) {
            // Store last position as negative use abs to solve multiple next calls
            $this->position = 0 - abs($this->position);
        } else {
            $this->position++;
        }
    }

    public function rewind()
    {
        $this->position = 0;
        parent::rewind();
    }

    public function seek($position)
    {
        $this->position = $position;
        parent::seek($position);
    }

    public function tell()
    {
        return $this->position;
    }

    public function unserialize($serialized)
    {
        $result = parent::unserialize($serialized);
        if ($this->position < 0) {
            // We are at the end, got to last
            $this->seek(abs($this->position));
            // And move to end
            $this->next();
        } else {
            $this->seek($this->position);
        }

        return $result;
    }

}