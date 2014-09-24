<?php
/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * Job to set frontendusers active / inactive depending on the date entered in BE
 * 
 * @package    Backend
 * @subpackage Cronjobs
 * @version    $Rev$
 * @author     Rudi Bieller
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 *
 *   $Id$:
 */

if (!defined("CON_FRAMEWORK")) {
    define("CON_FRAMEWORK", true);
}

// Contenido startup process
include_once ('../includes/startup.php');

include_once ($cfg['path']['contenido'].$cfg["path"]["classes"] . 'class.user.php');
include_once ($cfg['path']['contenido'].$cfg["path"]["classes"] . 'class.xml.php');
include_once ($cfg['path']['contenido'].$cfg["path"]["classes"] . 'class.navigation.php');
include_once ($cfg['path']['contenido'].$cfg["path"]["classes"] . 'template/class.template.php');
include_once ($cfg['path']['contenido'].$cfg["path"]["classes"] . 'class.backend.php');
include_once ($cfg['path']['contenido'].$cfg["path"]["classes"] . 'class.table.php');
include_once ($cfg['path']['contenido'].$cfg["path"]["classes"] . 'class.notification.php');
include_once ($cfg['path']['contenido'].$cfg["path"]["classes"] . 'class.area.php');
include_once ($cfg['path']['contenido'].$cfg["path"]["classes"] . 'class.layout.php');
include_once ($cfg['path']['contenido'].$cfg["path"]["classes"] . 'class.client.php');
include_once ($cfg['path']['contenido'].$cfg["path"]["classes"] . 'class.cat.php');
include_once ($cfg['path']['contenido'].$cfg["path"]["classes"] . 'class.treeitem.php');
include_once ($cfg['path']['contenido'].$cfg["path"]["classes"] . 'class.inuse.php');
include_once ($cfg['path']['contenido'].$cfg["path"]["includes"] . 'cfg_language_de.inc.php');
include_once ($cfg['path']['contenido'].$cfg["path"]["includes"] . 'functions.stat.php');

require_once($cfg['path']['contenido'].$cfg["path"]["includes"] . 'pseudo-cron.inc.php');

if (!isRunningFromWeb() || function_exists("runJob") || $area == "cronjobs")
{
	$db = new DB_Contenido();

	$sSql = "UPDATE " . $cfg['tab']['frontendusers'] . "
				SET active = 0
				WHERE
					(valid_to < NOW() AND valid_to != '0000-00-00 00:00:00')
					OR
					(valid_from > NOW() AND valid_from != '0000-00-00 00:00:00') ";
	//echo $sSql;
	$db->query($sSql);

}
?>
