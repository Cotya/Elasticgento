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
 * @package   Hackathon_ElasticgentoCatalogSearch
 * @author    Daniel Niedergesäß <daniel.niedergesaess ÄT gmail.com>
 * @author    Andreas Emer <emer ÄT mothership.de>
 * @author    Michael Ryvlin <ryvlin ÄT gmail.com>
 * @author    Johann Niklas <johann ÄT n1klas.de>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://mage-hackathon.de/
 *
 * Elasticgento CatalogSearch fulltext resource model replacement
 *
 */
class Hackathon_ElasticgentoCatalogSearch_Model_Catalogsearch_Resource_Fulltext
    extends Mage_CatalogSearch_Model_Resource_Fulltext
{

    /**
     * override the method to prepare the result
     *
     * @param Mage_CatalogSearch_Model_Fulltext $object
     * @param string                            $queryText
     * @param Mage_CatalogSearch_Model_Query    $query
     *
     * @return Hackathon_ElasticgentoCatalogSearch_Model_Catalogsearch_Resource_Fulltext|Mage_CatalogSearch_Model_Resource_Fulltext
     */
    public function prepareResult(
        $object,
        $queryText,
        $query
    ) {
        $helper = Mage::helper('elasticgento_catalogsearch');
        $flag   = Mage::getModel('core/flag',['flag_code'=>'elasticgento_catalogsearch_inactive_till'])->loadSelf();
        $now = time();

        if (!$helper->isSearchActive() || $flag->getFlagData()>$now) {
            return parent::prepareResult($object, $queryText, $query);
        }

        if (!$query->getIsProcessed()) {
            try{

                $adapter = $helper->getAdapter();
                $result = $this->fetchSearchResultFromElasticSearch($adapter, $queryText, $query);
                if($result && $result instanceof \Elastica\ResultSet )
                {
                    $this->fillSearchResultInMagentoResultTable($result, $query);
                }

            }catch( \Exception $e ){
                Mage::logException($e);
                $flag->setFlagData($now+(60*5))->save();
                return parent::prepareResult($object, $queryText, $query);
            }
        }
    }

    /**
     * @param Hackathon_ElasticgentoCore_Model_Resource_Client $searchAdapter
     * @param string $queryText
     * @param Mage_CatalogSearch_Model_Query $query
     *
     * @return Elastica\ResultSet
     */
    protected function fetchSearchResultFromElasticSearch(
        $searchAdapter,
        $queryText,
        $query
    )
    {
        $elasticQuery = new Elastica\Query();
        $boolQuery = new \Elastica\Query\Bool();
        $fieldnames = Mage::helper('elasticgento_catalogsearch/data')->getSearchableElasticSearchFieldNames();
        foreach($fieldnames as $fieldname){
            $queryFuzzyLikeThis = new \Elastica\Query\FuzzyLikeThis();
            $queryFuzzyLikeThis->addFields(
                [$fieldname]
            );
            if($fieldname=='manufacturer_value'){
                $queryFuzzyLikeThis->setBoost(100);
            }
            if(
                $fieldname=='name'
                || $fieldname=='tags'
            ){
                $queryFuzzyLikeThis->setBoost(50);
            }
            $queryFuzzyLikeThis->setLikeText($queryText);
            $boolQuery->addShould($queryFuzzyLikeThis);
        }

        $elasticQuery->setQuery($boolQuery);

        $returnFields = [
            'entity_id',
            'name',
        ];

        $elasticQuery->setFields($returnFields);
        $result = $searchAdapter->getIndex($query->getStoreId())->search($elasticQuery, [
                'limit' => 500,
            ]);

        return $result;
    }


    /**
     * @param \Elastica\ResultSet            $resultSet
     * @param Mage_CatalogSearch_Model_Query $query
     */
    protected function fillSearchResultInMagentoResultTable(
        \Elastica\ResultSet $resultSet,
        Mage_CatalogSearch_Model_Query $query
    )
    {

        $writeAdapter = $this->_getWriteAdapter();

        $insertQuery = $this->buildInsertQuery($resultSet, $query->getId());

        $writeAdapter->query($insertQuery);

        $query->setIsProcessed(1);

    }

    protected function buildInsertQuery( \Elastica\ResultSet $resultSet, $queryId )
    {

        $writeAdapter = $this->_getWriteAdapter();

        $query = 'INSERT';

        $query = sprintf('%s INTO %s', $query, $writeAdapter->quoteIdentifier($this->getTable('catalogsearch/result')));

        $fields = [
            'query_id',
            'product_id',
            'relevance',
        ];

        $columns   = array_map(array($writeAdapter, 'quoteIdentifier'), $fields);
        $columns   = implode(',', $columns);

        $query .= ' ('.$columns.')';

        $query .= ' VALUES';

        $valueRows = [];

        foreach($resultSet->getResults() as $result){
            $productId = $result->getData()['entity_id'][0];
            $values = [
                (int) $queryId,
                (int) $productId,
                (float) $result->getScore()
            ];
            $valueRows[] = '('.implode(', ',$values).')';
        };

        $query .= ' '.implode(', ', $valueRows);

        $update = array();
        foreach ($fields as $field) {
            $update[] = sprintf('%1$s = VALUES(%1$s)', $writeAdapter->quoteIdentifier($field));
        }

        if ($update) {
            $query = sprintf('%s ON DUPLICATE KEY UPDATE %s', $query, join(', ', $update));
        }

        return $query;

    }

    /**
     * overwrite, as original magento code does nothing else in the end result
     *
     * @param int  $storeId
     * @param null $productIds
     *
     * @return $this|Mage_CatalogSearch_Model_Resource_Fulltext
     */
    protected function _rebuildStoreIndex($storeId, $productIds = null)
    {
        $this->resetSearchResults();
        return $this;
    }

}