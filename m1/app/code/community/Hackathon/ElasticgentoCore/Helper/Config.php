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
 * Elasticgento module config helper
 *
 */
class Hackathon_ElasticgentoCore_Helper_Config extends Mage_Core_Helper_Abstract
{

    /**
     * get chunksize for bulk operations
     *
     * @return int
     */
    public function getChunkSize()
    {
        return (int)Mage::getStoreConfig('elasticgento/general/chunksize');
    }

    /**
     * get the connection data of the server nodes
     *
     * @return array
     */
    public function getElasticsearchNodeConnectionData()
    {
        $data = unserialize(Mage::getStoreConfig('elasticgento/general/nodes'));
        foreach ($data as &$server) {
            if (isset($server['auth_username']) && !empty($server['auth_username'])) {
                $server['curl'][CURLOPT_USERPWD] = $server['auth_username'].':'.$server['auth_password'];
            }
            if (isset($server['auth_username']) ){
                unset($server['auth_username'], $server['auth_password']);
            }
            if (isset($server['https'])) {
                $server['transport'] = 'https';
                unset($server['https']);
            }
        }
        return $data;
    }

    /**
     * get number of shards
     *
     * @return int
     */
    public function getNumberOfShards()
    {
        return (int)Mage::getStoreConfig('elasticgento/general/number_of_shards');
    }

    /**
     * get number of replicas
     *
     * @return int
     */
    public function getNumberOfReplicas()
    {
        return (int)Mage::getStoreConfig('elasticgento/general/number_of_replicas');
    }

    /**
     * get maximum number of items in facets
     *
     * @return int
     */
    public function getMaxFacetsSize()
    {
        return (int)Mage::getStoreConfig('elasticgento/general/facets_max_size');
    }

    /**
     * get maximum number of items in facets
     *
     * @return int
     */
    public function getIcuFoldingEnabled()
    {
        return Mage::getStoreConfigFlag('elasticgento/general/enable_icu_folding');
    }
}