<?php


namespace MUtil\Model\Transformer;


class ToManySingleColumnChildTransformer extends ToManyTransformer
{
    protected $singleColumn;

    public function __construct($singleColumn, $savable = false)
    {
        $this->singleColumn = $singleColumn;
        $this->savable = $savable;
    }

    /**
     * Function to allow overruling of transform for certain models
     *
     * @param \MUtil_Model_ModelAbstract $model Parent model
     * @param \MUtil_Model_ModelAbstract $sub Sub model
     * @param array $data The nested data rows
     * @param array $join The join array
     * @param string $name Name of sub model
     * @param boolean $new True when loading a new item
     * @param boolean $isPostData With post data, unselected multiOptions values are not set so should be added
     */
    protected function transformLoadSubModel(
        \MUtil_Model_ModelAbstract $model, \MUtil_Model_ModelAbstract $sub, array &$data, array $join,
        $name, $new, $isPostData)
    {
        $child = reset($join);
        $parent = key($join);

        $filter = [];
        $parentIds = array_column($data, $parent);

        foreach ($data as $key => $row) {

            $rows = null;
            // E.g. if loaded from a post
            if (isset($row[$name])) {
                $rows = $sub->processAfterLoad($row[$name], $new, $isPostData);
                unset($parentIds[$key]);
            } elseif ($new) {
                $rows = $sub->loadAllNew();
                unset($parentIds[$key]);
            }

            if ($rows !== null && isset($rows[$child])) {
                $data[$key][$name] = $rows[$child];
            }
        }

        $parentIndexes = array_flip($parentIds);
        $filter[$child] = $parentIds;

        $combinedResult = $sub->load($filter);

        foreach($combinedResult as $key => $result) {
            if (isset($result[$child]) && isset($parentIndexes[$result[$child]])) {
                $data[$parentIndexes[$result[$child]]][$name][] = $result[$this->singleColumn];
            }
        }
    }

    /**
     * Function to allow overruling of transform for certain models
     *
     * @param \MUtil_Model_ModelAbstract $model
     * @param \MUtil_Model_ModelAbstract $sub
     * @param array $data
     * @param array $join
     * @param string $name
     */
    protected function transformSaveSubModel(
        \MUtil_Model_ModelAbstract $model, \MUtil_Model_ModelAbstract $sub, array &$row, array $join, $name)
    {
        if (!$this->savable) {
            return;
        }

        if (! isset($row[$name])) {
            return;
        }

        $data = $row[$name];

        $child = reset($join);
        $parent = key($join);


        $parentId = $row[$parent];
        $filter = [$child => $parentId];
        $oldResults = $sub->load($filter);

        $newResults = [];
        $insertResults = [];
        $deletedResults = [];

        foreach($oldResults as $oldResult) {
            $index = array_search($oldResult[$this->singleColumn], $data);
            if ($index !== false) {
                $newResults[] = $oldResult;
                unset($data[$index]);
            } else {
                $deletedResults[] = $oldResult;
            }
        }

        foreach($data as $newValue) {
            $insertResults[] = [
                $child => $parentId,
                $this->singleColumn => $newValue,
            ];
        }

        if (!empty($insertResults)) {
            $insertedResults = $sub->saveAll($insertResults);
            $newResults = array_merge($newResults, $insertedResults);
        }

        $keys = $sub->getKeys();
        $key = reset($keys);

        $deleteIds = array_column($deletedResults, $key);
        if (!empty($deleteIds)) {
            $sub->delete([$key => $deleteIds]);
        }

        $row[$name] = $newResults;
    }
}
