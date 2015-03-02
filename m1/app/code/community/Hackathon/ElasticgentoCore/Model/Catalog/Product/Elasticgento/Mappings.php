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
 * Catalog Product Elasticgento Index Mappings
 *
 */
class Hackathon_ElasticgentoCore_Model_Catalog_Product_Elasticgento_Mappings extends Hackathon_ElasticgentoCore_Model_Abstract_Mappings
{
    /**
     * index templates for dynamic fields
     *
     * @var array
     */
    protected $_dynamicTemplates = array(
        array(
            'template_price_index' =>
                array(
                    "path_match" => 'price_index.price_customer_group_*',
                    'match' => 'price_customer_group_*',
                    'mapping' => array(
                        'type' => 'object',
                        'properties' => array(
                            'price' => array('type' => 'double'),
                            'min_price' => array('type' => 'double'),
                            'final_price' => array('type' => 'double'),
                            'max_price' => array('type' => 'double'),
                            'tier_price' => array('type' => 'double'),
                            'group_price' => array('type' => 'double')
                        )
                    )
                ),
        ),
        array('template_category_sort' =>
            array(
                "path_match" => "category_sort.category_*",
                'match' => 'category_sort',
                'mapping' => array(
                    'type' => 'integer'
                )
            )
        ),
        array('template_link_types' =>
            array(
                "path_match" => "product_link.*",
                'match' => '*',
                'mapping' => array(
                    'type' => 'integer'
                )
            )
        )
    );

    /**
     * Retrieve entity type
     *
     * @return string
     */
    public function getEntityType()
    {
        return Mage_Catalog_Model_Product::ENTITY;
    }

    /**
     * default mappings like entity_id etc
     *
     * @return array
     */
    protected function getDefaultMappings()
    {
        return array('entity_id' => array('type' => 'integer'),
            'attribute_set_id' => array('type' => 'integer'),
            'type_id' => array('type' => 'string', 'index' => 'not_analyzed'),
            'categories' => array('type' => 'integer'),
            'anchors' => array('type' => 'integer', 'store' => false));
    }
}