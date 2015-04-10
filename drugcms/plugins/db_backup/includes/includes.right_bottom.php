<?php
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

$plugin_name = "db_backup";
 
if ((!$perm->have_perm_area_action($plugin_name, $plugin_name)) && (!$cronjob)) {
    exit;
}

plugin_include($plugin_name, 'includes/config.plugin.php');

$oTpl = new Template();

# Check for the requirements
if ((!$pathfinder) || (!$permissionfinder)) {
    $oTpl->set('s', 'HEADLINE', i18n("Configuration", $plugin_name));
    $oTpl->set('s', 'INTRO', i18n("Backup requires a folder named 'backup' in /drugcms/data/ with write permission for the web server user.", $plugin_name));
    $oTpl->set('s', 'LABEL_SETTINGS', i18n("Current settings", $plugin_name));
    $oTpl->set('s', 'LABEL_PATHFINDER', i18n("Backup folder", $plugin_name));
    $oTpl->set('s', 'PATHFINDER', (($pathfinder) ? i18n("The backup folder exists", $plugin_name) : i18n("The backup folder doesn't exist, please create it", $plugin_name)));
    $oTpl->set('s', 'LABEL_CHMOD', i18n("Folder permissions", $plugin_name));
    $oTpl->set('s', 'CHMOD', (($permissionfinder) ? i18n("Write permission granted", $plugin_name) : i18n("Write permission denied", $plugin_name)));
    $oTpl->generate($cfg[$plugin_name]['templates']['config']);
} else {
    switch (intval($_GET['action'])) {
        case 1:
            $fStart = ((isset($_GET['start'])) ? floatval($_GET['start']) : microtime(true));
            $sFile = ((isset($_GET['file'])) ? $_GET['file'] : $contenido_database . '_' . date("Y\-m\-d\_H\-i\-s", time()) . '.sql');
            $ret = backup_tables($backup_path . $sFile, $contenido_host, $contenido_user, $contenido_password, $contenido_database, $_GET['table'], intval($_GET['row']));
            if (is_array($ret)) {
                $oTpl->set('s', 'PATH', $cfg['path']['contenido_fullhtml']);
                $oTpl->set('s', 'LABEL_WAIT', i18n("Please wait", $plugin_name) . '&hellip;');
                $oTpl->set('s', 'MESSAGE', i18n("Current table", $plugin_name) . ': ' . $ret['table'] . '<br />' . i18n("Current row", $plugin_name) . ': ' . $ret['row']);
                $oTpl->set('s', 'SCRIPT', 'document.location.href="' . $sess->url('main.php?area=' . $plugin_name . '&frame=4&mode=0&action=1&file=' . $sFile . '&start=' . $fStart . '&table=' . $ret['table'] . '&row=' . $ret['row']) . '";');
                $oTpl->generate($cfg[$plugin_name]['templates']['ajax_backup']);
                die();
            } elseif ($ret === true) {
                $sMsg = '<div style="margin-bottom: 2px; padding: 2px 0px 4px 0px; font-weight: bold; text-align: center; color: #FFF; background-color: #62BC47; border: 2px solid #080;">' . i18n("Backup created successfully", $plugin_name) . '</div>';
                $fExecTime = (microtime(true) - $fStart);
                $sMsg .= '<div style="margin-bottom: 10px;">' . sprintf(i18n("It took %s seconds to backup the database", $plugin_name), strval($fExecTime)) . '</div>';
            } else {
                $sMsg = '<div style="margin: 0px; padding: 2px 0px 4px 0px; font-weight: bold; text-align: center; color: #FFF; background-color: #F00; border: 2px solid #D00;">' . i18n("An error occured while creating the backup", $plugin_name) . '</div>';
            }
            break;
        case 2:
            $fStart = ((isset($_GET['start'])) ? floatval($_GET['start']) : microtime(true));
            $sFile = $_GET['file'];
            if (isset($_GET['result'])) {
                $ret = ($_GET['result'] == 'true');
            } else {
                $oTpl->set('s', 'PATH', $cfg['path']['contenido_fullhtml']);
                $oTpl->set('s', 'LABEL_WAIT', i18n("Please wait", $plugin_name) . '&hellip;');
                $oTpl->set('s', 'MESSAGE', i18n("Processed lines", $plugin_name) . ': 0');
                $oTpl->set('s', 'SCRIPT', 'document.location.href="' . $cfg['path']['contenido_fullhtml'] . 'plugins/db_backup/includes/ajax.restore.php?mode=1&action=2&file=' . $sFile . '&start=' . $fStart . '&line=0&wait=' . i18n("Please wait", $plugin_name) . '&processed=' . i18n("Processed lines", $plugin_name) . '&contenido=' . $contenido . '";');
                $oTpl->generate($cfg[$plugin_name]['templates']['ajax_restore']);
                die();
            }
            if ($ret === true) {
                $sMsg = '<div style="margin-bottom: 2px; padding: 2px 0px 4px 0px; font-weight: bold; text-align: center; color: #FFF; background-color: #62BC47; border: 2px solid #080;">' . i18n("Backup restored successfully", $plugin_name) . '</div>';
                $fExecTime = (microtime(true) - $fStart);
                $sMsg .= '<div style="margin-bottom: 10px;">' . sprintf(i18n("It took %s seconds to restore the database", $plugin_name), strval($fExecTime)) . '</div>';
            } else {
                $sMsg = '<div style="margin-bottom: 10px; padding: 2px 0px 4px 0px; font-weight: bold; text-align: center; color: #FFF; background-color: #F00; border: 2px solid #D00;">' . i18n("An error occured while restoring the backup", $plugin_name) . '</div>';
            }
            break;      
        case 3:
            $sFile = $_GET['file'];
            if (is_file($backup_path . $sFile)) {
                if (@unlink($backup_path . $sFile)) {
                    $sMsg = '<div style="margin: 0px 0px 10px 0px; padding: 2px 0px 4px 0px; font-weight: bold; text-align: center; color: #FFF; background-color: #62BC47; border: 2px solid #080;">' . i18n("Backup deleted successfully", $plugin_name) . '</div>';
                } else {
                    $sMsg = '<div style="margin: 0px 0px 10px 0px; padding: 2px 0px 4px 0px; font-weight: bold; text-align: center; color: #FFF; background-color: #F00; border: 2px solid #D00;">' . i18n("An error occured while deleting the backup", $plugin_name) . '</div>';
                }
            } else {
                $sMsg = '<div style="margin: 0px 0px 10px 0px; padding: 2px 0px 4px 0px; font-weight: bold; text-align: center; color: #FFF; background-color: #62BC47; border: 2px solid #080;">' . i18n("The backup doesn't exist, maybe it was deleted already", $plugin_name) . '</div>';
            }
            break;
    }
    $oTpl->set('s', 'MESSAGE', $sMsg);
    switch (intval($_GET['mode'])) {
        case 0:
            $oTpl->set('s', 'LABEL_BACKUP', i18n("Backup", $plugin_name));
            $oTpl->set('s', 'LINK_START_BACKUP', $sess->url('main.php?area=' . $plugin_name . '&amp;frame=4&amp;mode=0&amp;action=1'));
            $oTpl->set('s', 'LABEL_START_BACKUP', i18n("Start a new backup", $plugin_name));
            $oTpl->set('s', 'LABEL_PREVIOUS_BACKUPS', i18n("Created backups", $plugin_name));
            $aFiles = getFilesInDirectory($backup_path, '*.sql*', SORT_DESC);
            for ($i = 0, $n = count($aFiles); $i < $n; $i ++) {
                $oTpl->set('d', 'NO', ($i + 1));
                $oTpl->set('d', 'FILENAME', $aFiles[$i]);
                $oTpl->set('d', 'FILESIZE', number_format((filesize($backup_path . $aFiles[$i]) / 1024 / 1024), 2));
                $oTpl->set('d', 'LINK_DELETE', $sess->url('main.php?area=' . $plugin_name . '&amp;frame=4&amp;mode=0&amp;action=3&amp;file=' . $aFiles[$i]));
                $oTpl->set('d', 'LABEL_DELETE', '<img src="images/delete.gif" alt="X" title="' . i18n("Delete") . '" />');
                $oTpl->next();
            }
            $oTpl->generate($cfg[$plugin_name]['templates']['backup']);
            break;
        case 1:
            $oTpl->set('s', 'LABEL_RESTORE', i18n("Restore", $plugin_name));
            $aFiles = getFilesInDirectory($backup_path, '*.sql*', SORT_DESC);
            for ($i = 0, $n = count($aFiles); $i < $n; $i ++) {
                $oTpl->set('d', 'NO', ($i + 1));
                $oTpl->set('d', 'FILENAME', $aFiles[$i]);
                $oTpl->set('d', 'FILESIZE', number_format((filesize($backup_path . $aFiles[$i]) / 1024 / 1024), 2));
                $oTpl->set('d', 'LINK_RESTORE', $sess->url('main.php?area=' . $plugin_name . '&amp;frame=4&amp;mode=1&amp;action=2&amp;file=' . $aFiles[$i]));
                $oTpl->set('d', 'LABEL_RESTORE', '<img src="images/importieren.gif" alt="X" title="' . i18n("Restore") . '" />');
                $oTpl->next();
            }
            $oTpl->generate($cfg[$plugin_name]['templates']['restore']);
            break;
        default:
            $sMsg = '';
            break;
    }
}

