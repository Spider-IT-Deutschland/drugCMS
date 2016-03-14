<?php
/**
 * Project:
 * drugCMS Content Management System
 *
 * Description:
 * Plugin Manager
 *
 * Requirements:
 * @con_php_req 5.4
 *
 *
 * @package    drugCMS Backend plugins
 * @version    1.0
 * @author     René Mansveld
 * @copyright  Spider IT Deutschland
 * @license    http://www.drugcms.org/license/LICENCE.txt
 * @link       http://www.drugcms.de
 * @link       http://www.spider-it.de
 * @since      file available since drugCMS release 2.1.0
 *
 * {@internal
 *   created  2015-11-23
 * }}
 *
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal Call');
}

$plugin_name = 'pim';

# Set language specific names for rights area
$lngAct[$plugin_name]['plugins']        = i18n("Plugins", $plugin_name);
$lngAct[$plugin_name]['repositories']   = i18n("Repositories", $plugin_name);
$lngAct[$plugin_name]['settings']       = i18n("Settings", $plugin_name);

unset($plugin_name);
?>