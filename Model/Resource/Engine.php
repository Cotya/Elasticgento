<?php
/**
 *
 *
 *
 *
 */

namespace Cotya\Elasticgento\Model\Resource;

class Engine extends \Magento\CatalogSearch\Model\Resource\Engine
{
    /**
    protected function _construct()
    {

        //\logLocal(__FILE__.":".__LINE__);
        parent::_construct();
    }
**/
    /**
     * Retrieve fulltext search result data collection
     *
     * @return \Magento\Catalog\Model\Resource\Product\Collection
     */
    /**
    public function getResultCollection()
    {


        //\logLocal(__FILE__.":".__LINE__);

        return $this->productCollectionFactory->create(
            \Magento\CatalogSearch\Model\Resource\Product\CollectionFactory::PRODUCT_COLLECTION_FULLTEXT
        );
    }**/
}