# Functions
function backup_tables($file, $host, $user, $pass, $name, $current_table = '', $current_row = 0) {
    global $cfg;
    
    $iStart = time();
    $iMET = 2;#(intval(ini_get('max_execution_time')) - 10); # We need some time for other tasks
    $db = new DB_Contenido();
    
    # Open the output file
    $gz = extension_loaded('zlib');
    if ($gz) {
        if (!$handle = gzopen($file . '.gz', 'a')) {
            return false;
        }
    } else {
        if (!$handle = fopen($file, 'a')) {
            return false;
        }
    }
    
    if (strlen($current_table) == 0) {
        # Create the header
        $return  = '-- drugCMS SQL Dump' . "\n";
        $return .= '-- drugCMS ' . $cfg['version'] . "\n";
        $return .= '-- (c) 2013 Spider IT Deutschland' . "\n";
        $return .= '--' . "\n";
        $return .= '-- Host: ' . $host . "\n";
        $return .= '-- Backup creation date: ' . date('r') . "\n";
        $ver     = $db->server_info();
        $return .= '-- Server version: ' . $ver['description'] . "\n";
        $return .= '-- PHP version: ' . phpversion() . "\n";
        $return .= "\n";
        $return .= 'SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";' . "\n";
        $return .= "\n";
        $return .= '--' . "\n";
        $return .= '-- Database: `' . $name . '`' . "\n";
        $return .= '--';
        if ($gz) {
            gzwrite($handle, $return);
        } else {
            fwrite($handle, $return);
        }
        $return = '';
    }
    
    # Get all the tables
    $tables = array();
    $result = $db->query('SHOW TABLES');
    while ($db->next_record()) {
        $tables[] = $db->f(0);
    }
    
    # Loop through the tables
    $bOK = false;
    foreach ($tables as $table) {
        if ((strlen($current_table)) && (!$bOK)) {
            if ($table == $current_table) {
                $bOK = true;
            }
        } else {
            $bOK = true;
        }
        if ($bOK) {
            if ($current_row == 0) {
                $return .= "\n";
                $return .= "\n";
                $return .= '--  --------------------------------------------------------' . "\n";
                $return .= "\n";
                $return .= '--' . "\n";
                $return .= '-- Table structure for table `' . $table . '`' . "\n";
                $return .= '--' . "\n";
                $return .= "\n";
                if (in_array(substr($table, strlen($cfg['sql']['sqlprefix'])), array('_online_user', '_phplib_active_sessions'))) {
                    # Don't drop these tables as the current user would be logged out
                    # while restoring the database, stopping the restore process
                    $db->query('SHOW CREATE TABLE `' . $table . '`');
                    $db->next_record();
                    $row2 = $db->toArray(DB_SQL_Abstract::FETCH_BOTH);
                    $return .= str_replace('CREATE TABLE `', 'CREATE TABLE IF NOT EXISTS `', $row2[1]) . ";\n";
                } else {
                    $return .= 'DROP TABLE IF EXISTS `' . $table . '`;' . "\n";
                    $db->query('SHOW CREATE TABLE `' . $table . '`');
                    $db->next_record();
                    $row2 = $db->toArray(DB_SQL_Abstract::FETCH_BOTH);
                    $return .= $row2[1] . ";\n";
                }
                # Only backup data which is supposed to be permanent
                if (!in_array(substr($table, strlen($cfg['sql']['sqlprefix'])), array('_code', '_inuse', '_online_user', '_phplib_active_sessions'))) {
                    $return .= "\n";
                    $return .= '--' . "\n";
                    $return .= '-- Data for table `' . $table . '`' . "\n";
                    $return .= '--' . "\n";
                    if ($gz) {
                        gzwrite($handle, $return);
                    } else {
                        fwrite($handle, $return);
                    }
                    $return = '';
                }
            }
            # Only backup data which is supposed to be permanent
            if (!in_array(substr($table, strlen($cfg['sql']['sqlprefix'])), array('_code', '_inuse', '_online_user', '_phplib_active_sessions'))) {
                # Get the key (first) column in the table (we sort it on this to export
                # each row just once if we split because of the time management)
                $db->query('SHOW COLUMNS FROM ' . $table . ' WHERE EXTRA LIKE "%auto_increment%"');
                $db->next_record();
                $row = $db->toArray(DB_SQL_Abstract::FETCH_BOTH);
                $key_column = $db->f(0);
                if (!strlen($key_column)) {
                    $db->query('SHOW COLUMNS FROM ' . $table);
                    $db->next_record();
                    $row = $db->toArray(DB_SQL_Abstract::FETCH_BOTH);
                    $key_column = $row[0];
                }
                # Get the amount of rows in this table
                $db->query('SELECT COUNT(' . $key_column . ') AS num_rows FROM ' . $table);
                $db->next_record();
                $num_rows = $db->f('num_rows');
                # Query the data
                $bDone = false;
                while (!$bDone) {
                    $db->query('SELECT * FROM ' . $table . ' ORDER BY ' . $key_column . ' LIMIT ' . $current_row . ', ' . ((($num_rows - $current_row) > 250) ? 250 : ($num_rows - $current_row)));
                    $num_recs = 0;
                    $num_fields = $db->num_fields();
                    if ($db->next_record()) {
                        $num_recs ++;
                        $row = $db->toArray(DB_SQL_Abstract::FETCH_BOTH);
                        $return .= "\n";
                        $return .= 'INSERT INTO `' . $table . '` (';
                        $keys = array();
                        foreach ($row as $key => $value) {
                            if (!is_numeric($key)) {
                                $keys[] = '`' . $key . '`';
                            }
                        }
                        $return .= implode(', ', $keys) . ') VALUES' . "\n";
                        $return .= '(';
                        for ($i = 0; $i < $num_fields; $i++) {
                            if (!isset($row[$i])) {
                                $return .= 'NULL';
                            } elseif (is_numeric($row[$i])) {
                                $return .= $row[$i];
                            } else {
                                $return .= "'" . str_replace(array("'", '\\', "\r", "\n"), array("''", '\\\\', "\\r", "\\n"), $row[$i]) . "'";
                            }
                            if ($i < ($num_fields - 1)) {
                                $return .= ', ';
                            }
                        }
                        $return .= ')';
                        # Time management
                        if (((time() - $iStart) >= $iMET) || ($current_row == ($num_rows - 1)) || ($num_recs == 250)) {
                            $return .= ';' . "\n";
                        } else {
                            $return .= ',' . "\n";
                        }
                        if ($gz) {
                            gzwrite($handle, $return);
                        } else {
                            fwrite($handle, $return);
                        }
                        $return = '';
                        $current_row ++;
                    }
                    while ($db->next_record()) {
                        $num_recs ++;
                        $row = $db->toArray(DB_SQL_Abstract::FETCH_BOTH);
                        $return .= '(';
                        for ($i = 0; $i < $num_fields; $i++) {
                            if (!isset($row[$i])) {
                                $return .= 'NULL';
                            } elseif (is_numeric($row[$i])) {
                                $return .= $row[$i];
                            } else {
                                $return .= "'" . str_replace(array("'", '\\', "\r", "\n"), array("''", '\\\\', "\\r", "\\n"), $row[$i]) . "'";
                            }
                            if ($i < ($num_fields - 1)) {
                                $return .= ', ';
                            }
                        }
                        $return .= ')';
                        # Time management
                        if (((time() - $iStart) >= $iMET) || ($current_row == ($num_rows - 1)) || ($num_recs == 250)) {
                            $return .= ';' . "\n";
                        } else {
                            $return .= ',' . "\n";
                        }
                        if ($gz) {
                            gzwrite($handle, $return);
                        } else {
                            fwrite($handle, $return);
                        }
                        $return = '';
                        $current_row ++;
                        # Time management
                        if ((time() - $iStart) >= $iMET) {
                            $db->disconnect();
                            return array('table' => $table, 'row' => $current_row);
                        }
                    }
                    if ($current_row == $num_rows) {
                        $bDone = true;
                    }
                }
                $current_row = 0; # Reset for the next table
            }
        }
    }
    
    # Set the code generation flag on restoring
    $return .= "\n";
    $return .= "\n";
    $return .= "\n";
    $return .= '--  --------------------------------------------------------' . "\n";
    $return .= "\n";
    $return .= '--' . "\n";
    $return .= '-- Set the code generation flag on restoring' . "\n";
    $return .= '--' . "\n";
    $return .= "\n";
    $return .= 'UPDATE `' . $cfg['sql']['sqlprefix'] . '_cat_art` SET `createcode` = 1;';
    
    //save file
    if ($gz) {
        gzwrite($handle, $return . "\n");
        gzclose($handle);
    } else {
        fwrite($handle, $return . "\n");
        fclose($handle);
    }
    $db->disconnect();
    return true;
}
?>