<?php
/**
 * Project:
 * Contenido Content Management System
 *
 * Description:
 * Recursive loop over given category for building a sitemap navigation
 *
 * @package    Frontend
 * @subpackage Navigation
 * @version    1.0.0
 * @author     Rudi Bieller
 * @copyright  four for business AG <www.4fb.de>
 *
 *   $Id$
 */
class Contenido_Sitemap_Util {
    /**
     * Recursive Loop over all (sub)categories.
     * Each level will be assigned a css class sitemapStandardLevel_x
     *
     * @param Contenido_Category $oCategory
     * @param Contenido_FrontendNavigation $oFrontendNavigation
     * @param Template $oTpl
     * @param string $sUrlStyle
     * @param array $aCfg
     * @param int $iLang
     * @param array $aDepthInfo Info on level depth / where to stop. Format: array(iCurrentLoopCount, iMaxLoopCount)
     * @return void
     */
    public static function loopCats(Contenido_Category $oCategory, Contenido_FrontendNavigation $oFrontendNavigation, Template $oTpl, $sUrlStyle, array $aCfg, $iLang, array $aDepthInfo = array()) {
    	$aDepthInfo[0] = isset($aDepthInfo[0]) ? $aDepthInfo[0] + 1 : 1;
    	$aDepthInfo[1] = isset($aDepthInfo[1]) ? $aDepthInfo[1] : 1;
        // display current item
    	$iItemLevel = $oFrontendNavigation->getLevel($oCategory->getIdCat());
    	// this is just for sample client - modify to your needs!
    	if (($aCfg['url_builder']['name'] == 'front_content') || ($aCfg['url_builder']['name'] == 'MR')) {
    	    $aParams = array('lang' => $iLang, 'idcat' => $oCategory->getIdCat());
    	} else {
        	$aParams = array('a' => $oCategory->getIdCat(), 
        					'idcat' => $oCategory->getIdCat(), // needed to build category path
        					'lang' => $iLang, // needed to build category path
        					'level' => 1); // needed to build category path
    	}
    	// fill template with values
    	$oTpl->set('d', 'name', $oCategory->getCategoryLanguage()->getName());
    	$oTpl->set('d', 'css_level', $iItemLevel);
    	try {
    	   $oTpl->set('d', 'url', Contenido_Url::getInstance()->build($aParams));
    	} catch (InvalidArgumentException $e) {
    	    $oTpl->set('d', 'url', '#');
    	}
    	$oTpl->next();
    	// continue until max level depth
    	if ($aDepthInfo[1] > $aDepthInfo[0]) {
        	// check if current item has sub-items
    		$oSubCategories = $oFrontendNavigation->getSubCategories($oCategory->getIdCat());
        	if ($oSubCategories->count() > 0) {
        		foreach ($oSubCategories as $oSubCategory) {
        			self::loopCats($oSubCategory, $oFrontendNavigation, $oTpl, $sUrlStyle, $aCfg, $iLang, $aDepthInfo);
        		}
        	}
    	}
    }
}
?>