<?php
/**
 * Project: 
 * drugCMS
 * 
 * Description: 
 * Config file for the plugin backup
 * 
 * Requirements: 
 * @con_php_req 5.2
 * 
 *
 * @package    drugCMS Backend plugins
 * @version    1.0
 * @author     René Mansveld
 * @copyright  Spider IT Deutschland
 * @license    GPL v.2
 * @link       http://www.drugcms.org/
 * @link       http://www.spider-it.de/
 * @since      file available since contenido release 4.8.13
 * 
 * {@internal 
 *   created 2013-08-26
 *
 *   $Id$:
 * }}
 * 
 */

if(!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

$plugin_name = "db_backup";

// Die folgenden Einträge bitte nicht ändern!
$cfg['plugins'][$plugin_name]           = $cfg['path']['contenido'] . "plugins/" . $plugin_name . "/";
$cfg['plugins'][$plugin_name . '_path'] = $cfg['path']['contenido_fullhtml'] . "plugins/" . $plugin_name . "/";

// Grundkonfiguration des Plugin
$backup_path = $cfg['path']['contenido'] . 'data/backup/'; // Backup Ordner, bitte auf der gleichen Ebene wie "/contenido" anlegen und mit den mit den Rechten 757 versehen.
$number_of_files = '200'; // sind mehr als x Dateien im Ordner, beginnt die automatische Löschung

// Templates
$cfg[$plugin_name]['templates']['navi']         = $cfg['plugins'][$plugin_name] . "templates/standard/navi.html";
$cfg[$plugin_name]['templates']['config']       = $cfg['plugins'][$plugin_name] . "templates/standard/config.html";
$cfg[$plugin_name]['templates']['backup']       = $cfg['plugins'][$plugin_name] . "templates/standard/backup.html";
$cfg[$plugin_name]['templates']['ajax_backup']  = $cfg['plugins'][$plugin_name] . "templates/standard/ajax_backup.html";
$cfg[$plugin_name]['templates']['restore']      = $cfg['plugins'][$plugin_name] . "templates/standard/restore.html";
$cfg[$plugin_name]['templates']['ajax_restore'] = $cfg['plugins'][$plugin_name] . "templates/standard/ajax_restore.html";

// Prüfen, ob Backupverzeichnis mit den korrekten Berechtigungen vorhanden. Falls nein, im späteren Verlauf dessen Anlage einfordern
if (!is_dir($backup_path)) {
    $pathfinder = false;
} else {
    $pathfinder = true;
    
    // Ordnerrechte überprüfen
    if ($fp = @fopen($backup_path . 'test.txt', 'w')) {
        fclose($fp);
        @unlink($backup_path . 'test.txt');
        $permissionfinder = true;
    } else {
        $permissionfinder = false;
    }
}
?>