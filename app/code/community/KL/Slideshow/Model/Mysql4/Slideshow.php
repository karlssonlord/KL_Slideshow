<?php
/**
 * Slideshow
 *
 * LICENSE
 *
 * This source file is subject to the new MIT license that is bundled
 * with this package in the file LICENSE.
 *
 * @category   KL
 * @package    KL_Slideshow
 * @copyright  Copyright (c) 2013 Karlsson & Lord AB (http://karlssonlord.com)
 * @license    http://opensource.org/licenses/MIT MIT License
 */

/**
 * Slide
 *
 * @category   KL
 * @package    KL_Slideshow
 * @copyright  Copyright (c) 2013 Karlsson & Lord AB (http://karlssonlord.com)
 * @license    http://opensource.org/licenses/MIT MIT License
 */
class KL_Slideshow_Model_Mysql4_Slideshow
    extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('slideshow/slideshow', 'slideshow_id');
    }

    /**
     * Before save
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return KL_Slideshow_Model_Mysql4_Slide
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getId()) {
            $object->setCreationTime(Mage::getSingleton('core/date')->gmtDate());
        }

        $object->setUpdateTime(Mage::getSingleton('core/date')->gmtDate());

        return $this;
    }

    /**
     * After save
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return void
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $adapter   = $this->_getWriteAdapter();
        $condition = $adapter->quoteInto('slideshow_id = ?', $object->getId());
        $table     = $this->getTable('slideshow/slideshow_store');
        $stores    = (array) $object->getData('stores');

        $adapter->delete($table, $condition);

        foreach ($stores as $store) {
            $storeArray             = array();
            $storeArray['slideshow_id'] = $object->getId();
            $storeArray['store_id'] = $store;

            $adapter->insert($table, $storeArray);
        }

        // Categories
        $table      = $this->getTable('slideshow/slideshow_to_category');
        $categories = (array) $object->getData('categories');
        $adapter->delete($table, $condition);
        foreach ($categories as $category) {
            $storeArray                 = array();
            $storeArray['slideshow_id'] = $object->getId();
            $storeArray['category_id']  = $category;

            $adapter->insert($table, $storeArray);
        }


        return parent::_afterSave($object);
    }

    /**
     * After load
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        if ($object->getId()) {
            $select = $this->_getReadAdapter()->select()
                ->from($this->getTable('slideshow/slideshow_store'))
                ->where('slideshow_id = ?', $object->getId());
            $data   = $this->_getReadAdapter()->fetchAll($select);

            if ($data) {
                $storesArray = array();

                foreach ($data as $row) {
                    $storesArray[] = $row['store_id'];
                }

                $object->setData('store_id', $storesArray);
            }
        }

        // Load selected categories
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('slideshow/slideshow_to_category'))
            ->where('slideshow_id = ?', $object->getId());
        $data   = $this->_getReadAdapter()->fetchAll($select);
        if ($data) {
            $categories = array();
            foreach ($data as $row) {
                $categories[] = $row['category_id'];
            }
            $object->setData('category_id', $categories);
        }

        return parent::_afterLoad($object);
    }

    /**
     * Get load select
     *
     * @param $field
     * @param $value
     * @param $object
     *
     * @return
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $select->join(
                    array('as' => $this->getTable('slideshow/slideshow_store')),
                    $this->getMainTable().'.slideshow_id = as.slideshow_id')
                ->where(
                    'is_active=1 AND as.store_id in (0, ?) ',
                    $object->getStoreId()
                )
                ->order('store_id DESC')
                ->limit(1);
        }

        return $select;
    }

    /**
     * Lookup store ID
     *
     * @param int @id
     *
     * @return void
     */
    public function lookupStoreIds($id)
    {
        $result = $this->_getReadAdapter()->fetchCol(
            $this->_getReadAdapter()->select()->from($this->getTable('slideshow/slideshow_store'),
            'store_id'
        )->where("{$this->getIdFieldName()} = ?", $id));

        return $result;
    }
}
