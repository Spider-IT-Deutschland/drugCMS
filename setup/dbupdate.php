<?php
 /**
 * Project:
 * Contenido Content Management System
 *
 * Description:
 * Creates/Updates the database tables and fills them with entries (depending on 
 * selected options during setup process)
 *
 * @package    Contenido setup
 * @version    0.2.2
 * @author     unknown
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 *
 *   $Id$:
 */

if (!defined('CON_FRAMEWORK')) {
    define('CON_FRAMEWORK', true);
}
define('C_CONTENIDO_PATH', '../drugcms/');

include_once('lib/startup.php');


checkAndInclude(C_CONTENIDO_PATH . 'includes/functions.database.php');
checkAndInclude(C_CONTENIDO_PATH . 'classes/class.version.php');
checkAndInclude(C_CONTENIDO_PATH . 'classes/class.versionImport.php');

function removeDir($sDir, $leaveDir = false) {
    if (!$dh = @opendir($sDir)) {
        return;
    }
    while (($obj = readdir($dh)) !== false) {
        if (($obj == '.') || ($obj == '..')) {
            continue;
        }
        if (!@unlink($sDir . '/' . $obj)) {
            removeDir($sDir . '/' . $obj);
        }
    }
    closedir($dh);
    if (!$leaveDir){
        @rmdir($sDir);
    }
}

if ((!hasMySQLExtension()) && (!hasMySQLiExtension())) {
    die("Can't detect MySQLi or MySQL extension");
} else {
    $cfg['database_extension'] = $_SESSION['dbmode'];
}

checkAndInclude('../conlib/prepend.php');

$db = getSetupMySQLDBConnection(false);

if (checkMySQLDatabaseCreation($db, $_SESSION['dbname'])) {
    $db = getSetupMySQLDBConnection();
}

$currentstep = $_GET['step'];

if ($currentstep == 0) {
    $currentstep = 1;
}

if (($_SESSION["setuptype"] == 'setup') && (strlen($_SESSION["dbencoding"])) && ($currentstep == 1)) {
    $db->query('ALTER DATABASE `' . $_SESSION["dbname"] . '` CHARACTER SET ' . $_SESSION["dbencoding"]);
}

// Count DB Chunks
$file = fopen('data/tables.txt', 'r');
$step = 1;
while (($data = fgetcsv($file, 4000, ';')) !== false) {
    if ($count == 50) {
        $count = 1;
        $step++;
    }

    if ($currentstep == $step) {
        if ($data[7] == '1') {
            $drop = true;
        } else {
            $drop = false;
        }
        dbUpgradeTable($db, $_SESSION['dbprefix'].'_'.$data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], '', $drop);
        dbUpdateSequence($_SESSION['dbprefix'].'_sequence', $_SESSION['dbprefix'] . '_' . $data[1], $db);

        if ($db->errno != 0) {
            $_SESSION['install_failedupgradetable'] = true;
        }
    }

    $count++;
    $fullcount++;
}

$baseChunks = explode("\n", file_get_contents('data/base.txt'));

$clientChunksDe = explode("\n", file_get_contents('data/client_de.txt'));

$clientChunksEn = explode("\n", file_get_contents('data/client_en.txt'));

$clientChunksDeEn = explode("\n", file_get_contents('data/client_de_en.txt'));

$clientNoContentChunks = explode("\n", file_get_contents('data/client_no_content.txt'));

$moduleChunks = explode("\n", file_get_contents('data/standard.txt'));

$contentChunks = explode("\n", file_get_contents('data/examples.txt'));

$contentChunksDe = explode("\n", file_get_contents('data/examples_de.txt'));

$contentChunksEn = explode("\n", file_get_contents('data/examples_en.txt')); 

$contentChunksDeEn = explode("\n", file_get_contents('data/examples_de_en.txt')); 

$sysadminChunk = explode("\n", file_get_contents('data/sysadmin.txt'));

