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
 * Elasticgento base collection
 *
 */
abstract class Hackathon_ElasticgentoCore_Model_Resource_Collection extends Mage_Eav_Model_Entity_Collection_Abstract
{
    const SORT_ORDER_ASC = 'ASC';
    const SORT_ORDER_DESC = 'DESC';

    /**
     * @var Hackathon_ElasticgentoCore_Model_Resource_Client
     */
    protected $_client = null;

    /**
     * Current scope (store Id)
     *
     * @var int
     */
    protected $_storeId;

    /**
     * Entity object to define collection's attributes
     *
     * @var Mage_Eav_Model_Entity_Abstract
     */
    protected $_entity;

    /**
     * query object
     *
     * @var Elastica\Query()
     */
    protected $_query = null;

    /**
     * Filter object
     *
     * @var Elastica\Filter\BoolAnd
     */
    protected $_queryFilter = null;

    /**
     * array with filters to merge
     *
     * @var array
     */
    protected $_queryAttributeFilters = array();

    /**
     * array with facets to merge
     *
     * @var array
     */
    protected $_queryFacets = array();

    /**
     * array with facets to merge
     *
     * @var array
     */
    protected $_responseFacets = array();

    /**
     * extra attributes to automaticly map
     *
     * @var array
     */
    protected $_selectExtraAttributes = array();

    /**
     * resource collection initalization
     *
     * @param string $model
     * @return Hackathon_ElasticgentoCore_Model_Resource_Collection_Abstract
     */
    protected function _init($model, $entityModel = null)
    {
        $this->setItemObjectClass(Mage::getConfig()->getModelClassName($model));
        if ($entityModel === null) {
            $entityModel = $model;
        }
        $entity = Mage::getResourceSingleton($entityModel);
        $this->setEntity($entity);

        return $this;
    }

