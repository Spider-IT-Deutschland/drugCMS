<?php
if (!defined("CON_FRAMEWORK")) {
    define("CON_FRAMEWORK", true);
}

# includes
include_once(dirname(__FILE__) . '/../../../includes/startup.php');
cInclude('plugins', 'db_backup/includes/config.plugin.php');

# Parameters
$fStart     = ((isset($_GET['start'])) ? floatval($_GET['start']) : microtime(true));
$sFile      = $_GET['file'];
$iLine      = intval($_GET['line']);
$sWait      = $_GET['wait'];
$sProcessed = $_GET['processed'];
$contenido  = $_GET['contenido'];

# Defines
$plugin_name = 'db_backup';

# Execution
$ret = restore_tables($cfg['path']['contenido'] . 'data/backup/' . $sFile, $iLine);
if (is_int($ret)) {
    $oTpl = new Template();
    $oTpl->set('s', 'PATH', $cfg['path']['contenido_fullhtml']);
    $oTpl->set('s', 'LABEL_WAIT', $sWait . '&hellip;');
    $oTpl->set('s', 'MESSAGE', $sProcessed . ': ' . $ret);
    $oTpl->set('s', 'SCRIPT', 'document.location.href="' . $cfg['path']['contenido_fullhtml'] . 'plugins/db_backup/includes/ajax.restore.php?mode=1&action=2&file=' . $sFile . '&start=' . $fStart . '&line=' . $ret . '&wait=' . $sWait . '&processed=' . $sProcessed . '&contenido=' . $contenido . '";');
    $oTpl->generate($cfg[$plugin_name]['templates']['ajax_restore']);
} else {
    echo '<script type="text/javascript">document.location.href="' . $cfg['path']['contenido_fullhtml'] . 'main.php?area=' . $plugin_name . '&frame=4&mode=1&action=2&file=' . $sFile . '&start=' . $fStart . '&result=' . (($ret) ? 'true' : 'false') . '&contenido=' . $contenido . '";</script>';
}


# functions
function restore_tables($file, $first_line = 0) {
    global $cfg;
    
    $iStart = time();
    $iMET = 2;#(intval(ini_get('max_execution_time')) - 10); # We need some time for other tasks
    $db = new DB_Contenido();
    $current_line = 0;
    
    # Open the backup file
    $gz = (substr($file, -3) == '.gz');
    $len = filesize($file);
    if ($gz) {
        if (!$handle = gzopen($file, 'r')) {
            return false;
        }
    } else {
        if (!$handle = fopen($file, 'r')) {
            return false;
        }
    }
    
    # Process the file line by line
    while (true) {
        while ($current_line < $first_line) {
            if ($gz) {
                $line = trim(gzgets($handle, $len));
            } else {
                $line = trim(fgets($handle, $len));
            }
            $current_line ++;
        }
        if ($gz) {
            $line = trim(gzgets($handle, $len));
            if (gzeof($handle)) {
                return true;
            }
        } else {
            $line = trim(fgets($handle, $len));
            if (feof($handle)) {
                return true;
            }
        }
        $current_line ++;
        if ((strlen($line)) && (substr($line, 0, 2) != '--')) {
/*
            if ((substr(trim($line), 0, 10) == 'DROP TABLE') && ($current_line != ($first_line + 1))) {
                # New table definition
                $current_line --;
                return (int) $current_line;
            }
*/
            while (substr(trim($line), -1) != ';') {
                if ($gz) {
                    $line .= ' ' . trim(gzgets($handle, $len));
                } else {
                    $line .= ' ' . trim(fgets($handle, $len));
                }
                $current_line ++;
            }
            if (!$db->query($line)) {
                echo 'Error ' . $db->getErrorNumber() . ': ' . $db->getErrorMessage() . '<br />';
                return false;
            }
        }
        # Time management
        if ((time() - $iStart) >= $iMET) {
            return (int) $current_line;
        }
    }
    return true;
}
?>