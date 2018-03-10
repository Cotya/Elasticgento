<?php

class Hackathon_ElasticgentoCore_Model_Observer
{
    public function fullReindex()
    {
        if (!Mage::getStoreConfig('elasticgento/cron/is_active')) {
            return;
        }
        $indexer = new Hackathon_ElasticgentoCore_Model_Resource_Catalog_Product_Indexer_Elasticgento();
        $indexer->reindexAll();
    }
}
