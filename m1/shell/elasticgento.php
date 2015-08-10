<?php
/**
 * 
 * 
 * 
 */
require_once __DIR__.'/abstract.php';
require_once __DIR__.'/autoloader_initializer.php';

/**
 *
 *
 * @category    Mage
 * @package     Mage_Shell
 */
class Mage_Shell_Elasticgento extends AutoloaderInitializer
{


    /**
     * Run script
     *
     */
    public function run()
    {

        //Mage::setIsDeveloperMode(true);
        //error_reporting(E_ALL | E_STRICT);
        
        /** @var Varien_Db_Adapter_Pdo_Mysql $readConnection */
        $readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');



        //$indexer = Mage::getSingleton('elasticgento/catalog_product_elasticgento_indexer');
        $indexer = new Hackathon_ElasticgentoCore_Model_Resource_Catalog_Product_Indexer_Elasticgento();

        
        $indexer->reindexAll();
        
        //echo "\r\n\r\n Finished \r\n\r\n";


    }
}

$shell = new Mage_Shell_Elasticgento();
$shell->run();


