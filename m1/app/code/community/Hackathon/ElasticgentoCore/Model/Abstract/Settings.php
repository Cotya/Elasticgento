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
 * Elasticgento Abstract Index Settings
 *
 */
class Hackathon_ElasticgentoCore_Model_Abstract_Settings
{
    /**
     * Snowball languages supported in elasticsearch
     *
     * @var array
     * @link http://www.elasticsearch.org/guide/reference/index-modules/analysis/snowball-tokenfilter.html
     */
    protected $_supportedLanguages = array(
        'Armenian',
        'Basque',
        'Catalan',
        'Danish',
        'Dutch',
        'English',
        'Finnish',
        'French',
        'German',
        'Hungarian',
        'Italian',
        'Kp',
        'Lovins',
        'Norwegian',
        'Porter',
        'Portuguese',
        'Romanian',
        'Russian',
        'Spanish',
        'Swedish',
        'Turkish',
    );

    /**
     * supported language languages codes present by Snowball or default in elasticsearch or lucene
     *
     * @var array
     * @link http://www.elasticsearch.org/guide/reference/index-modules/analysis/snowball-tokenfilter.html
     */
    protected $_supportedLanguageCodes = array(
        /**
         * SnowBall filter based
         */
        // Danish
        'da' => 'da_DK',
        // Dutch
        'nl' => 'nl_NL',
        // English
        'en' => array('en_AU', 'en_CA', 'en_NZ', 'en_GB', 'en_US'),
        // Finnish
        'fi' => 'fi_FI',
        // French
        'fr' => array('fr_CA', 'fr_FR'),
        // German
        'de' => array('de_DE', 'de_DE', 'de_AT'),
        // Hungarian
        'hu' => 'hu_HU',
        // Italian
        'it' => array('it_IT', 'it_CH'),
        // Norwegian
        'nb' => array('nb_NO', 'nn_NO'),
        // Portuguese
        'pt' => array('pt_BR', 'pt_PT'),
        // Romanian
        'ro' => 'ro_RO',
        // Russian
        'ru' => 'ru_RU',
        // Spanish
        'es' => array('es_AR', 'es_CL', 'es_CO', 'es_CR', 'es_ES', 'es_MX', 'es_PA', 'es_PE', 'es_VE'),
        // Swedish
        'sv' => 'sv_SE',
        // Turkish
        'tr' => 'tr_TR',

        /**
         * Lucene class based
         */
        // Czech
        'cs' => 'cs_CZ',
        // Greek
        'el' => 'el_GR',
        // Thai
        'th' => 'th_TH',
        // Chinese
        'zh' => array('zh_CN', 'zh_HK', 'zh_TW'),
        // Japanese
        'ja' => 'ja_JP',
        // Korean
        'ko' => 'ko_KR'
    );


    /**
     * Current scope (store Id)
     *
     * @var int
     */
    protected $_storeId;

    /**
     * already created settings as array cache by store id
     *
     * @var array
     */
    protected $_settingsCache = array();

    /**
     * Set store scope
     *
     * @param int|string|Mage_Core_Model_Store $store
     * @return Mage_Catalog_Model_Resource_Collection_Abstract
     */
    public function setStore($store)
    {
        $this->setStoreId(Mage::app()->getStore($store)->getId());
        return $this;
    }

    /**
     * Set store scope
     *
     * @param int|string|Mage_Core_Model_Store $storeId
     * @return Mage_Catalog_Model_Resource_Collection_Abstract
     */
    public function setStoreId($storeId)
    {
        if ($storeId instanceof Mage_Core_Model_Store) {
            $storeId = $storeId->getId();
        }
        $this->_storeId = (int)$storeId;
        return $this;
    }

    /**
     * Return current store id
     *
     * @return int
     */
    public function getStoreId()
    {
        if (null === $this->_storeId) {
            $this->setStoreId(Mage::app()->getStore()->getId());
        }
        return $this->_storeId;
    }

    /**
     * Retrieve default store id
     *
     * @return int
     */
    public function getDefaultStoreId()
    {
        return Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
    }

