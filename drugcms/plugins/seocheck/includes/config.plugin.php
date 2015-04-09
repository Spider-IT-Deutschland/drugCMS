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

# Check if plugin is installed
if (!is_object($db)) {
    $db = new DB_Contenido();
}
$sql = 'SELECT idarea
        FROM ' . $cfg['tab']['area'] . '
        WHERE (name="con_seocheck")';
$db->query($sql);
if (!$db->next_record()) {
    plugin_include('seocheck', 'includes/include.functions.php');
    seocheck_installPlugin($db, $cfg);
}

global $_cecRegistry;

plugin_include('seocheck', 'includes/functions.chains.php');

$_cecRegistry->addChainFunction("Contenido.Article.RegisterCustomTab", "seocheck_RegisterCustomTab");
$_cecRegistry->addChainFunction("Contenido.Article.GetCustomTabProperties", "seocheck_GetCustomTabProperties");
?>