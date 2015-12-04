<?php
/**
 * Project:
 * drugCMS Content Management System
 *
 * Description:
 * drugCMS Plugin class
 *
 * Requirements:
 * PHP 5.4
 * 
 *
 * @package    drugCMS Backend Plugin Manager
 * @version    1.0.0
 * @author     René Mansveld
 * @copyright  Spider IT Deutschland
 * @license    MIT
 * @link       http://www.spider-it.de
 * @link       http://www.drugcms.org
 *
 * {@internal 
 *   created 2015-11-23
 * }}
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

define('FNM_CASEFOLD', 16);

class PluginInfo {
    
    protected $name = '';
    
    protected $info = '';
    
    public function __construct($pluginName = '') {
        $this->pluginName($pluginName);
    }
    
    public function pluginName($pluginName) {
        $this->name = $pluginName;
        $this->_loadPluginInfo();
    }
    
    public function checkTags($sFilter) {
        $aTags = array();
        if ($this->info != '') {
            if (count((array) $this->info->general->tags)) {
                foreach ($this->info->general->tags->tag as $tag) {
                    $aTags[] = (string) $tag;
                }
            }
        }
        $aFilter = explode(',', $sFilter);
        foreach ($aFilter as $sTag) {
            if (in_array(strtolower($sTag), $aTags)) {
                return true;
            }
        }
        return false;
    }
    
    public function getInfo() {
        if ($this->info != '') {
            $aDescriptions = array();
            if (count((array) $this->info->general->descriptions)) {
                foreach ($this->info->general->descriptions->description as $desc) {
                    $aDescriptions[(string) $desc['lang']] = (string) $desc;
                }
            }
            $aTags = array();
            if (count((array) $this->info->general->tags)) {
                foreach ($this->info->general->tags->tag as $tag) {
                    $aTags[] = (string) $tag;
                }
            }
            $aDependencies = array();
            if (count((array) $this->info->dependencies)) {
                foreach ($this->info->dependencies->plugin as $dep) {
                    $aDependencies[] = array('Name' => (string) $dep->name, 'Version' => (string) $dep->version);
                }
            }
            $aModules = array();
            if (count((array) $this->info->modules)) {
                foreach ($this->info->modules->module as $mod) {
                    $aModules[] = array('Name' => (string) $mod['name'], 'File' => (string) $mod);
                }
            }
            return array('Name' => (string) $this->info->general->name, 'Description' => $aDescriptions, 'Tags' => $aTags, 'Author' => (string) $this->info->general->author, 'Version' => (string) $this->info->general->version, 'Copyright' => (string) $this->info->general->copyright, 'Requirements' => array('php' => (string) $this->info->requirements->php, 'drugcms' => (string) $this->info->requirements->drugcms), 'Dependencies' => $aDependencies, 'Modules' => $aModules);
        }
        else {
            return false;
        }
    }
    
    public function getInstallInfo() {
        if ($this->info != '') {
            $aAreas = array();
            if (count((array) $this->info->drugcms->areas)) {
                foreach ($this->info->drugcms->areas->area as $area) {
                    $aAreas[] = json_decode(json_encode((array) $area), true);
                }
            }
            $aActions = array();
            if (count((array) $this->info->drugcms->actions)) {
                foreach ($this->info->drugcms->actions->action as $action) {
                    $aActions[] = json_decode(json_encode((array) $action), true);
                }
            }
            $aFiles = array();
            if (count((array) $this->info->drugcms->files)) {
                foreach ($this->info->drugcms->files->file as $file) {
                    $aFiles[] = json_decode(json_encode((array) $file), true);
                }
            }
            $aFrameFiles = array();
            if (count((array) $this->info->drugcms->frame_files)) {
                foreach ($this->info->drugcms->frame_files->frame_file as $frame_file) {
                    $aFrameFiles[] = json_decode(json_encode((array) $frame_file), true);
                }
            }
            $sNavMain = (string) $this->info->drugcms->nav_main->location;
            $aNavSubs = array();
            if (count((array) $this->info->drugcms->nav_subs)) {
                foreach ($this->info->drugcms->nav_subs->nav_sub as $nav_sub) {
                    $aNavSubs[] = json_decode(json_encode((array) $nav_sub), true);
                }
            }
            return array('areas' => $aAreas, 'actions' => $aActions, 'files' => $aFiles, 'frame_files' => $aFrameFiles, 'nav_main' => $sNavMain, 'nav_subs' => $aNavSubs);
        }
        else {
            return false;
        }
    }
    
    public function getSystemAdditionalFoldersAndFiles() {
        global $cfg;
        
        $sPath = $cfg['path']['contenido'] . $cfg['path']['plugins'] . $this->name . '/system/';
        $aFoldersAndFiles = $this->_getFoldersAndFilesRecursively($sPath);
        for ($i = (count($aFoldersAndFiles) - 1); $i >= 0; $i --) {
            if (substr($aFoldersAndFiles[$i], -9) == 'index.php') {
                if (file_get_contents($sPath . $aFoldersAndFiles[$i]) == '<?php die("Illegal Call"); ?>') {
                    unset($aFoldersAndFiles[$i]);
                }
            }
        }
        return $aFoldersAndFiles;
    }
    
    public function getModules() {
        if ($this->info != '') {
            $aModules = array();
            if (count((array) $this->info->modules)) {
                foreach ($this->info->modules->module as $mod) {
                    $aModules[] = array('Name' => (string) $mod['name'], 'File' => (string) $mod);
                }
            }
            return $aModules;
        }
        else {
            return false;
        }
    }
    
    public function getModuleInfo($sFile) {
        global $cfg;
        
        $info = simplexml_load_file($cfg['path']['contenido'] . $cfg['path']['plugins'] . $this->name . '/modules/' . $sFile, 'SimpleXMLElement', LIBXML_NOCDATA);
        
        $aModuleInfo = array();
        $aModuleInfo['module'] = (string) $info->module->name;
        if (count((array) $info->jsfiles)) {
            foreach ($info->jsfiles->name as $name) {
                $aModuleInfo['jsfiles'][] = (string) $name;
            }
        }
        if (count((array) $info->tplfiles)) {
            foreach ($info->tplfiles->name as $name) {
                $aModuleInfo['tplfiles'][] = (string) $name;
            }
        }
        if (count((array) $info->cssfiles)) {
            foreach ($info->cssfiles->name as $name) {
                $aModuleInfo['cssfiles'][] = (string) $name;
            }
        }
        if (count((array) $info->layouts)) {
            foreach ($info->layouts->name as $name) {
                $aModuleInfo['layouts'][] = (string) $name;
            }
        }
        return $aModuleInfo;
    }
    
    public function getModulesAdditionalFoldersAndFiles() {
        global $cfg;
        
        $aFoldersAndFiles = $this->_getFoldersAndFilesRecursively($cfg['path']['contenido'] . $cfg['path']['plugins'] . $this->name . '/modules');
        for ($i = (count($aFoldersAndFiles) - 1); $i >= 0; $i --) {
            if (($aFoldersAndFiles[$i] == 'index.php') || ((substr($aFoldersAndFiles[$i], -4) == '.xml') && (strpos($aFoldersAndFiles[$i], '/') === false))) {
                unset($aFoldersAndFiles[$i]);
            }
        }
        return $aFoldersAndFiles;
    }
    
    public function getFoldersAndFilesList() {
        global $cfg;
        
        return $this->_getFoldersAndFilesRecursively($cfg['path']['contenido'] . $cfg['path']['plugins'] . $this->name);
    }
    
    public function getAdditionalTablesToDelete() {
        if ($this->info != '') {
            $aDbTables = array();
            if (count((array) $this->info->dbtables)) {
                foreach ($this->info->dbtables->dbtable as $dbtable) {
                    $aDbTables[] = (string) $dbtable;
                }
                return $aDbTables;
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }
    
    protected function _loadPluginInfo() {
        global $cfg;
        
        if (strlen($this->name)) {
            $this->info = simplexml_load_file($cfg['path']['contenido'] . $cfg['path']['plugins'] . $this->name . '/plugin.xml', 'SimpleXMLElement', LIBXML_NOCDATA);
        }
        else {
            $this->info = '';
        }
    }
    
    protected function _getFoldersAndFilesRecursively($sDir, $sRootDir = '') {
        $aDirs = array();
        $aFiles = array();
        if (substr($sDir, -1) != '/') {
            $sDir .= '/';
        }
        if (strlen($sRootDir) == 0) {
            $sRootDir = $sDir;
        }
        if (is_dir($sDir)) {
            if ($oDir = opendir($sDir)) {
                while (($sFile = readdir($oDir)) !== false) {
                    if (is_dir($sDir . $sFile)) {
                        if (($sFile != '.') && ($sFile != '..')) {
                            $aDirs[] = $sFile;
                        }
                    }
                    elseif (($sDir != $sRootDir) || ($sFile != 'plugin.zip')) {
                        $aFiles[] = str_replace($sRootDir, '', $sDir) . $sFile;
                    }
                }
                closedir($oDir);
            }
        }
        sort($aFiles, SORT_STRING);
        sort($aDirs, SORT_STRING);
        for ($i = 0, $n = count($aDirs); $i < $n; $i ++) {
            $aFiles[] = str_replace($sRootDir, '', $sDir) . $aDirs[$i] . '/';
            $aFiles = array_merge($aFiles, $this->_getFoldersAndFilesRecursively($sDir . $aDirs[$i], $sRootDir));
        }
        return $aFiles;
    }
}
?>