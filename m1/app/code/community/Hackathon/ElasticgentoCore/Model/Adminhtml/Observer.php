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
 Elasticgento Adminhtml observer
 *
 */
class Hackathon_ElasticgentoCore_Model_Adminhtml_Observer
{
    /**
     * Adds additional fields to attribute edit form.
     *
     * @param Varien_Event_Observer $observer
     * @todo add more validation / configuration options
     * @todo add selection for search analyzer
     */
    public function catalog_product_attribute_edit_prepare_form(Varien_Event_Observer $observer)
    {

        /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
        $attribute = $observer->getEvent()->getAttribute();
        /** @var $form Varien_Data_Form */
        $form = $observer->getEvent()->getForm();
        $fieldset = $form->addFieldset('elasticgento',
            array('legend' => Mage::helper('elasticgento')->__('Elasticgento'))
        );

        $fieldset->addField(
            'search_weight',
            'text', array(
                'name' => 'search_weight',
                'value' => '0',
                'label' => Mage::helper('elasticgento')->__('Search Weight'),
            ));
        if ($attribute->getAttributeCode() == 'name') {
            $form->getElement('is_searchable')->setDisabled(1);
        };
    }

    /**
     * Check if index needs reindex after attribute save
     *
     * @param Varien_Event_Observer $observer
     * @todo better checks for fields which caused reindex
     */
    public function entity_attribute_save_after(Varien_Event_Observer $observer)
    {
        /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
        $attribute = $observer->getEvent()->getAttribute();
        if ($attribute->getData('search_weight') != $attribute->getOrigData('search_weight')) {
            Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_flat')
                ->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
        }

    }
}