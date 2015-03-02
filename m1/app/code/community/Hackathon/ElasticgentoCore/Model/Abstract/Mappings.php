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
 * Elasticgento Abstract Index Mappings
 *
 */
abstract class Hackathon_ElasticgentoCore_Model_Abstract_Mappings
{
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
    protected $_entity = null;

    /**
     * Eav Entity Type Id
     *
     * @var int
     */
    protected $_entityTypeId;

    /**
     * array of index settings with analyzers etc
     *
     * @var array
     */
    protected $_settings = null;

    /**
     * array with Attribute codes for index
     *
     * @var array
     */
    protected $_attributeCodes;

    /**
     * Attribute objects
     *
     * @var array
     */
    protected $_attributes = null;

    /**
     * index templates for dynamic fields
     *
     * @var array
     */
    protected $_dynamicTemplates = array();

    /**
     * mapping data
     *
     * @var array
     */
    protected $_mappings = null;

    /**
     * Retrieve entity type
     *
     * @return string
     */
    abstract function getEntityType();

    /**
     * Set store scope
     *
     * @param int|string|Mage_Core_Model_Store $store
     * @return Mage_Catalog_Model_Resource_Collection_Abstract
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
     * @return Mage_Catalog_Model_Resource_Collection_Abstract
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
     * Return current store id
     *
     * @return int
     */
    public function getStoreId()
    {
        if (null === $this->_storeId) {
            $this->setStoreId(Mage::app()->getStore()->getId());
        }
        return $this->_storeId;
    }

    /**
     * Retrieve default store id
     *
     * @return int
     */
    public function getDefaultStoreId()
    {
        return Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
    }

    /**
     * Get mapping entity object
     *
     * @return Mage_Eav_Model_Entity_Abstract
     */
    public function getEntity()
    {
        if (null === $this->_entity) {
            $this->_entity = Mage::getModel('eav/entity')->setType($this->getEntityType());
        }
        return $this->_entity;
    }

    /**
     * Get setting object
     *
     * @return array
     */
    protected function _getIndexSettings()
    {
        if (null === $this->_settings) {
            $this->_settings = Mage::getModel('elasticgento/abstract_settings')->setStore($this->getStoreId())->getIndexSettings();
        }
        return $this->_settings;
    }

    /**
     * Retrieve Catalog Entity Type Id
     *
     * @return int
     */
    public function getEntityTypeId()
    {
        if ($this->_entityTypeId === null) {
            $this->_entityTypeId = $this->getEntity()->getTypeId();
        }
        return $this->_entityTypeId;
    }

