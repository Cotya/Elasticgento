<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * PHP Version 5.3
 *
 * @category  Hackathon
 * @package   Hackathon_ElasticgentoCore
 * @author    Daniel Niedergesäß <daniel.niedergesaess ÄT gmail.com>
 * @author    Andreas Emer <emer ÄT mothership.de>
 * @author    Michael Ryvlin <ryvlin ÄT gmail.com>
 * @author    Johann Niklas <johann ÄT n1klas.de>
 * @copyright Copyright (c) 2014 Hackathon
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://mage-hackathon.de/
 *
 * Catalog Category layer Filter
 *
 */
class Hackathon_ElasticgentoCore_Model_Catalog_Layer_Filter_Category extends Mage_Catalog_Model_Layer_Filter_Category
{
    /**
     * add category facet filter to product collection.
     *
     * @param Mage_Catalog_Model_Category $category
     * @return Hackathon_ElasticgentoCore_Model_Catalog_Layer_Filter_Category
     */
    public function addCategoryFilter($category)
    {
        $this->getLayer()->getProductCollection()->addCategoryFilter($category);
        return $this;
    }

    /**
     * Adds facet condition to product collection.
     *
     * @return Hackathon_ElasticgentoCore_Model_Catalog_Layer_Filter_Category
     * @todo add filter for current category childen
     */
    public function addFacetToCollection()
    {
        /** @var $category Mage_Catalog_Model_Category */
        #$category = $this->getCategory();
        #$childrenCategories = $category->getChildrenCategories();
        /** @todo refactor */
        #$useFlat = (bool)Mage::getStoreConfig('catalog/frontend/flat_catalog_category');
        #$categories = ($useFlat) ? array_keys($childrenCategories) : array_keys($childrenCategories->toArray());
        $facet = new \Elastica\Facet\Terms('categories');
        $facet->setField('categories');
        $facet->setSize(10);
        $this->getLayer()->getProductCollection()->addFacet($facet);
        return $this;
    }

    /**
     * Retrieves request parameter and applies it to product collection.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param Mage_Core_Block_Abstract $filterBlock
     * @return Hackathon_ElasticgentoCore_Model_Catalog_Layer_Filter_Category
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $filter = (int)$request->getParam($this->getRequestVar());
        if ($filter) {
            $this->_categoryId = $filter;
        }

        /** @var $category Mage_Catalog_Model_Category */
        $category = $this->getCategory();
        if (!Mage::registry('current_category_filter')) {
            Mage::register('current_category_filter', $category);
        }

        if (!$filter) {
            $this->addCategoryFilter($category, null);
            return $this;
        }

        $this->_appliedCategory = Mage::getModel('catalog/category')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($filter);

        if ($this->_isValidCategory($this->_appliedCategory)) {
            $this->getLayer()->getProductCollection()
                ->addCategoryFilter($this->_appliedCategory);
            $this->addCategoryFilter($this->_appliedCategory);
            $this->getLayer()->getState()->addFilter(
                $this->_createItem($this->_appliedCategory->getName(), $filter)
            );
        }

        return $this;
    }

    /**
     * Retrieves current items data.
     *
     * @return array
     */
    protected function _getItemsData()
    {
        $layer = $this->getLayer();
        $key = $layer->getStateKey() . '_SUBCATEGORIES';
        $data = $layer->getCacheData($key);

        if ($data === null) {
            $categories = $this->getCategory()->getChildrenCategories();

            /** @var $productCollection Hackathon_ElasticgentoCore_Model_Resource_Catalog_Product_Collection */
            $productCollection = $layer->getProductCollection();
            $facets = $productCollection->getFacetData('categories');
            $data = array();
            foreach ($categories as $category) {
                /** @var $category Mage_Catalog_Model_Category */
                $categoryId = $category->getId();
                if (isset($facets[$categoryId])) {
                    $category->setProductCount($facets[$categoryId]);
                } else {
                    $category->setProductCount(0);
                }
                if ($category->getIsActive() && $category->getProductCount()) {
                    $data[] = array(
                        'label' => Mage::helper('core')->escapeHtml($category->getName()),
                        'value' => $categoryId,
                        'count' => $category->getProductCount(),
                    );
                }
            }
            $tags = $layer->getStateTags();
            $layer->getAggregator()->saveCacheData($data, $key, $tags);
        }

        return $data;
    }
}