if ($_SESSION['setuptype'] == 'setup') {
    switch ($_SESSION['clientmode']) {
        case 'CLIENT':
            $fullChunks = array_merge($baseChunks, $sysadminChunk, $clientNoContentChunks);
            break;
        case 'CLIENTMODULES':
            $fullChunks = array_merge($baseChunks, $sysadminChunk, $clientNoContentChunks, $moduleChunks);
            break;
        case 'CLIENTEXAMPLES1': # English only
            $fullChunks = array_merge($baseChunks, $sysadminChunk, $clientChunksEn, $moduleChunks, $contentChunks, $contentChunksEn);
            break;
        case 'CLIENTEXAMPLES2': # German only
            $fullChunks = array_merge($baseChunks, $sysadminChunk, $clientChunksDe, $moduleChunks, $contentChunks, $contentChunksDe);
            break;
        case 'CLIENTEXAMPLES3': # German and English
            $fullChunks = array_merge($baseChunks, $sysadminChunk, $clientChunksDeEn, $moduleChunks, $contentChunks, $contentChunksDeEn);
            break;
        default:
            $fullChunks = array_merge($baseChunks, $sysadminChunk);
            break;
    }
} else {
    $fullChunks = $baseChunks;
}

if ($_SESSION['setuptype'] == 'upgrade') {
    $upgradeChunks = explode("\n", file_get_contents('data/upgrade.txt'));
    $fullChunks = array_merge($fullChunks, $upgradeChunks);
}


list($root_path, $root_http_path) = getSystemDirectories();

$totalsteps = ceil($fullcount/50) + count($fullChunks) + 1;
foreach ($fullChunks as $fullChunk) {
    $step++;
    if ($step == $currentstep) {
        $failedChunks = array();

        $replacements = array(
            '<!--{contenido_root}-->' => addslashes(str_replace('\\', '/', $root_path)),
            '<!--{contenido_web}-->' => addslashes(str_replace('\\', '/', $root_http_path))
        );

        injectSQL($db, $_SESSION['dbprefix'], 'data/' . $fullChunk, $replacements, $failedChunks);

        if (count($failedChunks) > 0) {
            if (@$fp = fopen('../drugcms/data/logs/setuplog.txt', 'a')) {
                foreach ($failedChunks as $failedChunk) {
                    fwrite($fp, sprintf("Setup was unable to execute SQL. MySQL-Error: %s, MySQL-Message: %s, SQL-Statements:\n%s", $failedChunk['errno'], $failedChunk['error'], $failedChunk['sql']) . "\n\n");
                }
                fclose($fp);
            }
            $_SESSION['install_failedchunks'] = true;
        }
    }
}

$percent = intval((100 / $totalsteps) * ($currentstep));