    /**
     * Set entity to use for attributes
     *
     * @param Mage_Eav_Model_Entity_Abstract $entity
     * @throws Mage_Eav_Exception
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function setEntity($entity)
    {
        if ($entity instanceof Mage_Eav_Model_Entity_Abstract) {
            $this->_entity = $entity;
        } elseif (is_string($entity) || $entity instanceof Mage_Core_Model_Config_Element) {
            $this->_entity = Mage::getModel('eav/entity')->setType($entity);
        } else {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Invalid entity supplied: %s', print_r($entity, 1)));
        }
        return $this;
    }

    /**
     * Get collection's entity object
     *
     * @return Mage_Eav_Model_Entity_Abstract
     */
    public function getEntity()
    {
        if (empty($this->_entity)) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Entity is not initialized'));
        }
        return $this->_entity;
    }

    /**
     * Set store scope
     *
     * @param int|string|Mage_Core_Model_Store $store
     * @return Hackathon_ElasticgentoCore_Model_Resource_Collection
     */
    public function setStore($store)
    {
        $this->setStoreId(Mage::app()->getStore($store)->getId());
        return $this;
    }

    /**
     * Set store scope
     *
     * @param int|string|Mage_Core_Model_Store $storeId
     * @return Hackathon_ElasticgentoCore_Model_Resource_Collection
     */
    public function setStoreId($storeId)
    {
        if ($storeId instanceof Mage_Core_Model_Store) {
            $storeId = $storeId->getId();
        }
        $this->_storeId = (int)$storeId;
        return $this;
    }

    /**
     * @return Hackathon_ElasticgentoCore_Model_Resource_Client
     */
    protected function getAdapter()
    {
        if (null === $this->_client) {

            $this->_client = Mage::getResourceModel('elasticgento/client');

        }
        return $this->_client;
    }

    /**
     * Init select
     *
     * @return Hackathon_ElasticgentoCore_Model_Resource_Collection
     */
    protected function _initSelect()
    {
        return $this;
    }

    /**
     * Return current store id
     *
     * @return int
     */
    public function getStoreId()
    {
        if (true === is_null($this->_storeId)) {
            $this->setStoreId(Mage::app()->getStore()->getId());
        }
        return $this->_storeId;
    }


    /**
     * Before load action
     *
     * @return Hackathon_ElasticgentoCore_Model_Resource_Collection
     */
    protected function _beforeLoad()
    {
        //set up query object
        $this->_queryFilter = new Elastica\Filter\BoolAnd();
        return $this;
    }

    /**
     * Hook for operations before rendering filters
     *
     * @return Hackathon_ElasticgentoCore_Model_Resource_Collection
     */
    protected function _renderFiltersBefore()
    {
        return $this;
    }

    /**
     * render collection filters
     *
     * @return Hackathon_ElasticgentoCore_Model_Resource_Collection
     */
    protected function _renderFilters()
    {
        $this->_queryFilter = new Elastica\Filter\BoolAnd();
        foreach ($this->_queryAttributeFilters as $filter) {
            $this->_queryFilter->addFilter($filter);
        }
        return $this;
    }

    /**
     * Hook for operations after rendering filters
     *
     * @return Hackathon_ElasticgentoCore_Model_Resource_Collection
     */
    protected function _renderFiltersAfter()
    {
        return $this;
    }

    /**
     * Hook for operations before rendering query object
     *
     * @return Hackathon_ElasticgentoCore_Model_Resource_Collection
     */
    protected function _renderQueryBefore()
    {
        $this->_query = new Elastica\Query();
        return $this;
    }

    /**
     * render collection facets
     *
     * @return Hackathon_ElasticgentoCore_Model_Resource_Collection
     */
    protected function _renderQuery()
    {
        $queryString = new Elastica\Query\MatchAll();
        $filteredQuery = new Elastica\Query\Filtered(
            $queryString,
            $this->_queryFilter

        );
        $this->_query->setQuery($filteredQuery);
        return $this;
    }

    /**
     * Hook for operations after query object
     *
     * @return Hackathon_ElasticgentoCore_Model_Resource_Collection
     */
    protected function _renderQueryAfter()
    {
        return $this;
    }

    /**
     * Hook for operations before rendering facets
     *
     * @return Hackathon_ElasticgentoCore_Model_Resource_Collection
     */
    protected function _renderFacetsBefore()
    {
        return $this;
    }

    /**
     * render collection facets
     *
     * @return Hackathon_ElasticgentoCore_Model_Resource_Collection
     */
    protected function _renderFacets()
    {
        foreach ($this->_queryFacets as $facet) {
            $this->_query->addFacet($facet);
        }
        return $this;
    }

    /**
     * Hook for operations after rendering facets
     *
     * @return Hackathon_ElasticgentoCore_Model_Resource_Collection
     */
    protected function _renderFacetsAfter()
    {
        return $this;
    }

    /**
     * render collection sort
     *
     * @return Hackathon_ElasticgentoCore_Model_Resource_Collection
     */
    protected function _renderOrders()
    {
        return $this;
    }

    /**
     * add facet to collection
     *
     * @param AbstractFacet $facet
     * @return Hackathon_ElasticgentoCore_Model_Resource_Collection
     */
    public function addFacet($facet)
    {
        $this->_queryFacets[$facet->getName()] = $facet;
        return $this;
    }

    /**
     * add facet to collection
     *
     * @param AbstractFacet $facet
     * @return Hackathon_ElasticgentoCore_Model_Resource_Collection
     */
    public function removeFacet($name)
    {
        unset($this->_queryFacets[$name]);
        return $this;
    }

    /**
     * @param array|int|Mage_Eav_Model_Entity_Attribute_Interface|string $attribute
     * @param null $condition
     * @return Mage_Eav_Model_Entity_Collection_Abstract|void
     */
    public function addAttributeToFilter($attribute, $condition = NULL, $joinType = 'inner')
    {
        list($operator, $value) = each($condition);

        switch ($operator) {
            case 'eq':
            {
                $this->_queryAttributeFilters[$attribute] = new Elastica\Filter\Term(array($attribute => $value));
                break;
            }
            case 'in':
            {
                #var_dump($value);
                #$filter = new Elastica\Filter\BoolOr();
                #foreach($value as $param)
                #$this->_filterAttributes[] = new Elastica\Filter\Term(array($attribute => $value));
                break;
            }
        }
        return $this;
    }

    /**
     * Load collection data into object items
     *
     * @return Hackathon_ElasticgentoCore_Model_Resource_Collection
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }
        Varien_Profiler::start('__ELASTICGENTO_COLLECTION_BEFORE_LOAD__');
        Mage::dispatchEvent('elasticgento_collection_abstract_load_before', array('collection' => $this));
        $this->_beforeLoad();
        Varien_Profiler::stop('__ELASTICGENTO_COLLECTION_BEFORE_LOAD__');

        /**
         * render filters
         */
        Varien_Profiler::start('__ELASTICGENTO_RENDER_FILTERS__');
        $this->_renderFiltersBefore();
        $this->_renderFilters();
        $this->_renderFiltersAfter();
        Varien_Profiler::stop('__ELASTICGENTO_RENDER_FILTERS__');

        /**
         * render query object
         */
        Varien_Profiler::start('__ELASTICGENTO_RENDER_QUERY__');
        $this->_renderQueryBefore();
        $this->_renderQuery();
        $this->_renderQueryAfter();
        Varien_Profiler::stop('__ELASTICGENTO_RENDER_QUERY__');

        /**
         * render orders
         */
        Varien_Profiler::start('__ELASTICGENTO_RENDER_ORDERS__');
        $this->_renderOrders();
        Varien_Profiler::stop('__ELASTICGENTO_RENDER_ORDERS__');

        /**
         * render facets
         */
        Varien_Profiler::start('__ELASTICGENTO_RENDER_FACETS__');
        $this->_renderFacetsBefore();
        $this->_renderFacets();
        $this->_renderFacetsAfter();
        Varien_Profiler::stop('__ELASTICGENTO_RENDER_FACETS__');

        Varien_Profiler::start('__ELASTICGENTO_QUERY__');
        $this->_query->setFrom($this->getPageSize() * ($this->_curPage - 1));
        $this->_query->setSize($this->getPageSize());
        $type = $this->getAdapter()->getIndex($this->_storeId)->getType($this->getEntity()->getType());
        try {
            #var_dump(json_encode($this->_query->toArray()));
            $result = $type->search($this->_query);
        } catch (Exception $e) {
//            var_dump($e->getMessage());
        }
        Varien_Profiler::start('__ELASTICGENTO_HANDLE_RESPONSE__');
        $this->_responseFacets = $result->getFacets();
        Varien_Profiler::stop('__ELASTICGENTO_QUERY__');
        Varien_Profiler::start('__ELASTICGENTO_HANDLE_RESPONSE__');
        foreach ($result->getResults() as $item) {
            $data = $item->getData();
            $object = $this->getNewEmptyItem();
            foreach ($this->_defaultFields as $field) {
                if (true === isset($data[$field])) {
                    $object->setData($field, $data[$field]);
                }
            }
            foreach ($this->_selectAttributes as $attributeCode => $attributeId) {
                if (isset($data[$attributeCode . '_value'])) {
                    $object->setData($attributeCode, $data[$attributeCode . '_value']);
                } else {
                    $object->setData($attributeCode, $data[$attributeCode]);
                }
            }
            $this->_addObjectBefore($object, $data);
            $this->addItem($object);
            if (isset($this->_itemsById[$object->getId()])) {
                $this->_itemsById[$object->getId()][] = $object;
            } else {
                $this->_itemsById[$object->getId()] = array($object);
            }
        }
        foreach ($this->_items as $item) {
            $item->setOrigData();
        }

        $this->_totalRecords = $result->getTotalHits();
        Varien_Profiler::stop('__ELASTICGENTO_HANDLE_RESPONSE__');
        $this->_afterLoad();
        Mage::dispatchEvent('elasticgento_collection_abstract_load_after', array('collection' => $this));
        Varien_Profiler::stop('__EAV_COLLECTION_AFTER_LOAD__');
        $this->_setIsLoaded();
        return $this;
    }


    /**
     * callback before object is added to collection
     *
     * @param $object
     * @return $this
     */
    protected function _addObjectBefore($object, $data)
    {
        foreach ($this->_selectExtraAttributes as $field => $callback) {
            call_user_func(array($this, $callback), $object, $data, $field);
        }
        return $this;
    }

    /**
     * get Facet data
     *
     * @param $field
     * @return array
     */
    public function getFacetData($field)
    {
        $data = array();
        if (true === isset($this->_responseFacets[$field])) {
            if (true === isset($this->_responseFacets[$field]['terms'])) {
                foreach ($this->_responseFacets[$field]['terms'] as $facet) {
                    $data[$facet['term']] = $facet['count'];
                }
            }
        }
        return $data;
    }

    /**
     * Get all data array for collection
     *
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }
}