    /**
     * Retrieve attribute objects for flat
     *
     * @return array
     */
    public function getAttributes()
    {
        if ($this->_attributes === null) {
            $this->_attributes = array();
            $attributeCodes = $this->getAttributeCodes();
            $entity = Mage::getSingleton('eav/config')->getEntityType($this->getEntityType())->getEntity();
            foreach ($attributeCodes as $attributeCode) {
                $attribute = Mage::getSingleton('eav/config')
                    ->getAttribute($this->getEntityType(), $attributeCode)->setEntity($entity);
                try {
                    // check if exists source and backend model.
                    // To prevent exception when some module was disabled
                    $attribute->usesSource() && $attribute->getSource();
                    $attribute->getBackend();
                    $this->_attributes[$attributeCode] = $attribute;
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }

        return $this->_attributes;
    }

    /**
     * get list of all needed attribute codes
     * @todo make filters to reduce amount of data easier configurable
     */
    public function getAttributeCodes()
    {
        $forcedAttributeCodes = array(
            'name',
            'sku',
            'visibility',
        );

        if ($this->_attributeCodes === null) {
            /** @var Mage_Core_Model_Resource $resource */
            $resource = Mage::getSingleton('core/resource');
            $select = $resource->getConnection('core_read')->select();

            $this->_attributeCodes = array();
            $select = $select
                ->from(array('main_table' => $resource->getTableName('eav/attribute')))
                ->join(
                    array('additional_table' => $resource->getTableName('catalog/eav_attribute')),
                    'additional_table.attribute_id = main_table.attribute_id'
                )
                ->where('main_table.entity_type_id = :entity_type_id');
            $result = $resource->getConnection('core_read')->fetchAll($select, array('entity_type_id' => $this->getEntityTypeId()));
            Mage::getSingleton('eav/config')->importAttributesData($this->getEntityType(), $result);
            foreach ($result as $data) {
                if(
                    $data['is_searchable'] == "0"
                    && $data['is_filterable'] == "0"
                    && $data['used_in_product_listing'] == "0"
                    && $data['is_visible_on_front'] == "0"
                    && !in_array($data['attribute_code'], $forcedAttributeCodes)
                ){
                    continue;
                }
                $this->_attributeCodes[$data['attribute_id']] = $data['attribute_code'];
            }
            unset($result);
        }
        return $this->_attributeCodes;
    }

    /**
     * get an array of dynamic field definitions
     *
     * @return array
     */
    public function getDynamicTemplates()
    {
        return $this->_dynamicTemplates;
    }

    /**
     * get mapping from SQL to Elasticsearch
     * @return array
     * @todo make it cacheable
     */
    public function getMappings()
    {
        if (null === $this->_mappings) {
            $this->_createMappings();
        }

        return $this->_mappings;
    }

    /**
     * default mappings like entity_id etc
     *
     * @return array
     */
    protected function getDefaultMappings()
    {
        return array();
    }

    /**
     * generate mapping from attributes
     * @todo this code is dirty but works for now
     * @todo export field mapping for sortable
     */
    private function _createMappings()
    {
        //get table prefix for field detection
        $tablePrefix = $this->getEntity()->getEntityTable();
        $this->_mappings = $this->getDefaultMappings();
        foreach ($this->getAttributes() as $attribute) {
            /** @var Mage_Eav_Model_Entity_Attribute_Abstract $attribute */
            $columns = $attribute->getFlatColumns();
            if (false === isset($columns[$attribute->getAttributeCode()]) || false === is_array($columns) || count($columns) == 0) {
                $columnType = $attribute->getBackendTable();
                //replace table prefix
                $columnType = str_replace(array($tablePrefix, '_', '-'), '', $columnType);
                $fieldType = $this->_getFieldMappingType($columnType);
                $this->_mappings[$attribute->getAttributeCode()] = $this->_getFieldMapping($attribute, $fieldType, $attribute->getAttributeCode());
            } else {
                foreach ($columns as $fieldName => $column) {
                    $fieldType = $this->_getFieldMappingType($column['type']);
                    $this->_mappings[$fieldName] = $this->_getFieldMapping($attribute, $fieldType, $fieldName);
                }
            }
        }
    }

    /**
     * get elasticsearch field mapping
     *
     * @param $attribute Mage_Eav_Model_Entity_Attribute_Abstract
     * @param $fieldType
     * @return array
     */
    protected function _getFieldMapping(Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $fieldType, $fieldName)
    {
        switch ($fieldType) {
            case 'date':
            {
                $mapping['type'] = 'date';
                $mapping['format'] = 'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd';
                break;
            }
            case 'string':
            {
                //@todo add backend flags for analyzers and so on
                if (1 == $attribute->getisSearchable() || 1 == $attribute->getisVisibleInAdvancedSearch()) {
                    $mapping = array(
                        'type' => 'multi_field',
                        'fields' => array(
                            $fieldName => array(
                                "store" => 'no',
                                'type' => 'string',
                                'boost' => $attribute->getSearchWeight()
                            ),
                            'untouched' => array(
                                'type' => 'string',
                                'index' => 'not_analyzed',
                            ),
                        ),
                    );
                    //for now we implementing all analyzer
                    //@todo make multiselect in backend to make analyser selectable
                    $settings = $this->_getIndexSettings();
                    foreach (array_keys($settings['analysis']['analyzer']) as $analyzer) {
                        $mapping['fields'][$analyzer] = array(
                            'type' => 'string',
                            'analyzer' => $analyzer,
                            'boost' => $attribute->getSearchWeight(),
                        );
                    }
                } else {
                    $mapping = array('type' => 'string', 'index' => 'not_analyzed');
                }
                break;
            }
            default:
                {
                $mapping = array('type' => $fieldType);
                break;
                }
        }
        return $mapping;
    }

    /**
     * get column mapping from sql to elasticsearch fields
     *
     * @param $type
     * @return string
     * @todo this code is dirty but works for now
     */
    protected function _getFieldMappingType($type)
    {
        //put not default to top
        switch (true) {
            case strpos($type, 'smallint') === 0:
            case strpos($type, 'tinyint') === 0:
            case strpos($type, 'int') === 0:
            {
                return 'integer';

            }
            case strpos($type, 'decimal') === 0:
            {
                return 'double';
            }
            case strpos($type, 'datetime') === 0:
            case strpos($type, 'timestamp') === 0:
            {
                return 'date';
            }
            case strpos($type, 'text') === 0:
            case strpos($type, 'varchar') === 0:
            case strpos($type, 'char') === 0:
            default:
                {
                return 'string';
                }
        }
    }
}