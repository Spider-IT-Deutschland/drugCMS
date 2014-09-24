<?php
/**
 * File:
 * class.metatag.creator.html5.php
 *
 * Description:
 *  Check and/or create metatags for html5
 * 
 * @package Core
 * @subpackage Chains
 * @version $Rev$
 * @since 2.0.0
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright (c) 2012-2013, ConLite Team <www.conlite.org>
 * @license http://www.gnu.de/documents/gpl.en.html GPL v3 (english version)
 * @license http://www.gnu.de/documents/gpl.de.html GPL v3 (deutsche Version)
 * @link http://www.conlite.org ConLite.org
 * 
 * $Id$
 */

// security check
defined('CON_FRAMEWORK') or die('Illegal call');

/**
 * Description of class
 *
 * @author Ortwin Pinke <o.pinke@conlite.org>
 */
class MetaTagCreatorHtml5 {
    
    /**
     *
     * @var array 
     */
    protected static $_MetaExtensions = null;
    
    /**
     *
     * @var array default values for meta names
     */
    protected $_aDefinedMeta = array('application-name','author','description','generator','keywords');
    
    /**
     * Holds all config vars for metatag-creator
     * You may add custom-settings using client-setting
     * meta_tag_creator_html5 | [name of setting] | [value]
     * 
     * possible names|values are
     * only_html5|boolean (default:true) ,if set to true non-valide metas are deleted
     * add_article_meta|boolean (default:true), if set to true metas set in article conf will overwrite existing meta
     * use_cache|boolean (default:true) use cache/cachefile or not
     * cachetime|[seconds] 
     * cachedir|[path to writable cache dir]
     *
     * @var array predefined config array 
     */
    protected $_aConfig = array(
        'only_html5'        =>  true,
        'add_article_meta'  =>  true,
        'use_cache'         =>  true
    );
    
    /**
     *
     * @var string path/filename of cachefile 
     */
    protected $_sCacheFile = null;
    
    /**
     * Incoming and Outgoing MetaTags
     * 
     * @var array holds all metatags
     */
    protected $_aMetaTags = array();
    
    /**
     * New created MetaTags
     *
     * @var array 
     */
    protected $_aCreatedMetaTags = array();


    /**
     *
     * @var boolean switch on debugging output
     */
    protected $_bDebug = false;

    /**
     * Constructor
     * 
     * @global int $idart
     * @global int $client
     * @global int $lang
     * @param array $aMTags given array of metatags
     * @param array $aConfig configuration array
     * 
     * @return void
     */
    public function __construct($aMTags, $aConfig) {
        global $idart, $client, $lang;
        
        $this->_iIdart = (int) $idart;
        $this->_iClient = (int) $client;
        $this->_iLang = (int) $lang;
        
        if(is_null(self::$_MetaExtensions)) {
            $file = dirname(dirname(__FILE__))."/conf/MetaExtension.php";
            if ($aTmp = include_once($file)) {
                self::$_MetaExtensions = $aTmp;
            }
            self::$_MetaExtensions = array_merge(self::$_MetaExtensions, $this->_aDefinedMeta);
        }
        
        if(is_array($aConfig) && count($aConfig) > 0) {
            $this->_aConfig = array_merge($this->_aConfig, $aConfig);
        }
        $aCustomConfig = getEffectiveSettingsByType("meta_tag_creator_html5");
        if(is_array($aCustomConfig) && count($aCustomConfig) > 0) {
            $this->_aConfig = array_merge($this->_aConfig, $aCustomConfig);
        }
        
        if(is_array($aMTags) && count($aMTags) > 0) {
            $this->_aMetaTags = array_merge($this->_aMetaTags, $aMTags);
        }
        if($this->_bDebug) {
            echo "<pre>";
            print_r($this->_aMetaTags);
            echo "</pre>";
        }
        $this->_createCacheFileHash();
    }
    
