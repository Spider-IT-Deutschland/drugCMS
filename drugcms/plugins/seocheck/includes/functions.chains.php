<?php
/**
 * Project: 
 * drugCMS Content Management System
 * 
 * Description: 
 * Chains for SEO Check
 * 
 * Requirements: 
 * @con_php_req 5.2
 * 
 *
 * @package    drugCMS Backend plugins
 * @version    1.0.0
 * @author     René Mansveld
 * @copyright  Spider IT Deutschland <www.spider-it.de>
 * @license    http://www.drugcms.org/license/LICENSE.txt
 * @link       http://www.spider-it.de
 * @link       http://www.drugcms.org
 * @since      file available since drugCMS release 2.0.5
 * 
 * {@internal 
 *   created 2015-04-05
 *
 *   $Id$:
 * }}
 * 
 */

if(!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}

function seocheck_RegisterCustomTab ()
{
echo '<script>alert("Test");</script>';
    return array("con_seocheck");	
}

function seocheck_GetCustomTabProperties ($sIntName)
{
	if ($sIntName == "con_seocheck")
	{
		return array("con_seocheck", "con_edit", "");
	}	
}

function seocheck_ArticleListActions ($aActions)
{
	$aTmpActions["con_seocheck"] = "con_seocheck";
	
	return $aTmpActions + $aActions;
}

function seocheck_RenderArticleAction ($idcat, $idart, $idartlang, $actionkey)
{
	global $sess;
	
	if ($actionkey == "con_seocheck")
	{
 		return '<a title="'.i18n("SEO Check").'" alt="'.i18n("SEO Check").'" href="'.$sess->url('main.php?area=con_seocheck&action=con_edit&idart='.$idart.'&idartlang='.$idartlang.'&idcat='.$idcat.'&frame=4').'"><img src="plugins/content_allocation/images/call_contentallocation.gif"></a>';
 	
	} else {
		return "";	
	}
}
?>