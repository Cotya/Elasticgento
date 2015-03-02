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
 * Catalog layered navigation view block
 *
 */
class Hackathon_ElasticgentoCatalog_Block_Catalog_Layer_View extends Mage_Catalog_Block_Layer_View
{
    /**
     * Boolean block name.
     *
     * @var string
     */
    protected $_booleanFilterBlockName;

    /**
     * Registers current layer in registry.
     *
     * @see Mage_Catalog_Block_Product_List::getLayer()
     */
    protected function _construct()
    {
        parent::_construct();
        Mage::register('current_layer', $this->getLayer());
    }

    /**
     * Modifies default block names to specific ones if engine is active.
     * @todo add blocks
     */
    protected function _initBlocks()
    {
        parent::_initBlocks();
        $this->_categoryBlockName = 'elasticgento_catalog/catalog_layer_filter_category';
        $this->_attributeFilterBlockName = 'elasticgento_catalog/catalog_layer_filter_attribute';
        $this->_priceFilterBlockName = 'elasticgento_catalog/catalog_layer_filter_price';
        $this->_decimalFilterBlockName = 'elasticgento_catalog/catalog_layer_filter_decimal';
        $this->_booleanFilterBlockName = 'elasticgento_catalog/catalog_layer_filter_boolean';
    }

    /**
     * Prepare child blocks
     *
     * @return Hackathon_ElasticgentoCore_Block_Catalog_Layer_View
     */
    protected function _prepareLayout()
    {
        $stateBlock = $this->getLayout()->createBlock($this->_stateBlockName)->setLayer($this->getLayer());
        $categoryBlock = $this->getLayout()->createBlock($this->_categoryBlockName)->setLayer($this->getLayer())->init();
        $this->setChild('layer_state', $stateBlock);
        $this->setChild('category_filter', $categoryBlock->addFacetCondition());
        $this->getLayer()->apply();
        $filterableAttributes = $this->_getFilterableAttributes();
        $filters = array();
        foreach ($filterableAttributes as $attribute) {
            if ($attribute->getIsFilterable()) {
                if ($attribute->getAttributeCode() == 'price') {
                    #$filterBlockName = $this->_priceFilterBlockName;
                } elseif ($attribute->getBackendType() == 'decimal') {
                    #$filterBlockName = $this->_decimalFilterBlockName;
                } elseif ($attribute->getSourceModel() == 'eav/entity_attribute_source_boolean') {
                    #$filterBlockName = $this->_booleanFilterBlockName;
                } else {
                    Mage::log($this->_categoryBlockName,null,'bla.log',true);
                    $filterBlockName = $this->_attributeFilterBlockName;
                    $filters[$attribute->getAttributeCode() . '_filter'] = $this->getLayout()->createBlock($filterBlockName)
                        ->setLayer($this->getLayer())
                        ->setAttributeModel($attribute)
                        ->init();
                }

            }
        }
        foreach ($filters as $filterName => $block) {
            $this->setChild($filterName, $block->addFacetCondition());
        }
        $this->getLayer()->apply();
        return $this;
    }

    /**
     * Get layer object
     *
     * @return Hackathon_ElasticgentoCore_Model_Catalog_Layer
     */
    public function getLayer()
    {
        return Mage::getSingleton('elasticgento/catalog_layer');
    }

    /**
     * Get all layer filters
     *
     * @return array
     */
    public function getFilters()
    {
        $filters = array();
        if ($categoryFilter = $this->_getCategoryFilter()) {
            $filters[] = $categoryFilter;
        }

        $filterableAttributes = $this->_getFilterableAttributes();
        foreach ($filterableAttributes as $attribute) {
            $child = $this->getChild($attribute->getAttributeCode() . '_filter');
            //check child is an object
            if (true === is_object($child)) {
                $filters[] = $this->getChild($attribute->getAttributeCode() . '_filter');
            }
        }

        return $filters;
    }
}