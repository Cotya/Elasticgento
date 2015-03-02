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
 * Elasticgento Client
 *
 */
class Hackathon_ElasticgentoCore_Model_Resource_Client extends \Elastica\Client
{
    /**
     * Search query params with their default values
     *
     * @var array
     */
    protected $_defaultQueryParams = array(
        'offset' => 0,
        'limit' => 100,
        'sort_by' => array(array('score' => 'desc')),
        'fields' => array(),
    );

    /**
     * index instances array cache
     *
     * @var array
     */
    protected $_indexInstances = array();

    /**
     * Set the Elasticsearch Node Connection Data
     *
     * @var array
     */
    final public function __construct($options)
    {
        $config = array(
            'servers' => Mage::helper('elasticgento/config')->getElasticsearchNodeConnectionData()
        );
        //call elastica constructor
        parent::__construct($config);
    }

    /**
     * get array of supported languages codes
     *
     * @return array
     */
    final public function getLanguageCodes()
    {
        return $this->_languageCodes;
    }

    /**
     * get a list of languages
     *
     * @return array
     */
    final public function getLanguages()
    {
        return $this->_languages;
    }

    /**
     * get index name by store id
     *
     * @param integer $storeId
     * @return string
     */
    final public function getIndexName($storeId = null)
    {
        return sprintf('%s_store_%s',
            (string)Mage::getConfig()->getNode('global/resources/default_setup/connection/dbname'),
            $storeId);
    }

    /**
     * get index instance
     *
     * @param integer $storeId
     * @return \Elastica\Index
     */
    final public function getIndex($storeId = null)
    {
        if (false === isset($this->_indexInstances[$this->getIndexName($storeId)])) {
            $this->_indexInstances[$this->getIndexName($storeId)] = parent::getIndex($this->getIndexName($storeId));
        }
        return $this->_indexInstances[$this->getIndexName($storeId)];
    }

    /**
     * escape reserved characters
     *
     * @param string $value
     * @return mixed
     */
    public function escape($value)
    {
        $pattern = '/(\+|-|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/';
        $replace = '\\\$1';

        return preg_replace($pattern, $replace, $value);
    }

    /**
     * escapes specified phrase
     *
     * @param string $value
     * @return string
     */
    public function escapePhrase($value)
    {
        $pattern = '/("|\\\)/';
        $replace = '\\\$1';

        return preg_replace($pattern, $replace, $value);
    }

    /**
     * get a new Document
     *
     * @param integer|string $id
     * @param mixed $data
     */
    public function getDocument($id, $data)
    {
        return $document = new Elastica\Document($id, $data);
    }
}