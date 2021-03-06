<?php

/**
 *
 * @package    MUtil
 * @subpackage Model_Bridge
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

/**
 *
 * @package    MUtil
 * @subpackage Model_Bridge
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class MUtil_Model_Bridge_TableBridge extends \MUtil_Model_Bridge_TableBridgeAbstract
{
    public $paginateClass    = 'centerAlign';
    public $sortAsc          = true;
    public $sortAscClass     = 'sortAsc';
    public $sortAscClassSel  = 'sortAscSelected';
    public $sortAscParam;
    public $sortDescClassSel = 'sortDescSelected';
    public $sortDescParam;
    public $sortKey;
    public $useRowHref       = true;

    protected $baseUrl = array();
    protected $has_multi_refs = false;
    protected $row_href;

    public function add($name, $label = null, $tdClass = null, $thClass = null)
    {
        $td = $this->_getLazyName($name);
        $th = $this->_checkLabel($label, $name);

        if ($tdClass || ($tdClass = $this->model->get($name, 'tdClass'))) {
            $td = array($td, 'class' => $tdClass);
        }
        if ($thClass || ($thClass = $this->model->get($name, 'thClass'))) {
            $th = array($th, 'class' => $thClass);
        }

        return $this->table->addColumn($td, $th);
    }

    /**
     * Add columns to the table
     *
     * @param array|null $addItems list of column names to add to the table. if null, all labeled items will be added
     * @param bool $showLabels show labels in the table header
     */
    public function addColumns($addItems=null, $showLabels=true)
    {
        if ($addItems === null) {
            $addItems = $this->model->getColNames('label');
        }

        foreach($addItems as $item) {
            $td = $this->_getLazyName($name);
            $th = $this->_checkLabel($label, $name);

            if ($tdClass || ($tdClass = $this->model->get($name, 'tdClass'))) {
                $td = array($td, 'class' => $tdClass);
            }
            if ($thClass || ($thClass = $this->model->get($name, 'thClass'))) {
                $th = array($th, 'class' => $thClass);
            }
            if ($showLabels) {
                $this->table->addColumn($td, $th);
            } else {
                $this->table->addColumn($td, null);
            }
        }
    }

    /**
     *
     * @param \MUtil_Html_HtmlElement $link Or anything else to put a the column
     * @return \MUtil_MultiWrapper containing the column, header and footer cell
     */
    public function addItemLink($link)
    {
        $tds = $this->table->addColumnArray($link);
        $tbody = $tds[0];
        $tbody->class = 'table-button';

        if ($this->useRowHref) {
            if ($this->row_href) {
                if ($link instanceof \MUtil_Html_HtmlElement) {
                    $tds[0]->onclick = array('location.href=\'', $link->href, '\';');
                } else {
                    $tds[0]->onclick = '// Dummy on click';
                }
                $this->has_multi_refs = true;
            } else {
                if ($link instanceof \MUtil_Html_HtmlElement) {
                    $this->row_href = $link->href;
                }
            }
        }

        return new \MUtil_MultiWrapper($tds);
    }

    public function addMultiSort($arg_array)
    {
        $args = func_get_args();

        $headers = null;
        $content = null;

        foreach ($args as $name) {
            if (is_string($name)) {
                // $headers[] = $this->model->get($name, 'label');
                $headers[] = $this->createSortLink($name);
                $content[] = $this->_getLazyName($name);

            } elseif (is_array($name)) {
                if ($c = array_shift($name)) {
                    $content[] = $c;
                }
                if ($h = array_shift($name)) {
                    $headers[] = $h;
                }
                if ($cc = array_shift($name)) { // Content class
                    $content[] = $cc;
                }
                if ($hc = array_shift($name)) {
                    $headers[] = $hc;
                } elseif ($cc) {
                    $headers[] = $cc;
                }

            } else {
                $headers[] = $name;
                $content[] = $name;
            }
        }

        return $this->table->addColumn($content, $headers);
    }

    public function addSortable($name, $label = null, $tdOptions = null, $thOptions = null)
    {
        $td = $this->_getLazyName($name);
        $th = $this->createSortLink($name, $label);

        // Make sure this is the right kind of array
        if ($tdOptions && (! is_array($tdOptions))) {
            $tdOptions = array('class' => $tdOptions);
        }
        // Copy default input to $thOptions
        if (null === $thOptions) {
            $thOptions = $tdOptions;
        }
        // Get class from model
        if ($tdClass = $this->model->get($name, 'tdClass')) {
            if (isset($tdOptions['class'])) {
                if (is_string($tdOptions['class']) && ($tdOptions['class'] !== $tdClass)) {
                    $tdOptions['class'] = $tdOptions['class'] . ' ' . $tdClass;
                }
            } else {
                $tdOptions['class'] = $tdClass;
            }
        }
        // Use options
        if ($tdOptions) {
            $td = array($td, $tdOptions);
        }
        // Make sure this is the right kind of array
        if ($thOptions && (! is_array($thOptions))) {
            $thOptions = array('class' => $thOptions);
        }
        // Get class from model
        if ($thClass = $this->model->get($name, 'thClass')) {
            if (isset($thOptions['class'])) {
                if (is_string($thOptions['class'])) {
                    $thOptions['class'] = $thOptions['class'] + ' ' + $thClass;
                }
            } else {
                $thOptions['class'] = $thClass;
            }
        }
        // Use options
        if ($thOptions) {
            $th = array($th, $thOptions);
        }

        return $this->table->addColumn($td, $th);
    }

    /**
     * Create a sort link for the given $name element using the $label if provided or the label from the model
     * when null
     *
     * @param string $name
     * @param string $label
     * @return \MUtil_Html_AElement
     */
    public function createSortLink($name, $label = null)
    {
        $label = $this->_checkLabel($label, $name);

        if ($this->model->get($name, 'noSort')) {
            return $label;
        }

        $class      = $this->sortAscClass;
        $sortParam  = $this->sortAscParam;
        $nsortParam = $this->sortDescParam;

        if ($this->sortKey == $name) {
            if ($this->sortAsc) {
                $class      = $this->sortAscClassSel;
                $sortParam  = $this->sortDescParam;
                $nsortParam = $this->sortAscParam;
            } else {
                $class      = $this->sortDescClassSel;
            }
        }

        $sortUrl[$sortParam]  = $name;
        $sortUrl[$nsortParam] = null;  // Fix: no need for RouteReset if the link sets the other sort param to null
        // $sortUrl['RouteReset'] = false; // Prevents tabs from being communicated
        $sortUrl = $sortUrl + $this->baseUrl;

        return \MUtil_Html::create()->a($sortUrl, array('class' => $class, 'title' => $this->model->get($name, 'description')), $label);
    }

    /**
     *
     * @param array $data
     * @return \MUtil_Html_TableElement
     */
    public function displayListTable($data)
    {
        $table = $this->displaySubTable($data);
        $table->setPivot(true, 0);

        return $table;
    }

    /**
     *
     * @param array $data
     * @return \MUtil_Html_TableElement
     */
    public function displaySubTable($data)
    {
        $table = $this->getTable();
        $table->setRepeater($data);

        return $table;
    }

    /**
     * Returns the baseUrl for any automatic links
     *
     * @return array
     */
    public function getBaseUrl()
    {
        return (array) $this->baseUrl;
    }

    /**
     * Get the actual table
     *
     * @return \MUtil_Html_TableElement
     */
    public function getTable()
    {
        if ($this->useRowHref && $this->row_href) {
            $onclick = array('location.href=\'', $this->row_href, '\';');

            if ($this->has_multi_refs) {
                foreach ($this->table[\MUtil_Html_TableElement::TBODY] as $row) {
                    if ($row instanceof \MUtil_Html_TrElement) {
                        $row->onclick = "{// Dummy for CSS\n}";
                    }
                    foreach ($row as $cell) {
                        if (! isset($cell->onclick)) {
                            $cell->onclick = $onclick;
                        }
                    }
                }
            } else {
                foreach ($this->table[\MUtil_Html_TableElement::TBODY] as $row) {
                    if ($row instanceof \MUtil_Html_TrElement) {
                        $row->onclick = $onclick;
                    }
                }
            }
        }

        return $this->table;
    }

    /**
     * Add an item based of a lazy if
     *
     * @param mixed $if
     * @param mixed $item
     * @param mixed $else
     * @return array
     */
    public function itemIf($if, $item, $else = null)
    {
        if (is_string($if)) {
            $if = $this->$if;
        }

        return array(\MUtil_Lazy::iff($if, $item, $else), $item);
    }

    public function setBaseUrl(array $url)
    {
        if (isset($url[$this->sortAscParam])) {
            unset($url[$this->sortAscParam]);
        }
        if (isset($url[$this->sortDescParam])) {
            unset($url[$this->sortDescParam]);
        }

        $this->baseUrl = $url;
        return $this;
    }

    /**
     * Set the model to be used by the bridge.
     *
     * This method exist to allow overruling in implementation classes
     *
     * @param \MUtil_Model_ModelAbstract $model
     * @return \MUtil_Model_Bridge_TableBridge
     */
    public function setModel(\MUtil_Model_ModelAbstract $model)
    {
        $this->sortAscParam  = $model->getSortParamAsc();
        $this->sortDescParam = $model->getSortParamDesc();

        if ($sort = $model->getSort()) {
            $this->setSort($sort);
        }

        return parent::setModel($model);
    }

    public function setPage(\Zend_Paginator $paginator, \Zend_Controller_Request_Abstract $request, \Zend_Translate $t, $scrollingStyle = 'Sliding')
    {
        $this->table->tfrow()->pagePanel($paginator, $request, $t, array('baseurl' => $this->baseUrl, 'scrollingStyle' => $scrollingStyle));
    }

    /**
     * Set the sortorder
     *
     * @param string|array              $sort
     * @return \MUtil_Model_Bridge_TableBridge
     */
    public function setSort($sort)
    {
        if (is_array($sort)) {
            $this->sortAsc = reset($sort) !== SORT_DESC;
            $this->sortKey = key($sort);
        } else {
            $this->sortAsc = true;
            $this->sortKey = $sort;
        }
        return $this;
    }
}
