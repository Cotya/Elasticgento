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
 * Catalog Category Product attribute Filter
 *
 */
class Hackathon_ElasticgentoCore_Model_Catalog_Layer_Filter_Attribute extends Mage_Catalog_Model_Layer_Filter_Attribute
{
    /**
     * adds current attribute facet condition to product collection
     *
     * @return Hackathon_ElasticgentoCore_Model_Catalog_Layer_Filter_Attribute
     */
    public function addFacetToCollection()
    {
        $facet = new \Elastica\Facet\Terms($this->getAttributeModel()->getAttributeCode());
        $facet->setField($this->getAttributeModel()->getAttributeCode());
        $facet->setSize(10);
        $this->getLayer()->getProductCollection()->addFacet($facet);
        return $this;
    }

    /**
     * Retrieves request parameter and applies it to product collection.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param Mage_Core_Block_Abstract $filterBlock
     * @return Hackathon_ElasticgentoCore_Model_Catalog_Layer_Filter_Attribute
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $filter = $request->getParam($this->_requestVar);
        if (is_array($filter) || null === $filter) {
            return $this;
        }

        $text = $this->_getOptionText($filter);
        if ($this->_isValidFilter($filter) && strlen($text)) {
            $this->applyFilterToCollection($this, $filter);
            $this->getLayer()->getState()->addFilter($this->_createItem($text, $filter));
            $this->_items = array();
        }

        return $this;
    }

    /**
     * Applies filter to product collection.
     *
     * @param $filter
     * @param $value
     * @return Hackathon_ElasticgentoCore_Model_Catalog_Layer_Filter_Attribute
     */
    public function applyFilterToCollection($filter, $value)
    {
        if (false === $this->_isValidFilter($value)) {
            $value = array();
        } else if (false === is_array($value)) {
            $value = array($value);
        }
        $this->getLayer()->getProductCollection()->addAttributeToFilter($this->getAttributeModel()->getAttributeCode(), array('eq' => $value));
        return $this;
    }

    /**
     * Retrieves current items data.
     *
     * @return array
     */
    protected function _getItemsData()
    {
        /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
        $attribute = $this->getAttributeModel();
        $this->_requestVar = $attribute->getAttributeCode();

        $layer = $this->getLayer();
        $key = $layer->getStateKey() . '_' . $this->_requestVar;
        $data = $layer->getAggregator()->getCacheData($key);

        if ($data === null) {
            $facets = $this->getLayer()->getProductCollection()->getFacetData($this->getAttributeModel()->getAttributeCode());
            $data = array();
            if (array_sum($facets) > 0) {
                if ($attribute->getFrontendInput() != 'text') {
                    $options = $attribute->getFrontend()->getSelectOptions();
                } else {
                    $options = array();
                    foreach ($facets as $label => $count) {
                        $options[] = array(
                            'label' => $label,
                            'value' => $label,
                            'count' => $count,
                        );
                    }
                }
                foreach ($options as $option) {
                    if (is_array($option['value']) || !Mage::helper('core/string')->strlen($option['value'])) {
                        continue;
                    }
                    $count = 0;
                    $label = $option['label'];
                    if (isset($facets[$option['value']])) {
                        $count = (int)$facets[$option['value']];
                    }
                    if (!$count && $this->_getIsFilterableAttribute($attribute) == self::OPTIONS_ONLY_WITH_RESULTS) {
                        continue;
                    }
                    $data[] = array(
                        'label' => $label,
                        'value' => $option['value'],
                        'count' => (int)$count,
                    );
                }
            }

            $tags = array(
                Mage_Eav_Model_Entity_Attribute::CACHE_TAG . ':' . $attribute->getId()
            );

            $tags = $layer->getStateTags($tags);
            $layer->getAggregator()->saveCacheData($data, $key, $tags);
        }

        return $data;
    }

    /**
     * Returns option label if attribute uses options.
     *
     * @param int $optionId
     * @return bool|int|string
     */
    protected function _getOptionText($optionId)
    {
        if ($this->getAttributeModel()->getFrontendInput() == 'text') {
            return $optionId; // not an option id
        }

        return parent::_getOptionText($optionId);
    }

    /**
     * Checks if given filter is valid before being applied to product collection.
     *
     * @param string $filter
     * @return bool
     */
    protected function _isValidFilter($filter)
    {
        return !empty($filter);
    }
}