echo '<script type="text/javascript">parent.updateProgressbar(' . $percent . ');</script>';
if ($currentstep < $totalsteps) {
    printf('<script type="text/javascript">window.setTimeout("nextStep()", 10); function nextStep() { window.location.href="dbupdate.php?step=%s"; }</script>', $currentstep + 1);
} else {
    $sql = 'SHOW TABLES';
    $db->query($sql);

    // For import mod_history rows to versioning
    if ($_SESSION['setuptype'] == 'migration' || $_SESSION['setuptype'] == 'upgrade') {
        $cfgClient = array();
        rereadClients_Setup();

        $oVersion = new VersionImport($cfg, $cfgClient, $db, $client, $area, $frame);
        $oVersion->CreateHistoryVersion();
    }

    $tables = array();

    while ($db->next_record()) {
        $tables[] = $db->f(0);
    }

    foreach ($tables as $table) {
        dbUpdateSequence($_SESSION['dbprefix'].'_sequence', $table, $db);
    }

    updateContenidoVersion($db, $_SESSION['dbprefix'].'_system_prop', C_SETUP_VERSION);
    updateSystemProperties($db, $_SESSION['dbprefix'].'_system_prop');

    if (isset($_SESSION['sysadminpass']) && $_SESSION['sysadminpass'] != '') {
        updateSysadminPassword($db, $_SESSION['dbprefix'].'_phplib_auth_user_md5', 'sysadmin');
    }

    $sql = 'DELETE FROM %s';
    $db->query(sprintf($sql, $_SESSION['dbprefix'].'_code'));

    // As con_code has been emptied, force code creation (on update)
    $sql = "UPDATE %s SET createcode = 1";
    $db->query(sprintf($sql, $_SESSION['dbprefix'].'_cat_art'));

    if ($_SESSION['setuptype'] == 'migration') {
        $aClients = listClients($db, $_SESSION['dbprefix'].'_clients');

        foreach ($aClients as $iIdClient => $aInfo) {
            updateClientPath($db, $_SESSION['dbprefix'].'_clients', $iIdClient, str_replace('\\', '/', $_SESSION['frontendpath'][$iIdClient]), str_replace('\\', '/', $_SESSION['htmlpath'][$iIdClient]));
        }
    }

    $_SESSION['start_compatible'] = false;

    if ($_SESSION['setuptype'] == 'upgrade') {
        $sql = "SELECT is_start FROM %s WHERE is_start = 1";
        $db->query(sprintf($sql, $_SESSION['dbprefix'].'_cat_art'));

        if ($db->next_record()) {
            $_SESSION['start_compatible'] = true;
        }
    }

    // Update Keys
    $aNothing = array();

    injectSQL($db, $_SESSION['dbprefix'], 'data/indexes.sql', array(), $aNothing);

    if ($_SESSION['setuptype'] == 'upgrade') {
        /**
         * For upgrades from pre 2.1.0 we need to upgrade the plugins.
         * We can check this on the plugins' folders, because they don't have the plugin.xml file.
         * If so, we need to place the plugin.xml files there and create the _plugins DB entries.
         * And then, we should delete non-installed plugins, so that they can be installed by PIM.
         */
        if ((is_dir(str_replace('\\', '/', dirname(__FILE__)) . '/' . C_CONTENIDO_PATH . 'plugins/content_allocation/')) && (!is_file(str_replace('\\', '/', dirname(__FILE__)) . '/' . C_CONTENIDO_PATH . 'plugins/content_allocation/plugin.xml'))) {
            $sql = 'SELECT idnavs
                    FROM ' . $_SESSION['dbprefix'] . '_nav_sub
                    WHERE (location="content_allocation/xml/;navigation/extra/content_allocation/main")';
            $db->query($sql);
            if ($db->num_rows()) {
                # Plugin is installed, upgrade it
                copy(str_replace('\\', '/', dirname(__FILE__)) . '/data/upgrade/content_allocation.xml', str_replace('\\', '/', dirname(__FILE__)) . '/' . C_CONTENIDO_PATH . 'plugins/content_allocation/plugin.xml');
                $aDescription = array();
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_nav_sub', 'id' => 800);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_nav_sub', 'id' => 801);
                $id = $db->nextid($_SESSION['dbprefix'] . '_plugins');
                $sql = 'INSERT INTO ' . $_SESSION['dbprefix'] . '_plugins (idplugin, name, description, path, installed)
                        VALUES (' . $id . ', "content_allocation", "' . json_encode($aDescription) . '", "content_allocation", 1)';
                $db->query($sql);
            }
            else {
                # Plugin is not installed, remove it
                removeDir(str_replace('\\', '/', dirname(__FILE__)) . '/' . C_CONTENIDO_PATH . 'plugins/content_allocation/');
            }
        }
        if ((is_dir(str_replace('\\', '/', dirname(__FILE__)) . '/' . C_CONTENIDO_PATH . 'plugins/db_backup/')) && (!is_file(str_replace('\\', '/', dirname(__FILE__)) . '/' . C_CONTENIDO_PATH . 'plugins/db_backup/plugin.xml'))) {
            $sql = 'SELECT idnavs
                    FROM ' . $_SESSION['dbprefix'] . '_nav_sub
                    WHERE (location="navigation/administration/db_backup/main")';
            $db->query($sql);
            if ($db->num_rows()) {
                # Plugin is installed, upgrade it
                copy(str_replace('\\', '/', dirname(__FILE__)) . '/data/upgrade/db_backup.xml', str_replace('\\', '/', dirname(__FILE__)) . '/' . C_CONTENIDO_PATH . 'plugins/db_backup/plugin.xml');
                $aDescription = array();
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_area', 'id' => 900);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 901);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 902);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 903);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 904);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 901);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 902);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 903);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 904);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_nav_sub', 'id' => 900);
                $id = $db->nextid($_SESSION['dbprefix'] . '_plugins');
                $sql = 'INSERT INTO ' . $_SESSION['dbprefix'] . '_plugins (idplugin, name, description, path, installed)
                        VALUES (' . $id . ', "db_backup", "' . json_encode($aDescription) . '", "db_backup", 1)';
                $db->query($sql);
            }
            else {
                # Plugin is not installed, remove it
                removeDir(str_replace('\\', '/', dirname(__FILE__)) . '/' . C_CONTENIDO_PATH . 'plugins/db_backup/');
            }
        }
        if ((is_dir(str_replace('\\', '/', dirname(__FILE__)) . '/' . C_CONTENIDO_PATH . 'plugins/linkchecker/')) && (!is_file(str_replace('\\', '/', dirname(__FILE__)) . '/' . C_CONTENIDO_PATH . 'plugins/linkchecker/plugin.xml'))) {
            copy(str_replace('\\', '/', dirname(__FILE__)) . '/data/upgrade/linkchecker.xml', str_replace('\\', '/', dirname(__FILE__)) . '/' . C_CONTENIDO_PATH . 'plugins/linkchecker/plugin.xml');
            $aDescription = array();
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_area', 'id' => 500);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_area', 'id' => 501);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 500);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 501);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 500);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 501);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 502);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 503);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 500);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 501);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 503);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_nav_sub', 'id' => 500);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_nav_sub', 'id' => 502);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_nav_sub', 'id' => 503);
            $id = $db->nextid($_SESSION['dbprefix'] . '_plugins');
            $sql = 'INSERT INTO ' . $_SESSION['dbprefix'] . '_plugins (idplugin, name, description, path, installed)
                    VALUES (' . $id . ', "linkchecker", "' . json_encode($aDescription) . '", "linkchecker", 1)';
            $db->query($sql);
        }
        if ((is_dir(str_replace('\\', '/', dirname(__FILE__)) . '/' . C_CONTENIDO_PATH . 'plugins/mod_rewrite/')) && (!is_file(str_replace('\\', '/', dirname(__FILE__)) . '/' . C_CONTENIDO_PATH . 'plugins/mod_rewrite/plugin.xml'))) {
            $sql = 'SELECT idnavs
                    FROM ' . $_SESSION['dbprefix'] . '_nav_sub
                    WHERE (location="mod_rewrite/xml/;navigation/content/mod_rewrite/main")';
            $db->query($sql);
            if ($db->num_rows()) {
                # Plugin is installed, upgrade it
                copy(str_replace('\\', '/', dirname(__FILE__)) . '/data/upgrade/mod_rewrite.xml', str_replace('\\', '/', dirname(__FILE__)) . '/' . C_CONTENIDO_PATH . 'plugins/mod_rewrite/plugin.xml');
                $aDescription = array();
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_area', 'id' => 700);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_area', 'id' => 701);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_area', 'id' => 702);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 700);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 701);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 702);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 700);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 701);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 702);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 703);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 700);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 701);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 702);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 703);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_nav_sub', 'id' => 700);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_nav_sub', 'id' => 701);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_nav_sub', 'id' => 702);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_nav_sub', 'id' => 703);
                $id = $db->nextid($_SESSION['dbprefix'] . '_plugins');
                $sql = 'INSERT INTO ' . $_SESSION['dbprefix'] . '_plugins (idplugin, name, description, path, installed)
                        VALUES (' . $id . ', "mod_rewrite", "' . json_encode($aDescription) . '", "mod_rewrite", 1)';
                $db->query($sql);
            }
            else {
                # Plugin is not installed, remove it
                removeDir(str_replace('\\', '/', dirname(__FILE__)) . '/' . C_CONTENIDO_PATH . 'plugins/mod_rewrite/');
            }
        }
        if ((is_dir(str_replace('\\', '/', dirname(__FILE__)) . '/' . C_CONTENIDO_PATH . 'plugins/newsletter/')) && (!is_file(str_replace('\\', '/', dirname(__FILE__)) . '/' . C_CONTENIDO_PATH . 'plugins/newsletter/plugin.xml'))) {
            $sql = 'SELECT idnavs
                    FROM ' . $_SESSION['dbprefix'] . '_nav_sub
                    WHERE (location="navigation/extra/newsletter")';
            $db->query($sql);
            if ($db->num_rows()) {
                # Plugin is installed, upgrade it
                copy(str_replace('\\', '/', dirname(__FILE__)) . '/data/upgrade/newsletter.xml', str_replace('\\', '/', dirname(__FILE__)) . '/' . C_CONTENIDO_PATH . 'plugins/newsletter/plugin.xml');
                $aDescription = array();
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_area', 'id' => 16);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_area', 'id' => 17);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_area', 'id' => 26);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_area', 'id' => 27);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_area', 'id' => 50);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_area', 'id' => 86);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_area', 'id' => 90);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_area', 'id' => 91);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 337);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 338);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 339);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 341);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 342);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 343);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 422);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 423);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 424);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 425);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 427);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 428);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 434);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 435);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 436);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 437);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 438);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 439);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 440);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 441);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 442);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 81);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 100);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 101);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 103);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 104);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 107);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 114);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 189);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 190);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 191);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 201);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 202);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 203);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 204);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 205);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 83);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 102);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 103);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 105);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 106);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 109);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 118);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 196);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 197);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 198);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 209);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 210);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 211);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 212);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 213);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_nav_sub', 'id' => 38);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_nav_sub', 'id' => 81);
                $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_nav_sub', 'id' => 610);
                $id = $db->nextid($_SESSION['dbprefix'] . '_plugins');
                $sql = 'INSERT INTO ' . $_SESSION['dbprefix'] . '_plugins (idplugin, name, description, path, installed)
                        VALUES (' . $id . ', "newsletter", "' . json_encode($aDescription) . '", "newsletter", 1)';
                $db->query($sql);
            }
            else {
                # Plugin is not installed, remove it
                removeDir(str_replace('\\', '/', dirname(__FILE__)) . '/' . C_CONTENIDO_PATH . 'plugins/newsletter/');
            }
        }
        if ((is_dir(str_replace('\\', '/', dirname(__FILE__)) . '/' . C_CONTENIDO_PATH . 'plugins/seocheck/')) && (!is_file(str_replace('\\', '/', dirname(__FILE__)) . '/' . C_CONTENIDO_PATH . 'plugins/seocheck/plugin.xml'))) {
            copy(str_replace('\\', '/', dirname(__FILE__)) . '/data/upgrade/seocheck.xml', str_replace('\\', '/', dirname(__FILE__)) . '/' . C_CONTENIDO_PATH . 'plugins/seocheck/plugin.xml');
            $aDescription = array();
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_area', 'id' => 1400);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 1400);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 1400);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_nav_sub', 'id' => 1400);
            $id = $db->nextid($_SESSION['dbprefix'] . '_plugins');
            $sql = 'INSERT INTO ' . $_SESSION['dbprefix'] . '_plugins (idplugin, name, description, path, installed)
                    VALUES (' . $id . ', "seocheck", "' . json_encode($aDescription) . '", "seocheck", 1)';
            $db->query($sql);
        }
        if ((is_dir(str_replace('\\', '/', dirname(__FILE__)) . '/' . C_CONTENIDO_PATH . 'plugins/workflow/')) && (!is_file(str_replace('\\', '/', dirname(__FILE__)) . '/' . C_CONTENIDO_PATH . 'plugins/workflow/plugin.xml'))) {
            copy(str_replace('\\', '/', dirname(__FILE__)) . '/data/upgrade/workflow.xml', str_replace('\\', '/', dirname(__FILE__)) . '/' . C_CONTENIDO_PATH . 'plugins/workflow/plugin.xml');
            $aDescription = array();
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_area', 'id' => 600);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_area', 'id' => 601);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_area', 'id' => 602);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_area', 'id' => 603);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_area', 'id' => 604);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 600);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 601);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 602);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 603);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 604);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 605);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 606);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 607);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 608);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 609);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 610);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 611);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 612);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 613);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 614);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 615);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 616);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 617);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_actions', 'id' => 618);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 600);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 601);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 602);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 603);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 604);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_files', 'id' => 605);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 600);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 601);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 602);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 603);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 604);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 605);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_frame_files', 'id' => 606);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_nav_sub', 'id' => 600);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_nav_sub', 'id' => 601);
            $aDescription[] = array('table' => $_SESSION['dbprefix'] . '_nav_sub', 'id' => 602);
            $id = $db->nextid($_SESSION['dbprefix'] . '_plugins');
            $sql = 'INSERT INTO ' . $_SESSION['dbprefix'] . '_plugins (idplugin, name, description, path, installed)
                    VALUES (' . $id . ', "workflow", "' . json_encode($aDescription) . '", "workflow", 1)';
            $db->query($sql);
        }
    }

    printf('<script type="text/javascript">parent.document.getElementById("installing").style.visibility="hidden";parent.document.getElementById("installingdone").style.visibility="visible";</script>');
    printf('<script type="text/javascript">parent.document.getElementById("next").style.display="block"; window.setTimeout("nextStep()", 10); function nextStep() { window.location.href="makeconfig.php"; }</script>');
}
?>