    /**
     * 
     * @return array generated cached metatag array
     */
    public function generateMetaTags() {
        if($this->_aConfig['use_cache'] && $this->_checkCacheFile()) {
            return $this->_getCacheFile();
        }        
        $this->_addArticleMeta();
        
        
        $this->_mergeNewMetaTags();
        if(count($this->_aMetaTags) > 0) {
            $this->_checkForHtml5Tags();
        }        
        if($this->_bDebug) {
            echo "<pre>";
            print_r($this->_aMetaTags);
            echo "</pre>";
        }
        if($this->_aConfig['use_cache'] && $this->_createCacheFile()) {
            return $this->_getCacheFile();
        } 
        return $this->_aMetaTags;
    }
    
    /**
     * Adds article meta to meta array
     * 
     * @global int $lang
     * @global array $encoding
     * @return void
     */
    protected function _addArticleMeta() {
        global $db, $cfg, $lang, $encoding;
        
        if($this->_aConfig['add_article_meta'] === false) return false;
        $oArticle = new Article($this->_iIdart, $this->_iClient, $this->_iLang);
        $aHeadLines = $this->_checkAndMergeArrays($oArticle->getContent("htmlhead"), $oArticle->getContent("head"));
        $aText = $this->_checkAndMergeArrays($oArticle->getContent("html"), $oArticle->getContent("text"));
        $sHead = mb_convert_encoding($this->_getFirstArrayValue($aHeadLines), $encoding[$lang], 'auto');
        #$sText = $this->_getFirstArrayValue($aText);
        $sText = mb_convert_encoding(html_entity_decode(strip_tags(implode(' ', $aText)), ENT_COMPAT, $encoding[$lang]), $encoding[$lang], 'auto');
        if(strlen($sHead)) {
            $sHead = substr(str_replace(array("\r\n", "\r", "\n"),' ',strip_tags($sHead)),0,100);
            $this->_addMeta('description', htmlentities(html_entity_decode($sHead, ENT_COMPAT, $encoding[$lang]), ENT_COMPAT, $encoding[$lang]));
        }
        if(strlen($sHead . $sText)) {
            $sHead = mb_convert_encoding(html_entity_decode(strip_tags(implode(' ', $aHeadLines)), ENT_COMPAT, $encoding[$lang]), $encoding[$lang], 'auto');
            $sText = keywordDensity($sHead, $sText, $encoding[$lang]);
            $this->_addMeta('keywords', $sText);
        }
        $sAuthor = $oArticle->getField('author');
        $sql = 'SELECT realname
                FROM ' . $cfg['tab']['phplib_auth_user_md5'] . '
                WHERE (username="' . $sAuthor . '")';
        $db->query($sql);
        if ($db->next_record()) {
            $sAuthor = $db->f('realname');
        }
        $this->_addMeta('author', $sAuthor);
        
        // get custom meta from article conf
        $aAvailableMeta = conGetAvailableMetaTagTypes();
        foreach($aAvailableMeta as $iIdMeta=>$aValue) {
            if($aValue['fieldname'] != 'name') continue;
            if($this->_isHtml5Ext($aValue['name'])) {
                $sTmpContent = conGetMetaValue($oArticle->getIdArtLang(), $iIdMeta);
                if(empty($sTmpContent)) continue;
                $this->_addMeta($aValue['name'], htmlentities(html_entity_decode($sTmpContent, ENT_COMPAT, $encoding[$lang]), ENT_COMPAT, $encoding[$lang]));
            }
        }
        unset($oArticle);
    }
    
    protected function _mergeNewMetaTags() {
        if(count($this->_aCreatedMetaTags) > 0) {
            foreach($this->_aCreatedMetaTags as $iKey=>$aValue) {
                $iKey = $this->_inMetaArray($aValue['name'], $this->_aMetaTags);
                if($iKey !== false) {
                    $this->_aMetaTags[$iKey]['content'] = $aValue['content'];
                } else {
                    array_push($this->_aMetaTags, $aValue);
                }
            }
        }
    }

    /**
     * Check meta array for valid html5 meta tags
     * 
     * @todo add support for other meta tags than name
     * @return void
     */
    protected function _checkForHtml5Tags() {
        if(!$this->_aConfig['only_html5']) return;
        
        foreach($this->_aMetaTags as $iKey => $aValue) {
            if(key_exists('name', $aValue)) {
                if($this->_isHtml5Ext($aValue['name'])) continue;
                unset($this->_aMetaTags[$iKey]);
            }
        }
        
    }
    
