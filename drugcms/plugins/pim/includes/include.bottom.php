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
 * @author     Ren Mansveld
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
    die('Illegal call');
}

$plugin_name = "pim";

if (!$perm->have_perm_area_action($plugin_name, $plugin_name)) {
    exit;
}

function isWritablePath($sPath) {
    $sPath .= ((substr($sPath, -1) == DIRECTORY_SEPARATOR) ? '' : DIRECTORY_SEPARATOR);
    $sFile = 'sit_' . uniqid(mt_rand()) . '.writable';
    $bOK = false;
    $fp = @fopen($sPath . $sFile, 'w');
    if ($fp !== false) {
        $bOK = true;
        fclose($fp);
        unlink($sPath . $sFile);
    }
    return $bOK;
}
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
function copyr($src, $dst) {
    $src .= ((substr($src, -1) == '/') ? '' : '/');
    $dst .= ((substr($dst, -1) == '/') ? '' : '/');
    
    $dir = opendir($src);
    @mkdir($dst);
    while (($file = readdir($dir)) !== false) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                copyr($src . $file, $dst . $file);
            }
            else {
                copy($src . $file, $dst . $file);
            }
        }
    }
    closedir($dir);
}

$sContent = '';
if (!isWritablePath($cfg['path']['contenido'] . $cfg['path']['plugins'])) {
    # Error Missing write permission for folder drugcms/plugins
    $sContent .= $notification->returnNotification("error", sprintf(i18n("%s is not writable"), basename($cfg['path']['contenido']) . '/' . $cfg['path']['plugins'])) . '</p>';
}

plugin_include($plugin_name, 'includes/config.plugin.php');
plugin_include($plugin_name, 'classes/class.plugininfo.php');

$oTpl = new Template();

$sWhat = ((isset($_REQUEST['what'])) ? $_REQUEST['what'] : (($perm->have_perm_area_action($plugin_name, 'plugins')) ? 'plugins' : 'repositories'));
$sAction = ((isset($_REQUEST['Action'])) ? $_REQUEST['Action'] : 'List');
$sFilter = ((isset($_REQUEST['Filter'])) ? $_REQUEST['Filter'] : '');
if (strlen($sFilter)) {
    $aFilter = explode(',', $sFilter);
    $sFilter = '';
    for ($i = 0, $n = count($aFilter); $i < $n; $i ++) {
        if (strlen(trim($aFilter[$i]))) {
            $sFilter .= strtolower(trim($aFilter[$i])) . ',';
        }
    }
    $sFilter = substr($sFilter, 0, -1);
}

