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
 * @author    Johann Niklas <johann ÄT n1klas.de>
 * @author    Tim Vroom<tim ÄT nichecommerce.nl>
 * @copyright Copyright (c) 2014 Hackathon
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      https://www.mage-hackathon.de/
 *
 * class to display checkbox in admin fieldset renderer
 *
 */
class Hackathon_ElasticgentoCore_Block_Adminhtml_System_Config_Fieldset_Renderer_Checkbox extends Mage_Adminhtml_Block_Abstract
{
    protected function _toHtml(){
        return $this->_getCheckboxHtml('1');
    }

    protected function _getCheckboxHtml($value = '') {
        $html = '<input type="checkbox" ';
        $html .= 'name="' . $this->getInputName() . '" ';
        $html .= 'value="' . $this->escapeHtml($value) . '" ';
        $html .= 'class="'. ($this->getInlineCss() ? $this->getInlineCss() : 'checkbox') .'"';
        $html .= '#{' . $this->getColumnName() . '}'. $this->getDisabled() . '/>';
        return $html;
    }
}