    /**
     * get language code for current store scope needed for stemming etc.
     *
     * @return boolean|string
     */
    public function getLanguageCodeByStore()
    {
        $localeCode = (string)Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE, $this->getStoreId());
        if (true === empty($localeCode)) {
            return false;
        }
        //check we already tried to detect langucode
        if (false === isset($this->_supportedLanguageCodes[$localeCode])) {
            $this->_supportedLanguageCodes[$localeCode] = false;
            foreach ($this->_supportedLanguageCodes as $code => $locales) {
                if (true === is_array($locales)) {
                    if (true === in_array($localeCode, $locales)) {
                        $this->_supportedLanguageCodes[$localeCode] = $code;
                    }
                } elseif ($localeCode == $locales) {
                    $this->_supportedLanguageCodes[$localeCode] = $code;
                }
            }
        }
        return $this->_supportedLanguageCodes[$localeCode];
    }

    /**
     * get index settings
     *
     * @todo make this cacheable via magento cache backend
     * @todo later optimize analyzer
     * @todo later implement synonyms
     * @return array
     */
    public function getIndexSettings()
    {
        if (false === isset($this->_settingsCache[$this->getStoreId()])) {
            $indexSettings = array();
            $indexSettings['number_of_shards'] = Mage::helper('elasticgento/config')->getNumberOfShards();
            $indexSettings['number_of_replicas'] = Mage::helper('elasticgento/config')->getNumberOfReplicas();
            // define analyzer
            $indexSettings['analysis']['analyzer'] = array(
                'whitespace' => array(
                    'tokenizer' => 'standard',
                    'filter' => array('lowercase'),
                ),
                'edge_ngram_front' => array(
                    'tokenizer' => 'standard',
                    'filter' => array('length', 'edge_ngram_front', 'lowercase'),
                ),
                'edge_ngram_back' => array(
                    'tokenizer' => 'standard',
                    'filter' => array('length', 'edge_ngram_back', 'lowercase'),
                ),
                'shingle' => array(
                    'tokenizer' => 'standard',
                    'filter' => array('shingle', 'length', 'lowercase'),
                ),
                'shingle_strip_ws' => array(
                    'tokenizer' => 'standard',
                    'filter' => array('shingle', 'strip_whitespaces', 'length', 'lowercase'),
                ),
                'shingle_strip_apos_and_ws' => array(
                    'tokenizer' => 'standard',
                    'filter' => array('shingle', 'strip_apostrophes', 'strip_whitespaces', 'length', 'lowercase'),
                ),
            );
            // define filters
            $indexSettings['analysis']['filter'] = array(
                'shingle' => array(
                    'type' => 'shingle',
                    'max_shingle_size' => 5,
                    'output_unigrams' => true,
                ),
                'strip_whitespaces' => array(
                    'type' => 'pattern_replace',
                    'pattern' => '\s',
                    'replacement' => '',
                ),
                'strip_apostrophes' => array(
                    'type' => 'pattern_replace',
                    'pattern' => "'",
                    'replacement' => '',
                ),
                'edge_ngram_front' => array(
                    'type' => 'edgeNGram',
                    'min_gram' => 3,
                    'max_gram' => 8,
                    'side' => 'front',
                ),
                'edge_ngram_back' => array(
                    'type' => 'edgeNGram',
                    'min_gram' => 3,
                    'max_gram' => 8,
                    'side' => 'back',
                ),
                'length' => array(
                    'type' => 'length',
                    'min' => 2,
                ),
            );
            $languageCode = $this->getLanguageCodeByStore();
            $language = Zend_Locale_Data::getContent('en_GB', 'language', $languageCode);
            if (true === in_array($language, $this->_supportedLanguages)) {
                $indexSettings['analysis']['analyzer']['analyzer_' . $languageCode] = array(
                    'type' => 'custom',
                    'tokenizer' => 'standard',
                    'filter' => array('length', 'lowercase', 'snowball_' . $languageCode),
                );
                $indexSettings['analysis']['filter']['snowball_' . $languageCode] = array(
                    'type' => 'snowball',
                    'language' => $language,
                );
            }
            $indexSettings['language'] = $language;
            $indexSettings['language_code'] = $languageCode;
            $this->_settingsCache[$this->getStoreId()] = $indexSettings;
        }
        return $this->_settingsCache[$this->getStoreId()];
    }
}