switch ($sWhat) {
    case 'plugins':
        if ($perm->have_perm_area_action($plugin_name, 'plugins')) {
            $sScrollScript = '<script type="text/javascript">
                setTimeout(function() {
                    $("html, body").animate({scrollTop: $(document).height() + "px"}, 2000);
                }, 100);
            </script>';
            $sReloadScript = '<script type="text/javascript">
                var tmr;
                tmr = setInterval(function() {
                    checkLoadInit();
                }, 1);
                function checkLoadInit() {
                    var nav = parent.parent.frames.header.document.getElementById("main_3");
                    if (nav == null) {
                        clearInterval(tmr);
                        tmr = setInterval(function() {
                            checkLoadReady();
                        }, 10);
                    }
                }
                function checkLoadReady() {
                    var nav = parent.parent.frames.header.document.getElementById("main_3");
                    if (nav != null) {
                        clearInterval(tmr);
                        $("#sub_pim", top.frames["header"].document).removeClass("sub").addClass("activemenu");
                        parent.parent.frames.header.window.show("sub_3", "main_3");
                        setTimeout(function() {
                            var menu = parent.parent.frames.header.window.ContenidoRegistry.get("headerMenu");
                            menu.setActiveSubMenu("sub_3");
                            menu.setActiveMenu("main_3");
                        }, 10);
                    }
                }
                parent.parent.frames["header"].document.location.href = parent.parent.frames["header"].document.location.href;
                setTimeout(function() {
                    document.location.href = document.location.href' . ((strlen($_REQUEST['Filter'])) ? ' + "&Filter=' . $_REQUEST['Filter'] . '"' : '') . ';
                }, 5000);
            </script>';

            switch ($sAction) {
                case 'List':
                    
                    # Set fixed informations
                    $oTpl->set('s', 'FORM_ACTION', 'main.php?area=pim&amp;frame=4&amp;contenido=' . $contenido);
                    $oTpl->set('s', 'TEXT_INSTALLED', i18n("Installed plugins", $plugin_name));
                    $oTpl->set('s', 'TEXT_FILTER', i18n("Filter", $plugin_name));
                    $oTpl->set('s', 'TEXT_FILTER_DESCRIPTION', i18n("(Comma separated list of tags to search for)", $plugin_name));
                    $oTpl->set('s', 'TEXT_DO_FILTER', i18n("Use filter", $plugin_name));
                    $oTpl->set('s', 'TEXT_CLEAR_FILTER', i18n("Clear filter", $plugin_name));
                    $oTpl->set('s', 'TEXT_AVAILABLE', i18n("Available plugins", $plugin_name));
                    $oTpl->set('s', 'TEXT_INSTALL', i18n("Install", $plugin_name));
                    $oTpl->set('s', 'TEXT_UNINSTALL', i18n("Uninstall", $plugin_name));
                    $oTpl->set('s', 'TEXT_NAME', i18n("Plugin", $plugin_name));
                    $oTpl->set('s', 'TEXT_DESCRIPTION', i18n("Description", $plugin_name));
                    $oTpl->set('s', 'TEXT_TAGS', i18n("Tags", $plugin_name));
                    $oTpl->set('s', 'TEXT_AUTHOR', i18n("Author", $plugin_name));
                    $oTpl->set('s', 'TEXT_COPYRIGHT', i18n("Copyright", $plugin_name));
                    $oTpl->set('s', 'TEXT_VERSION', i18n("Version", $plugin_name));
                    $oTpl->set('s', 'TEXT_DEPENDENCIES', i18n("Dependencies", $plugin_name));
                    $oTpl->set('s', 'TEXT_MODULES', i18n("Modules", $plugin_name));
                    $oTpl->set('s', 'TEXT_INSTALL_MODULES', i18n("Install modules for the current client", $plugin_name));
                    $oTpl->set('s', 'TEXT_REPOSITORY', i18n("Repository", $plugin_name));
                    
                    $oTpl->set('s', 'FILTER', $_REQUEST['Filter']);
                    
                    # Get installed plugins
                    $aPluginsI = array();
                    
                    # First list all plugin folders
                    if ($dh = opendir($cfg['path']['contenido'] . $cfg['path']['plugins'])) {
                        while (($sEntry = readdir($dh)) !== false) {
                            if ((is_dir($cfg['path']['contenido'] . $cfg['path']['plugins'] . $sEntry)) && (substr($sEntry, 0, 1) != '.')) {
                                $aPluginsI[$sEntry] = array();
                            }
                        }
                        closedir($dh);
                    }
                    
                    # Then get the plugin info's from their plugin.xml files
                    $oLang = new Language();
                    $oLang->loadByPrimaryKey($lang);
                    $sLanguage = strtolower($oLang->getProperty('language', 'code'));
                    $sCulture = $sLanguage . '_' . strtoupper($oLang->getProperty('country', 'code'));
                    $oPlugin = new PluginInfo();
                    foreach ($aPluginsI as $key => $value) {
                        $oPlugin->pluginName($key);
                        if (($aInfo = $oPlugin->getInfo()) !== false) {
                            $aPluginsI[$key] = $aInfo;
                        }
                    }
                    
                    # Get available plugins from plugins.drugcms.org
                    #getRemoteContent($url, &$errno, &$errmsg, $login = '', $password = '')
                    $sUrl = 'http://plugins.drugcms.org/' . ((strlen($sFilter)) ? '?Filter=' . urlencode($sFilter) : '');
                    $sDownloadUrl = 'http://plugins.drugcms.org/';
                    $iErrNo = 0;
                    $sErrMsg = '';
                    $aPluginsA = json_decode(getRemoteContent($sUrl, $iErrNo, $sErrMsg), true);
                    foreach ($aPluginsA as $key => $value) {
                        $aPluginsA[$key]['Url'] = $sDownloadUrl;
                        $aPluginsA[$key]['Repo'] = 'drugCMS';
                    }
                    
                    # Get available plugins from additionally added repositories
                    $aReposInfo = getPropertiesByItemtype('plugin_repository', 'itemid');
                    $aRepos = array();
                    for ($i = 0, $n = count($aReposInfo); $i < $n; $i ++) {
                        $aRepos[$aReposInfo[$i]['itemid']][$aReposInfo[$i]['name']] = $aReposInfo[$i]['value'];
                    }
                    foreach ($aRepos as $itemid => $server) {
                        $oTpl->set('s', 'SHOW_REPOSITORY', '1');
                        $sUrl = $server['host'] . ((strlen($sFilter)) ? '?Filter=' . urlencode($sFilter) : '');
                        $sDownloadUrl = $server['host'];
                        $sLogin = $server['login'];
                        $sPassword = $server['password'];
                        $iErrNo = 0;
                        $sErrMsg = '';
                        $aPluginsO = json_decode(getRemoteContent($sUrl, $iErrNo, $sErrMsg, $sLogin, $sPassword), true);
                        foreach ($aPluginsO as $key => $value) {
                            $aPluginsA[$key . '~~' . $itemid] = $value;
                            $aPluginsA[$key . '~~' . $itemid]['Url'] = $sDownloadUrl;
                            $aPluginsA[$key . '~~' . $itemid]['Repo'] = $itemid;
                        }
                    }
                    $oTpl->set('s', 'SHOW_REPOSITORY', '');
                    
                    # List installed plugins
                    $sClass = 'odd';
                    foreach ($aPluginsI as $key => $value) {
                        if (count($value)) {
                            $oTpl->set('d', 'KEY', $key);
                            $oTpl->set('d', 'NAME', $value['Name']);
                            $sDescription = '';
                            if (strlen($value['Description'][$sCulture])) {
                                $sDescription = $value['Description'][$sCulture];
                            }
                            else {
                                foreach ($value['Description'] as $cult => $desc) {
                                    if (substr($sCult, 0, strpos($sCult, '_')) == $sLanguage) {
                                        $sDescription = $desc;
                                        break;
                                    }
                                }
                            }
                            $oTpl->set('d', 'DESCRIPTION', ((strlen($sDescription)) ? $sDescription : $value['Description']['en_US']));
                            $oTpl->set('d', 'TAGS', implode(', ', $value['Tags']));
                            $oTpl->set('d', 'AUTHOR', $value['Author']);
                            $oTpl->set('d', 'COPYRIGHT', $value['Copyright']);
                            $oTpl->set('d', 'VERSION', $value['Version']);
                            $sDependencies = '';
                            for ($i = 0, $n = count($value['Dependencies']); $i < $n; $i ++) {
                                $sDependencies .= $value['Dependencies'][$i]['Name'] . ' (' . $value['Dependencies'][$i]['Version'] . '), ';
                            }
                            $oTpl->set('d', 'DEPENDENCIES', substr($sDependencies, 0, -2));
                            $sModules = '';
                            for ($i = 0, $n = count($value['Modules']); $i < $n; $i ++) {
                                $sModules .= $value['Modules'][$i]['Name'] . ', '; # $value['Modules'][$i]['File']
                            }
                            $oTpl->set('d', 'MODULES', substr($sModules, 0, -2));
                            $oTpl->set('d', 'INST_MOD', ((strlen($sModules)) ? 'true' : 'false'));
                            if (strlen($sModules)) {
                                $oTpl->set('s', 'SHOW_MODULES', 'true');
                            }
                            if ((!array_key_exists($key, $aPluginsA)) || (version_compare($value['Version'], $aPluginsA[$key]['Version'], '>='))) {
                                $oTpl->set('d', 'CLASS', $sClass);
                            }
                            elseif (version_compare($value['Version'], $aPluginsA[$key]['Version'], '<')) {
                                $oTpl->set('d', 'CLASS', 'old');
                            }
                            foreach ($aPluginsI as $plugin => $settings) {
                                for ($i = 0, $n = count($settings['Dependencies']); $i < $n; $i ++) {
                                    if ($settings['Dependencies'][$i]['Name'] == $value['Name']) {
                                        $oTpl->set('d', 'IMG_CLASS', '');
                                        $oTpl->set('d', 'IMAGE', 'exclamation');
                                        $oTpl->set('d', 'TEXT_UNINSTALL', sprintf(i18n("%s can't be uninstalled because of another installed plugin's dependencies", $plugin_name), $value['Name']));
                                        break(2);
                                    }
                                }
                            }
                            $oTpl->set('d', 'IMG_CLASS', 'remove');
                            $oTpl->set('d', 'IMAGE', 'but_delete');
                            $oTpl->set('d', 'TEXT_UNINSTALL', sprintf(((strlen($sModules)) ? i18n("Uninstall %s including it's modules (all clients)", $plugin_name) : i18n("Uninstall %s", $plugin_name)), $value['Name']));
                            $sClass = (($sClass == 'odd') ? 'even' : 'odd');
                            $oTpl->next();
                        }
                    }
                    $oTpl->set('s', 'SHOW_MODULES', 'false');
                    
                    # List available plugins
                    $sClass = 'odd';
                    foreach ($aPluginsA as $key => $value) {
                        $bAdditional = (strpos($key, '~~') !== false);
                        $key = ((strpos($key, '~~') !== false) ? substr($key, 0, strpos($key, '~~')) : $key);
                        if ((!array_key_exists($key, $aPluginsI)) || (version_compare($value['Version'], $aPluginsI[$key]['Version'], '>'))) {
                            $oTpl->set('d1', 'KEY', $key);
                            $oTpl->set('d1', 'URL', $value['Url']);
                            $oTpl->set('d1', 'NAME', $value['Name']);
                            $sDescription = '';
                            if (strlen($value['Description'][$sCulture])) {
                                $sDescription = $value['Description'][$sCulture];
                            }
                            else {
                                foreach ($value['Description'] as $cult => $desc) {
                                    if (substr($sCult, 0, strpos($sCult, '_')) == $sLanguage) {
                                        $sDescription = $desc;
                                        break;
                                    }
                                }
                            }
                            $oTpl->set('d1', 'DESCRIPTION', ((strlen($sDescription)) ? $sDescription : $value['Description']['en_US']));
                            $oTpl->set('d1', 'TAGS', implode(', ', $value['Tags']));
                            $oTpl->set('d1', 'AUTHOR', $value['Author']);
                            $oTpl->set('d1', 'COPYRIGHT', $value['Copyright']);
                            $oTpl->set('d1', 'VERSION', $value['Version']);
                            $sDependencies = '';
                            for ($i = 0, $n = count($value['Dependencies']); $i < $n; $i ++) {
                                if (!array_key_exists($value['Dependencies'][$i]['Name'], $aPluginsI)) {
                                    $sDependencies .= '<span class="red" title="' . i18n("Missing plugin", $plugin_name) . '">' . $value['Dependencies'][$i]['Name'] . ' (' . $value['Dependencies'][$i]['Version'] . ')</span>, ';
                                    $oTpl->set('d1', 'CLASS', $sClass);
                                    $oTpl->set('d1', 'TEXT_INSTALL', sprintf(i18n("Missing or outdated dependencies prevent installation of %s", $plugin_name), $value['Name']));
                                    $oTpl->set('d1', 'IMG_CLASS', '');
                                    $oTpl->set('d1', 'IMAGE', 'icon_warning');
                                }
                                elseif (version_compare($aPluginsI[$value['Dependencies'][$i]['Name']]['Version'], $value['Dependencies'][$i]['Version'], '<')) {
                                    $sDependencies .= '<span class="red" title="' . i18n("Outdated plugin", $plugin_name) . '">' . $value['Dependencies'][$i]['Name'] . ' (' . $value['Dependencies'][$i]['Version'] . ')</span>, ';
                                    $oTpl->set('d1', 'CLASS', $sClass);
                                    $oTpl->set('d1', 'TEXT_INSTALL', sprintf(i18n("Missing or outdated dependencies prevent installation of %s", $plugin_name), $value['Name']));
                                    $oTpl->set('d1', 'IMG_CLASS', '');
                                    $oTpl->set('d1', 'IMAGE', 'icon_warning');
                                }
                                else {
                                    $sDependencies .= $value['Dependencies'][$i]['Name'] . ' (' . $value['Dependencies'][$i]['Version'] . '), ';
                                }
                            }
                            $oTpl->set('d1', 'DEPENDENCIES', substr($sDependencies, 0, -2));
                            if (!array_key_exists($key, $aPluginsI)) {
                                $oTpl->set('d1', 'CLASS', $sClass);
                                $oTpl->set('d1', 'TEXT_INSTALL', sprintf(i18n("Install %s", $plugin_name), $value['Name']));
                            }
                            elseif (version_compare($value['Version'], $aPluginsI[$key]['Version'], '>')) {
                                $oTpl->set('d1', 'CLASS', 'new');
                                $oTpl->set('d1', 'TEXT_INSTALL', sprintf(i18n("Update %s", $plugin_name), $value['Name']));
                            }
                            $oTpl->set('d1', 'IMG_CLASS', 'install');
                            $oTpl->set('d1', 'IMAGE', 'importieren');
                            $oTpl->set('d1', 'REPOSITORY', $value['Repo']);
                            $sClass = (($sClass == 'odd') ? 'even' : 'odd');
                            $oTpl->next(1);
                        }
                    }
                    
                    # Generate list template
                    $sContent .= $oTpl->generate($cfg['path']['contenido'] . $cfg['path']['plugins'] . $plugin_name . '/templates/default/list.html', true);
                    $oTpl->reset();
                    break;
                
                case 'Install':
                    $sContent .= $sScrollScript;
                    $sPlugin = $_POST['Key'];
                    $sUrl = $_POST['Url'];
                    $sPath = $cfg['path']['contenido'] . $cfg['path']['plugins'] . $sPlugin . '/';
                    $bUpdate = is_dir($sPath);
                    if (($bUpdate) || (mkdir($sPath, 0750))) {
                        # Download the plugin
                        $bOK = false;
                        if (class_exists('ZipArchive')) {
                            if (getRemoteContentToFile($sUrl . '?Plugin=' . $sPlugin . '&Action=GetZip', $sPath . $sPlugin . '.zip', $iErrNo, $sErrMsg, $sLogin, $sPassword)) {
                                $sContent .= '<p>' . i18n("Zip file downloaded", $plugin_name) . '</p>';
                                flush();
                                
                                # Extract the zip file to it's folder
                                $zip = new ZipArchive();
                                if ($zip->open($sPath . $sPlugin . '.zip')) {
                                    $zip->extractTo($sPath);
                                    $zip->close();
                                }
                                $sContent .= '<p>' . i18n("Zip file extracted", $plugin_name) . '</p>';
                                flush();
                                $bOK = true;
                                
                                # Delete the zip file
                                #unlink($sPath . $sPlugin . '.zip');
                            }
                            else {
                                $sContent .= $notification->returnNotification("error", i18n("Failed to download zip file", $plugin_name)) . '</p>';
                            }
                        }
                        if (!$bOK) {
                            $bOK = true;
                            $aFoldersAndFiles = json_decode(getRemoteContent($sUrl . '?Plugin=' . $sPlugin . '&Action=Files', $iErrNo, $sErrMsg, $sLogin, $sPassword));
                            for ($i = 0, $n = count($aFoldersAndFiles); $i < $n; $i ++) {
                                if (substr($aFoldersAndFiles[$i], -1) == '/') {
                                    if (mkdir($sPath . $aFoldersAndFiles[$i], 0750)) {
                                        $sContent .= '<p>' . sprintf(i18n("Folder %s created", $plugin_name), $aFoldersAndFiles[$i]) . '</p>';
                                        flush();
                                    }
                                    else {
                                        $sContent .= $notification->returnNotification("error", sprintf(i18n("Unable to create folder %s", $plugin_name), $aFoldersAndFiles[$i])) . '</p>';
                                        flush();
                                        $bOK = false;
                                        break;
                                    }
                                }
                                else {
                                    if (getRemoteContentToFile($sUrl . '?Plugin=' . $sPlugin . '&Action=GetFile&File=' . urlencode($aFoldersAndFiles[$i]), $sPath . $aFoldersAndFiles[$i], $iErrNo, $sErrMsg, $sLogin, $sPassword)) {
                                        $sContent .= '<p>File ' . $aFoldersAndFiles[$i] . ' downloaded</p>';
                                        flush();
                                    }
                                    else {
                                        $sContent .= $notification->returnNotification("error", $sErrMsg) . '</p>';
                                        flush();
                                        $bOK = false;
                                        break;
                                    }
                                }
                            }
                        }
                        if ($bOK) {
                            
                            # Install the plugin
                            $oPlugin = new PluginInfo($sPlugin);
                            $aInstall = $oPlugin->getInstallInfo();
                            if ($aInstall !== false) {
                                $sContent .= '<p>' . i18n("Installing database entries", $plugin_name) . '</p>';
                                $aDescription = array();
                                
                                # Areas
                                for ($i = 0, $n = count($aInstall['areas']); $i < $n; $i ++) {
                                    $id = $db->nextid($cfg['tab']['area']);
                                    if ((is_numeric($aInstall['areas'][$i]['parent'])) && ($aInstall['areas'][$i]['parent'] != 0)) {
                                        $sql = 'INSERT INTO ' . $cfg['tab']['area'] . ' (idarea, parent_id, name, relevant, online, menuless)
                                                VALUES (' . $id . ', "' . Contenido_Security::escapeDB($aInstall['areas'][($aInstall['areas'][$i]['parent'] - 1)]['name'], $db) . '", "' . Contenido_Security::escapeDB($aInstall['areas'][$i]['name'], $db) . '", ' . intval($aInstall['areas'][$i]['relevant']) . ', ' . intval($aInstall['areas'][$i]['online']) . ', ' . intval($aInstall['areas'][$i]['menuless']) . ')';
                                    }
                                    else {
                                        $sql = 'INSERT INTO ' . $cfg['tab']['area'] . ' (idarea, parent_id, name, relevant, online, menuless)
                                                VALUES (' . $id . ', "' . Contenido_Security::escapeDB($aInstall['areas'][$i]['parent'], $db) . '", "' . Contenido_Security::escapeDB($aInstall['areas'][$i]['name'], $db) . '", ' . intval($aInstall['areas'][$i]['relevant']) . ', ' . intval($aInstall['areas'][$i]['online']) . ', ' . intval($aInstall['areas'][$i]['menuless']) . ')';
                                    }
                                    $db->query($sql);
                                    $aInstall['areas'][$i]['idarea'] = $id;
                                    $aDescription[] = array('table' => $cfg['tab']['area'], 'id' => $id);
                                }
                                
                                # Actions
                                for ($i = 0, $n = count($aInstall['actions']); $i < $n; $i ++) {
                                    $id = $db->nextid($cfg['tab']['actions']);
                                    $sql = 'INSERT INTO ' . $cfg['tab']['actions'] . ' (idaction, idarea, alt_name, name, code, location, relevant)
                                            VALUES (' . $id . ', ' . intval($aInstall['areas'][(intval($aInstall['actions'][$i]['area']) - 1)]['idarea']) . ', "' . Contenido_Security::escapeDB($aInstall['actions'][$i]['alt_name'], $db) . '", "' . Contenido_Security::escapeDB($aInstall['actions'][$i]['name'], $db) . '", "' . Contenido_Security::escapeDB($aInstall['actions'][$i]['code'], $db) . '", "' . Contenido_Security::escapeDB($aInstall['actions'][$i]['location'], $db) . '", "' . Contenido_Security::escapeDB($aInstall['actions'][$i]['relevant'], $db) . '")';
                                    $db->query($sql);
                                    $aInstall['actions'][$i]['idaction'] = $id;
                                    $aDescription[] = array('table' => $cfg['tab']['actions'], 'id' => $id);
                                }
                                
                                # Files
                                for ($i = 0, $n = count($aInstall['files']); $i < $n; $i ++) {
                                    $id = $db->nextid($cfg['tab']['files']);
                                    if (is_numeric($aInstall['files'][$i]['area'])) {
                                        $sql = 'INSERT INTO ' . $cfg['tab']['files'] . ' (idfile, idarea, filename, filetype)
                                                VALUES (' . $id . ', ' . intval($aInstall['areas'][(intval($aInstall['files'][$i]['area']) - 1)]['idarea']) . ', "' . Contenido_Security::escapeDB($aInstall['files'][$i]['filename'], $db) . '", "' . Contenido_Security::escapeDB($aInstall['files'][$i]['filetype'], $db) . '")';
                                    }
                                    else {
                                        $sql = 'SELECT idarea
                                                FROM ' . $cfg['tab']['area'] . '
                                                WHERE (name="' . $aInstall['files'][$i]['area'] . '")';
                                        $db->query($sql);
                                        if ($db->next_record()) {
                                            $idarea = $db->f('idarea');
                                        }
                                        $sql = 'INSERT INTO ' . $cfg['tab']['files'] . ' (idfile, idarea, filename, filetype)
                                                VALUES (' . $id . ', ' . intval($idarea) . ', "' . Contenido_Security::escapeDB($aInstall['files'][$i]['filename'], $db) . '", "' . Contenido_Security::escapeDB($aInstall['files'][$i]['filetype'], $db) . '")';
                                    }
                                    $db->query($sql);
                                    $aInstall['files'][$i]['idfile'] = $id;
                                    $aDescription[] = array('table' => $cfg['tab']['files'], 'id' => $id);
                                }
                                
                                # FrameFiles
                                for ($i = 0, $n = count($aInstall['frame_files']); $i < $n; $i ++) {
                                    $id = $db->nextid($cfg['tab']['framefiles']);
                                    if (is_numeric($aInstall['frame_files'][$i]['area'])) {
                                        $sql = 'INSERT INTO ' . $cfg['tab']['framefiles'] . ' (idframefile, idarea, idframe, idfile)
                                                VALUES (' . $id . ', ' . intval($aInstall['areas'][(intval($aInstall['frame_files'][$i]['area']) - 1)]['idarea']) . ', ' . intval($aInstall['frame_files'][$i]['frame_id']) . ', ' . intval($aInstall['files'][(intval($aInstall['frame_files'][$i]['file']) - 1)]['idfile']) . ')';
                                    }
                                    else {
                                        $sql = 'SELECT idarea
                                                FROM ' . $cfg['tab']['area'] . '
                                                WHERE (name="' . $aInstall['frame_files'][$i]['area'] . '")';
                                        $db->query($sql);
                                        if ($db->next_record()) {
                                            $idarea = $db->f('idarea');
                                        }
                                        $sql = 'INSERT INTO ' . $cfg['tab']['framefiles'] . ' (idframefile, idarea, idframe, idfile)
                                                VALUES (' . $id . ', ' . intval($idarea) . ', ' . intval($aInstall['frame_files'][$i]['frame_id']) . ', ' . intval($aInstall['files'][(intval($aInstall['frame_files'][$i]['file']) - 1)]['idfile']) . ')';
                                    }
                                    $db->query($sql);
                                    $aInstall['frame_files'][$i]['idframefile'] = $id;
                                    $aDescription[] = array('table' => $cfg['tab']['framefiles'], 'id' => $id);
                                }
                                
                                # NavMain
                                if (strlen($aInstall['nav_main'])) {
                                    $id = $db->nextid($cfg['tab']['nav_main']);
                                    $sql = 'INSERT INTO ' . $cfg['tab']['nav_main'] . ' (idnavm, location)
                                            VALUES (' . $id . ', "' . Contenido_Security::escapeDB($aInstall['nav_main']['location'], $db) . '")';
                                    $db->query($sql);
                                    $aInstall['nav_main']['idnavm'] = $id;
                                    $aDescription[] = array('table' => $cfg['tab']['nav_main'], 'id' => $id);
                                }
                                
                                # NavSub
                                for ($i = 0, $n = count($aInstall['nav_subs']); $i < $n; $i ++) {
                                    $id = $db->nextid($cfg['tab']['nav_sub']);
                                    if (is_numeric($aInstall['nav_subs'][$i]['nav_main'])) {
                                        $idnavm = intval($aInstall['nav_main']['idnavm']);
                                    }
                                    else {
                                        $sql = 'SELECT idnavm
                                                FROM ' . $cfg['tab']['nav_main'] . '
                                                WHERE (location LIKE "%' . Contenido_Security::escapeDB($aInstall['nav_subs'][$i]['nav_main'], $db) . '%")';
                                        $db->query($sql);
                                        $db->next_record();
                                        $idnavm = $db->f('idnavm');
                                    }
                                    $sql = 'INSERT INTO ' . $cfg['tab']['nav_sub'] . ' (idnavs, idnavm, idarea, level, location, online)
                                            VALUES (' . $id . ', ' . $idnavm . ', ' . intval($aInstall['areas'][(intval($aInstall['nav_subs'][$i]['area']) - 1)]['idarea']) . ', ' . intval($aInstall['nav_subs'][$i]['level']) . ', "' . Contenido_Security::escapeDB($aInstall['nav_subs'][$i]['location'], $db) . '", ' . intval($aInstall['nav_subs'][$i]['online']) . ')';
                                    $db->query($sql);
                                    $aInstall['nav_subs'][$i]['idframefile'] = $id;
                                    $aDescription[] = array('table' => $cfg['tab']['nav_sub'], 'id' => $id);
                                }
                                
                                # Copy additional system files
                                $aFoldersAndFiles = $oPlugin->getSystemAdditionalFoldersAndFiles();
                                if (count($aFoldersAndFiles)) {
                                    $sContent .= '<p>' . i18n("Copying additional system files", $plugin_name) . '</p>';
                                    foreach ($aFoldersAndFiles as $entry) {
                                        if (substr($entry, -1) == '/') {
                                            
                                            # Entry is a folder, create it
                                            mkdir($cfg['path']['frontend'] . '/' . $entry, 0750);
                                        }
                                        else {
                                            
                                            # Entry is a file, copy it
                                            copy($sPath . 'system/' . $entry, $cfg['path']['frontend'] . '/' . $entry);
                                        }
                                    }
                                }
                                
                                if ($bUpdate) {
                                    
                                    # Check if there is install info and get it
                                    $sql = 'SELECT idplugin, description
                                            FROM ' . $cfg['tab']['plugins'] . '
                                            WHERE ((name="' . $sPlugin . '")
                                               AND (path="' . $sPlugin . '"))';
                                    $db->query($sql);
                                    if ($db->next_record()) {
                                        $idplugin = $db->f('idplugin');
                                        $aDescription = json_decode($db->f('description'), true);
                                        
                                        # Delete the database entries
                                        foreach ($aDescription as $value) {
                                            $sql = 'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE (table_name="' . $value['table'] . '")';
                                            $db->query($sql);
                                            if ($db->next_record()) {
                                                $sql = 'DELETE FROM ' . $value['table'] . ' WHERE (' . $db->f(0) .'=' . $value['id'] . ')';
                                                $db->query($sql);
                                            }
                                        }
                                        
                                        # Delete the record from the _plugins table
                                        $sql = 'DELETE FROM ' . $cfg['tab']['plugins'] . '
                                                WHERE (idplugin=' . $idplugin . ')';
                                        $db->query($sql);
                                    }
                                }
                                
                                # Enter plugin into _plugins
                                $sContent .= '<p>' . sprintf(i18n("Registering plugin %s in database", $plugin_name), $sPlugin) . '</p>';
                                $id = $db->nextid($cfg['tab']['plugins']);
                                $sql = 'INSERT INTO ' . $cfg['tab']['plugins'] . ' (idplugin, name, description, path, installed)
                                        VALUES (' . $id . ', "' . $sPlugin . '", "' . Contenido_Security::escapeDB(json_encode($aDescription), $db) . '", "' . $sPlugin . '", 1)';
                                $db->query($sql);
                                
                                $sContent .= $notification->returnNotification('info', sprintf(i18n("Plugin %s successfully installed", $plugin_name), $sPlugin));
                                $sContent .= '<p>' . sprintf(i18n("(auto redirect in 5 seconds)", $plugin_name), $sPlugin) . '</p>';
                                $sContent .= $sReloadScript;
                            }
                            else {
                                $sContent .= $notification->returnNotification("error", sprintf(i18n("Unable to install %s, missing install information", $plugin_name), $sPlugin));
                                $sContent .= $sReloadScript;
                                removeDir($sPath);
                            }
                        }
                    }
                    else {
                        
                        # Error Missing write permission for folder drugcms/plugins
                        $sContent .= $notification->returnNotification("error", sprintf(i18n("%s is not writable"), basename($cfg['path']['contenido']) . '/' . $cfg['path']['plugins']));
                        $sContent .= $sReloadScript;
                    }
                    break;
                
                case 'InstallModules':
                    $sContent .= $sScrollScript;
                    
                    $sPlugin = $_POST['Key'];
                    $sPath = $cfg['path']['contenido'] . $cfg['path']['plugins'] . $sPlugin . '/modules/';
                    
                    # Get the modules info from the package files
                    $oPlugin = new PluginInfo($sPlugin);
                    $aModules = $oPlugin->getModules();
                    foreach ($aModules as $mod) {
                        
                        # Check if there already is a module with this name
                        $sql = 'SELECT idmod
                                FROM ' . $cfg['tab']['mod'] . '
                                WHERE ((`idclient`=' . $client . ')
                                   AND (`name`="' . $mod['Name'] . '"))
                                ORDER BY idmod';
                        $db->query($sql);
                        if ($db->next_record()) {
                            
                            # Update the first found module for this client
                            $sContent .= '<p>' . sprintf(i18n("Updating module %s", $plugin_name), $mod['Name']) . '</p>';
                            $oMod = new cApiModule($db->f('idmod'));
                            $oMod->importPackage($sPath . $mod['File']);
                        }
                        else {
                            
                            # Install the module
                            $sContent .= '<p>' . sprintf(i18n("Installing module %s", $plugin_name), $mod['Name']) . '</p>';
                            $oMC = new cApiModuleCollection();
                            $oMod = $oMC->create($mod['Name']);
                            $oMod->importPackage($sPath . $mod['File']);
                        }
                    }
                    
                    # If there are additional folders in the plugin's modules folder, copy them to the client's folder
                    $aFoldersAndFiles = $oPlugin->getModulesAdditionalFoldersAndFiles();
                    if (count($aFoldersAndFiles)) {
                        $sContent .= '<p>' . i18n("Copying additional files", $plugin_name) . '</p>';
                        foreach ($aFoldersAndFiles as $entry) {
                            if (substr($entry, -1) == '/') {
                                
                                # Entry is a folder, create it
                                mkdir($cfgClient[$client]['path']['frontend'] . $entry, 0750);
                            }
                            else {
                                
                                # Entry is a file, copy it
                                copy($sPath . $entry, $cfgClient[$client]['path']['frontend'] . $entry);
                            }
                        }
                    }
                    
                    $sContent .= $notification->returnNotification('info', sprintf(i18n("Modules of plugin %s successfully installed for client %s", $plugin_name), $sPlugin, getClientName($client)));
                    $sContent .= '<p>' . sprintf(i18n("(auto redirect in 5 seconds)", $plugin_name), $sPlugin) . '</p>';
                    $sContent .= $sReloadScript;
                    break;
                
                case 'Uninstall':
                    $sContent .= $sScrollScript;
                    $sPlugin = $_POST['Key'];
                    
                    # Get the modules info from the package files
                    $oPlugin = new PluginInfo($sPlugin);
                    $aModules = $oPlugin->getModules();
                    foreach ($aModules as $mod) {
                        $aModInfo = $oPlugin->getModuleInfo($mod['File']);
                        
                        # Get the idmods for this module from the database (all clients)
                        $sql = 'SELECT idmod
                                FROM ' . $cfg['tab']['mod'] . '
                                WHERE (`name`="' . $aModInfo['module'] . '")';
                        $sContent .= '<p>' . sprintf(i18n("Searching for installed modules %s", $plugin_name), $aModInfo['module']) . '</p>';
                        $db->query($sql);
                        while ($db->next_record()) {
                            $aIdMods[] = $db->f('idmod');
                        }
                        
                        if (count($aIdMods)) {
                            # Delete the module from the database (all clients)
                            $sql = 'DELETE FROM ' . $cfg['tab']['mod'] . '
                                    WHERE (idmod IN (' . implode(', ', $aIdMods) . '))';
                            $sContent .= '<p>' . sprintf(i18n("Deleting module %s", $plugin_name), $aModInfo['module']) . '</p>';
                            $db->query($sql);
                            
                            # Delete the module translations from the database (all clients)
                            $sql = 'DELETE FROM ' . $cfg['tab']['mod_translations'] . '
                                    WHERE (idmod IN (' . implode(', ', $aIdMods) . '))';
                            $sContent .= '<p>' . sprintf(i18n("Deleting translation entries for module %s", $plugin_name), $aModInfo['module']) . '</p>';
                            $db->query($sql);
                            
                            # Get all clients
                            $sContent .= '<p>' . sprintf(i18n("Deleting accompanying file for module %s from all clients", $plugin_name), $aModInfo['module']) . '</p>';
                            foreach ($cfgClient as $aClient) {
                                if (is_array($aClient)) {
                                    
                                    # Delete all js files
                                    foreach ($aModInfo['jsfiles'] as $file) {
                                        if (is_file($aClient['js']['path'] . $file)) {
                                            @unlink($aClient['js']['path'] . $file);
                                        }
                                    }
                                    
                                    # Delete all tpl files
                                    foreach ($aModInfo['tplfiles'] as $file) {
                                        if (is_file($aClient['tpl']['path'] . $file)) {
                                            @unlink($aClient['tpl']['path'] . $file);
                                        }
                                    }
                                    
                                    # Delete all css files
                                    foreach ($aModInfo['cssfiles'] as $file) {
                                        if (is_file($aClient['css']['path'] . $file)) {
                                            @unlink($aClient['css']['path'] . $file);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    
                    # If there are additional folders in the plugin's modules folder, delete them in the client's folders
                    $aFoldersAndFiles = $oPlugin->getModulesAdditionalFoldersAndFiles();
                    if (count($aFoldersAndFiles)) {
                        $sContent .= '<p>' . i18n("Deleting additional files", $plugin_name) . '</p>';
                        $aFoldersAndFiles = array_reverse($aFoldersAndFiles);
                        
                        # Get all clients
                        foreach ($cfgClient as $aClient) {
                            if (is_array($aClient)) {
                                foreach ($aFoldersAndFiles as $entry) {
                                    if (substr($entry, -1) == '/') {
                                        
                                        # Entry is a folder, delete it if it's empty and not a system folder
                                        if (!in_array(substr($entry, 0, -1), array('cache', 'css', 'images', 'includes', 'js', 'logs', 'templates', 'upload', 'version'))) {
                                            @rmdir($cfgClient[$client]['path']['frontend'] . $entry);
                                        }
                                    }
                                    else {
                                        
                                        # Entry is a file, delete it
                                        unlink($cfgClient[$client]['path']['frontend'] . $entry);
                                    }
                                }
                            }
                        }
                    }
                    
                    # Get additional database tables to delete
                    $aDbTables = $oPlugin->getAdditionalTablesToDelete();
                    if (is_array($aDbTables)) {
                        $sContent .= '<p>' . sprintf(i18n("Deleting additional database tables for %s", $plugin_name), $sPlugin) . '</p>';
                        foreach ($aDbTables as $DbTable) {
                            if (strpos($DbTable, '*') !== false) {
                                $aTables = array();
                                $sql = 'SELECT TABLE_NAME
                                        FROM information_schema.TABLES
                                        WHERE (TABLE_NAME LIKE "' . str_replace(array('!PREFIX!', '*'), array($cfg['sql']['sqlprefix'], '%'), $DbTable) . '")';
                                $db->query($sql);
                                while ($db->next_record()) {
                                    $aTables[] = $db->f(0);
                                }
                                for ($i = 0, $n = count($aTables); $i < $n; $i ++) {
                                    $sql = 'DROP TABLE `' . $aTables[$i] . '`';
                                    $db->query($sql);
                                }
                            }
                            else {
                                $sql = 'DROP TABLE `' . str_replace('!PREFIX!', $cfg['sql']['sqlprefix'], $DbTable) . '`';
                                $db->query($sql);
                            }
                        }
                    }
                    
                    # If there are additional folders in the plugin's system folder, delete them in the system's folders
                    $aFoldersAndFiles = $oPlugin->getSystemAdditionalFoldersAndFiles();
                    if (count($aFoldersAndFiles)) {
                        $sContent .= '<p>' . i18n("Deleting additional system files", $plugin_name) . '</p>';
                        $aFoldersAndFiles = array_reverse($aFoldersAndFiles);
                        foreach ($aFoldersAndFiles as $entry) {
                            if (substr($entry, -1) == '/') {
                                
                                # Entry is a folder, delete it if it's empty
                                @rmdir($cfg['path']['frontend'] . '/' . $entry);
                            }
                            else {
                                
                                # Entry is a file, delete it
                                unlink($cfg['path']['frontend'] . '/' . $entry);
                            }
                        }
                    }
                    
                    # Get the uninstall info from the database
                    $sContent .= '<p>' . sprintf(i18n("Getting the uninstall info for %s", $plugin_name), $sPlugin) . '</p>';
                    $sql = 'SELECT idplugin, description
                            FROM ' . $cfg['tab']['plugins'] . '
                            WHERE ((name="' . $sPlugin . '")
                               AND (path="' . $sPlugin . '"))';
                    $db->query($sql);
                    if ($db->next_record()) {
                        $idplugin = $db->f('idplugin');
                        $aDescription = json_decode($db->f('description'), true);
                        
                        # Delete the database entries
                        $sContent .= '<p>' . sprintf(i18n("Deleting database records for %s", $plugin_name), $sPlugin) . '</p>';
                        foreach ($aDescription as $value) {
                            $sql = 'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE (table_name="' . $value['table'] . '")';
                            $db->query($sql);
                            if ($db->next_record()) {
                                $sql = 'DELETE FROM ' . $value['table'] . ' WHERE (' . $db->f(0) .'=' . $value['id'] . ')';
                                $db->query($sql);
                            }
                        }
                        
                        # Delete the record from the _plugins table
                        $sContent .= '<p>' . sprintf(i18n("Deleting the uninstall info for %s", $plugin_name), $sPlugin) . '</p>';
                        $sql = 'DELETE FROM ' . $cfg['tab']['plugins'] . '
                                WHERE (idplugin=' . $idplugin . ')';
                        $db->query($sql);
                    }
                    
                    # Remove the plugin's folder
                    $sContent .= '<p>' . sprintf(i18n("Removing folders and files for %s", $plugin_name), $sPlugin) . '</p>';
                    $sPath = $cfg['path']['contenido'] . $cfg['path']['plugins'] . $sPlugin . '/';
                    removeDir($sPath);
                    
                    $sContent .= $notification->returnNotification('info', sprintf(i18n("Plugin %s successfully uninstalled and removed", $plugin_name), $sPlugin));
                    $sContent .= '<p>' . sprintf(i18n("(auto redirect in 5 seconds)", $plugin_name), $sPlugin) . '</p>';
                    $sContent .= $sReloadScript;
                    break;
            }
        }
        break;
    
    case 'repositories':
        if ($perm->have_perm_area_action($plugin_name, 'repositories')) {
            if ((isset($_POST['Name'])) && (strlen($_POST['Name'])) && (isset($_POST['Address'])) && (strlen($_POST['Address']))) {
                
                # Add a new repository
                $sAddress = $_POST['Address'];
                if (strpos($sAddress, '://') === false) {
                    $sAddress = 'http://' . $sAddress;
                }
                if ((substr($sAddress, -1) != '/') && (!in_array(substr($sAddress, -4), array('.php', '.htm', 'html')))) {
                    $sAddress .= '/';
                }
                setProperty(0, 'plugin_repository', $_POST['Name'], 'server', 'host', $sAddress, intval(((isset($_POST['AddressKey'])) ? $_POST['AddressKey'] : 0)));
                setProperty(0, 'plugin_repository', $_POST['Name'], 'server', 'login', $_POST['Login'], intval(((isset($_POST['LoginKey'])) ? $_POST['LoginKey'] : 0)));
                setProperty(0, 'plugin_repository', $_POST['Name'], 'server', 'password', $_POST['Password'], intval(((isset($_POST['PasswordKey'])) ? $_POST['PasswordKey'] : 0)));
            }
            elseif ($sAction == 'Delete') {
                
                # Delete a repository
                $sql = 'DELETE FROM ' . $cfg['tab']['properties'] . '
                        WHERE (`idproperty` IN (' . str_replace('|', ',', $_POST['Key']) . '))';
                $db->query($sql);
            }
            
            $oTpl->set('s', 'FORM_ACTION', 'main.php?area=pim&amp;what=repositories&amp;frame=4&amp;contenido=' . $contenido);
            
            # Texts
            $oTpl->set('s', 'TEXT_REPOSITORIES', i18n("Repositories", $plugin_name));
            $oTpl->set('s', 'TEXT_NAME', i18n("Name", $plugin_name));
            $oTpl->set('s', 'TEXT_ADDRESS', i18n("Server address", $plugin_name));
            $oTpl->set('s', 'TEXT_LOGIN', i18n("Login", $plugin_name));
            $oTpl->set('s', 'TEXT_PASSWORD', i18n("Password", $plugin_name));
            $oTpl->set('s', 'TEXT_EDIT', i18n("Edit", $plugin_name));
            $oTpl->set('s', 'TEXT_EDIT_NOT_POSSIBLE', i18n("Edit not possible", $plugin_name));
            $oTpl->set('s', 'TEXT_DELETE', i18n("Delete", $plugin_name));
            $oTpl->set('s', 'TEXT_DELETE_NOT_POSSIBLE', i18n("Delete not possible", $plugin_name));
            $oTpl->set('s', 'TEXT_NEW', i18n("Add a new repository", $plugin_name));
            $oTpl->set('s', 'TEXT_SAVE', i18n("Save", $plugin_name));
            
            # Get added repositories
            $aReposInfo = getPropertiesByItemtype('plugin_repository', 'itemid', 0, true);
            $aRepos = array();
            for ($i = 0, $n = count($aReposInfo); $i < $n; $i ++) {
                $aRepos[$aReposInfo[$i]['itemid']][$aReposInfo[$i]['name']] = array('idproperty' => $aReposInfo[$i]['idproperty'], 'value' => $aReposInfo[$i]['value']);
            }
            
            # Added repositories
            $sClass = 'even';
            foreach ($aRepos as $itemid => $server) {
                $oTpl->set('d', 'CLASS', $sClass);
                $oTpl->set('d', 'NAME', $itemid);
                $oTpl->set('d', 'ADDRESS', $server['host']['value']);
                $oTpl->set('d', 'LOGIN', $server['login']['value']);
                $oTpl->set('d', 'PASSWORD', ((strlen($server['password']['value'])) ? '******' : '&nbsp;'));
                $oTpl->set('d', 'ADDRESS_ID', $server['host']['idproperty']);
                $oTpl->set('d', 'LOGIN_ID', $server['login']['idproperty']);
                $oTpl->set('d', 'PASSWORD_ID', $server['password']['idproperty']);
                $oTpl->next();
                $sClass = (($sClass == 'odd') ? 'even' : 'odd');
            }
            
            $sContent .= $oTpl->generate($cfg['path']['contenido'] . $cfg['path']['plugins'] . $plugin_name . '/templates/default/repositories.html', true);
            $oTpl->reset();
        }
        break;
}

# Place the content into the main template
$oTpl->set('s', 'CONTENT', $sContent);

# Generate and send output
$oTpl->generate($cfg['path']['contenido'] . $cfg['path']['plugins'] . $plugin_name . '/templates/default/page.html');
?>