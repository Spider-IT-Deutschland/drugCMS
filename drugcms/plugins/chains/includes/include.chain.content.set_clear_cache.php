<?php
/**
 * Project:
 * Contenido Content Management System
 *
 * Description:
 * Enables regeneration of con_cache for specific article when article content changes.
 * To enable this chain function you need to set a system-, client- or userproperty
 * 
 * save_article | clear_cache | [true|false]
 * 
 * so you may set it to true for only one power user or something like that.
 * 
 * 2011-08-09
 * Now you can add additional articles to set its cache-flag so they will be updated with next call.
 * Therefore we have added a new property where you can add a comma-separated list of articles which
 * have to be updated.
 * 
 * save_article | clear_cache_add_idart | [comma-separated list of idart]
 * 
 * Installation:
 * Install this chain by putting this file into contenido/plugins/chains/includes and
 * edit/create a 'config.local.php' file in folder contenido/includes
 * with the following code:
 * 
 * # add custom chains
 * cInclude("plugins", "chains/includes/include.chain.content.set_clear_cache.php");
 * $_cecRegistry->addChainFunction("Contenido.Content.SaveContentEntry", "cecContentSetClearCache");
 * 
 *
 * @package    Contenido Backend
 * @subpackage Contenido Chain
 * @version    $Id$
 * @since      2011-07-14
 * @author     Ortwin Pinke
 * @copyright  DCEonline <www.dceonline.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.contenido.org
 * @link       http://www.ortwinpinke.de
 * @link       http://forum.contenido.org/viewtopic.php?f=66&t=31432
 *
 */

if(!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}

/**
 *
 * @param int $idartlang
 * @param int $type
 * @param int $typeid
 * @param string $value
 * @return bool 
 */
function cecContentSetClearCache($idartlang, $type, $typeid, $value) {
    
    $aArticle2Clear = array($idartlang);
    
    $bClearCache = getEffectiveSetting('save_article', 'clear_cache', false);
    $sAdditionalArticles = getEffectiveSetting('save_article', 'clear_cache_add_idart', null);
    $iLimitAdditionalArticles = (int) getEffectiveSetting('save_article', 'clear_cache_limit_add_idart', 10);
    $iLimitAdditionalArticles = ($iLimitAdditionalArticles < 10)?10:$iLimitAdditionalArticles;
    
    if(!is_null($sAdditionalArticles)) {
        $aAdditionalArticles = array_map('trim',explode(",", $sAdditionalArticles, $iLimitAdditionalArticles +1));
        $iCountElements = count($aAdditionalArticles);        
        if($iCountElements > 0) {
            if($iCountElements > 1 && $iCountElements > $iLimitAdditionalArticles) {
                array_pop ($aAdditionalArticles);
            }
            array_splice($aArticle2Clear, count($aArticle2Clear), 0, $aAdditionalArticles);
        }
    }
    
    if($bClearCache) {
        cInclude("classes", "contenido/class.articlelanguage.php");
        cInclude("classes", "contenido/class.categoryarticle.php");
        
        foreach($aArticle2Clear as $iIdArt) {
            $oArtLang = new cApiArticleLanguage();
            if($oArtLang->loadByPrimaryKey($iIdArt)) {
                $oArtCat = new cApiCategoryArticle();
                if($oArtCat->loadBy('idart', $oArtLang->get('idart'))) {
                    $bCreateCode = !$oArtCat->get('createcode');
                    if($bCreateCode) {
                        $oArtCat->set('createcode', 1);
                        $oArtCat->store();
                    }
                }
                unset($oArtCat);
            }
            unset($oArtLang);
        }       
    }
    return $value;
}
?>