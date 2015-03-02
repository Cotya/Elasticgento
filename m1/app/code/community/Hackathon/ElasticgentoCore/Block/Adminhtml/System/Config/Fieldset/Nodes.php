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
 * @link      https://www.mage-hackathon.de/
 *
 * class to display info about version in admin
 *
 */
class Hackathon_ElasticgentoCore_Block_Adminhtml_System_Config_Fieldset_Nodes
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    /**
     * overide method _prepareToRender in Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
     * this is needed to display custom dynamic fieldset
     *
     * @param void
     *
     * @return void
     */
    protected function _prepareToRender()
    {

        $this->_typeRenderer = null;

        $this->addColumn('host', array(
            'label' => Mage::helper('elasticgento')->__('Host')
        ));
        $this->addColumn('port', array(
            'label' => Mage::helper('elasticgento')->__('Port'),
            'style' => 'width:50px',
        ));
        $this->addColumn('https', array(
            'label' => Mage::helper('elasticgento')->__('Https?'),
            'style' => 'width:20px',
            'renderer' => new Hackathon_ElasticgentoCore_Block_Adminhtml_System_Config_Fieldset_Renderer_Checkbox
        ));
        $this->addColumn('auth_username', array(
            'label' => Mage::helper('elasticgento')->__('Auth Username')
        ));
        $this->addColumn('auth_password', array(
            'label' => Mage::helper('elasticgento')->__('Auth Password/Api key')
        ));
        // Disables "Add after" button
        $this->_addAfter       = false;
        $this->_addButtonLabel = Mage::helper('elasticgento')->__('Add Node');
    }

    /**
     * overide method _renderCellTemplate in Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
     * this is needed to display custom dynamic fieldset and inject custom element
     *
     * @see Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
     *
     * @param string $columnName
     *
     * @return void
     */
    protected function _renderCellTemplate($columnName)
    {
        $inputName = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';
        switch ($columnName) {
            case 'type':
                return $this->_getTypeRenderer()
                    ->setName($inputName)
                    ->setTitle($columnName)
                    ->setExtraParams('style="width:50px"')
                    ->setOptions(
                        $this->getElement()->getValues())
                    ->toHtml();
                break;
            default:
                return parent::_renderCellTemplate($columnName);
                break;
        }
    }

    protected function _prepareArrayRow(Varien_Object $row) {
        if ($row->getHttps()) {
            $row->setHttps(' checked="checked"');
        } else {
            $row->unsHttps();
        }
    }
}