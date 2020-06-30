<?php

/**
 *
 * @package    MUtil
 * @subpackage Snippets\Standard
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Snippets\Standard;

use MUtil\Snippets\ModelConfirmDataChangeSnippetAbstract;

/**
 *
 *
 * @package    MUtil
 * @subpackage Snippets\Standard
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 * @since      Class available since MUtil version 1.7.2 30-sep-2015 19:15:02
 */
class ModelConfirmDataChangeSnippet extends ModelConfirmDataChangeSnippetAbstract
{
    /**
     *
     * @var \MUtil_Model_ModelAbstract
     */
    protected $model;

    /**
     * Creates the model
     *
     * @return \MUtil_Model_ModelAbstract
     */
    protected function createModel()
    {
        return $this->model;
    }
}
