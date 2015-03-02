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
 * Elasticgento CatalogSearch fulltext indexer model replacement
 *
 */
class Hackathon_ElasticgentoCatalogSearch_Model_Indexer_Fulltext extends Mage_CatalogSearch_Model_Indexer_Fulltext
{
    /**
     * Retrieve Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return Mage::helper('elasticgento_catalogsearch')->__('Rebuild Catalog product fulltext search index (indexing done within Elasticgento)');
    }

    /**
     * make indexer not usable because indexing is done withing catalog_product_flat
     *
     * @param Mage_Index_Model_Event $event
     * @return bool
     */
    public function matchEvent(Mage_Index_Model_Event $event)
    {
        if (Mage::helper('elasticgento_catalogsearch')->isSearchActive()) {
            return parent::matchEvent($event);
        } else {
            return false;
        }
    }

    /**
     * Rebuild all index data
     */
    public function reindexAll()
    {
        return parent::reindexAll();
//        if (!Mage::helper('elasticgento_catalogsearch')->isSearchActive()) {
//            return parent::reindexAll();
//        }
    }
}