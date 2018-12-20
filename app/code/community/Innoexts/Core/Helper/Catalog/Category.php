<?php
/**
 * Innoexts
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@innoexts.com so we can send you a copy immediately.
 * 
 * @category    Innoexts
 * @package     Innoexts_Core
 * @copyright   Copyright (c) 2014 Innoexts (http://www.innoexts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product category helper
 * 
 * @category   Innoexts
 * @package    Innoexts_Core
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_Core_Helper_Catalog_Category 
    extends Innoexts_Core_Helper_Abstract 
{
    /**
     * Categories
     *
     * @var array of Mage_Catalog_Model_Category
     */
    protected $_categories;
    /**
     * Active categories
     *
     * @var array of Mage_Catalog_Model_Category
     */
    protected $_activeCategories;
    /**
     * Get category collection
     * 
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    public function getCategoryCollection()
    {
        return Mage::getSingleton('catalog/category')->getCollection();
    }
    /**
     * Get categories
     * 
     * @return array of Mage_Catalog_Model_Category
     */
    public function getCategories()
    {
        if (is_null($this->_categories)) {
            $categories = array();
            $collection = $this->getCategoryCollection()
                ->addNameToResult();
            foreach ($collection as $category) {
                $categories[(int) $category->getId()] = $category;
            }
            $this->_categories = $categories;
        }
        return $this->_categories;
    }
    /**
     * Get category by id
     * 
     * @param int $categoryId
     * 
     * @return Mage_Catalog_Model_Category
     */
    public function getCategoryById($categoryId)
    {
        $categories = $this->getCategories();
        if (isset($categories[$categoryId])) {
            return $categories[$categoryId];
        } else {
            return null;
        }
    }
    /**
     * Get category by name
     * 
     * @param string $name
     * 
     * @return Mage_Catalog_Model_Category
     */
    public function getCategoryByName($name)
    {
        $category = null;
        foreach ($this->getCategories() as $_category) {
            if ($_category->getName() == $name) {
                $category = $_category;
                break;
            }
        }
        return $category;
    }
    /**
     * Get active categories
     * 
     * @return array of Mage_Catalog_Model_Category
     */
    public function getActiveCategories()
    {
        if (is_null($this->_activeCategories)) {
            $categories = array();
            $collection = $this->getCategoryCollection()
                ->addNameToResult()
                ->addAttributeToFilter('is_active', 1);
            foreach ($collection as $category) {
                $categories[(int) $category->getId()] = $category;
            }
            $this->_activeCategories = $categories;
        }
        return $this->_activeCategories;
    }
    /**
     * Get active category ids
     * 
     * @return array
     */
    public function getActiveCategoryIds()
    {
        return array_keys($this->getActiveCategories());
    }
}