    /**
     * Check if extensions is registered
     * 
     * @uses $_MetaExtensions Array of default and registered extensions
     * @param string $sExt
     * @return boolean
     */
    protected function _isHtml5Ext($sExt) {
        $sExt = strtolower($sExt);
        // check standard tags first
        if(in_array($sExt, $this->_aDefinedMeta)) return true;
        // check only names to save time
        if(in_array($sExt, self::$_MetaExtensions['names'])) return true;
        // now check keys with deeper arrays
        if(array_key_exists($sExt, self::$_MetaExtensions)) return true;
        // parts
        foreach(self::$_MetaExtensions as $sKey=>$aValue) {
            if($sKey === $sExt) return true;
            if(stristr($sKey, $sExt)) return true;
        }        
        return false;
    }

    /**
     * Cachefile exists and not outdated
     * 
     * @return boolean
     */
    protected function _checkCacheFile() {
        if(file_exists($this->_sCacheFile)) {
            $iDiff = mktime() - filemtime($this->_sCacheFile);
            if($iDiff < $this->_aConfig['cachetime']) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Get meta-array from cachefile
     * 
     * @return array
     */
    protected function _getCacheFile() {
        return unserialize(file_get_contents($this->_sCacheFile));
    }
    
    /**
     * Create cachefile
     * 
     * @return boolean
     */
    protected function _createCacheFile() {
        return (file_put_contents($this->_sCacheFile, serialize($this->_aMetaTags)) === false)?false:true;
    }

    /**
     * Create path to cachefile with hashed filename
     * 
     * @global int $idart
     * @global int $lang
     * @return void
     */
    protected function _createCacheFileHash() {
        global $idart, $lang;
        if(isset($this->_aConfig['cachedir']) 
                && !empty($this->_aConfig['cachedir']) 
                && is_dir($this->_aConfig['cachedir'])
                && is_writable($this->_aConfig['cachedir'])) {
            $hash = 'metatag_'.md5($idart.'/'.$lang);
            $this->_sCacheFile = $this->_aConfig['cachedir'].$hash.'.tmp';
        }
    }
    
    /**
     * Merge 2 arrays
     * 
     * @param array $aArr1
     * @param array $aArr2
     * @return array merged array
     */
    protected function _checkAndMergeArrays($aArr1, $aArr2) {
        if(!is_array($aArr1)) {
            $aArr1 = array();
        }
        if(!is_array($aArr2)) {
            $aArr2 = array();
        }
        return array_merge($aArr1, $aArr2);
    }
    
    /**
     * 
     * @param type $aArr
     * @return mixed text as string or false
     */
    protected function _getFirstArrayValue($aArr) {
        $sText = "";
        foreach ($aArr as $key => $value) {
            if ($value != '') {
                $sText = $value;
                break;
            }
        }
        return (empty($sText))?false:$sText;
    }
    
    /**
     * Add new meta to meta-array
     * overwrite if exist
     * 
     * @param string $sName
     * @param string $sValue
     * @return void
     */
    protected function _addMeta($sName, $sValue) {
        $aTmp = array(
            'name'      =>  $sName,
            'content'   =>  $sValue
        );
        $iTmpKey = $this->_inMetaArray($sName, $this->_aCreatedMetaTags);
        if(false !== $iTmpKey) {
            $this->_aCreatedMetaTags[$iTmpKey]['content'] = $sValue;
        } else {
            array_push($this->_aCreatedMetaTags, $aTmp);
        }
    }
    
    /**
     * Search in meta-array for a name/content
     * returns the key if the needle is found
     * 
     * @param string $sNeedle
     * @param array $aHaystack
     * @param boolean $bStrict
     * @return mixed key_number in haystack or false if nothing was found
     */
    protected function _inMetaArray($sNeedle, $aHaystack, $bStrict = false) {
        foreach($aHaystack as $iKey=>$aValue) {
            if(in_array($sNeedle, $aValue)) return $iKey;
        }
        return false;
    }
}
?>