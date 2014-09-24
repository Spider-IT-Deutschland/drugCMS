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

// Count DB Chunks (plugins)
$file = fopen('data/tables_pi.txt', 'r');
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

$pluginChunks = array();

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

if ($_SESSION['plugin_newsletter'] == 'true') {
    $newsletter = explode("\n", file_get_contents('data/plugin_newsletter.txt'));
    $pluginChunks = array_merge($pluginChunks, $newsletter);
}

if ($_SESSION['plugin_content_allocation'] == 'true') {
    $content_allocation = explode("\n", file_get_contents('data/plugin_content_allocation.txt'));
    $pluginChunks = array_merge($pluginChunks, $content_allocation);
}

if ($_SESSION['plugin_mod_rewrite'] == 'true') {
    $mod_rewrite = explode("\n", file_get_contents('data/plugin_mod_rewrite.txt'));
    $pluginChunks = array_merge($pluginChunks, $mod_rewrite);
}

if ($_SESSION['plugin_db_backup'] == 'true') {
    $db_backup = explode("\n", file_get_contents('data/plugin_db_backup.txt'));
    $pluginChunks = array_merge($pluginChunks, $db_backup);
}

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

$fullChunks = array_merge($fullChunks, $pluginChunks);


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
        if ($table == $_SESSION['dbprefix'] . '_mod') {
            $db->query('SELECT nextid FROM ' . $_SESSION['dbprefix'] . '_sequence WHERE (seq_name="' . $table . '")');
            $db->next_record();
            $last_mod = $db->f('nextid');
            if ($last_mod < 2) {
                $db->query('UPDATE ' . $_SESSION['dbprefix'] . '_sequence SET nextid = 2 WHERE (seq_name="' . $table . '")');
            }
        }
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

    printf('<script type="text/javascript">parent.document.getElementById("installing").style.visibility="hidden";parent.document.getElementById("installingdone").style.visibility="visible";</script>');
    printf('<script type="text/javascript">parent.document.getElementById("next").style.display="block"; window.setTimeout("nextStep()", 10); function nextStep() { window.location.href="makeconfig.php"; }</script>');
}
?>