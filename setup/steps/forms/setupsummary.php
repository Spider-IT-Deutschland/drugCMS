<?php
/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * 
 * Requirements: 
 * @con_php_req 5
 *
 * @package    ContenidoBackendArea
 * @version    0.2
 * @author     unknown
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * 
 * 
 * 
 * {@internal 
 *   created  unknown
 *   modified 2008-07-07, bilal arslan, added security fix
 *
 *   $Id$:
 * }}
 * 
 */
if(!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

class cSetupSetupSummary extends cSetupMask {
	function __construct($step, $previous, $next) {
		cSetupMask::__construct("templates/setup/forms/setupsummary.tpl", $step);
		$this->setHeader(i18n_setup("Summary"));
		$this->_oStepTemplate->set("s", "TITLE", i18n_setup("Summary"));
		$this->_oStepTemplate->set("s", "DESCRIPTION", i18n_setup("Please check your settings and click on the next button to start the installation"));
        
		$cHTMLErrorMessageList = new cHTMLErrorMessageList;
		
		switch ($_SESSION["setuptype"]) {
			case "setup":
				$sType = i18n_setup("Setup");
				break;
			case "upgrade":
				$sType = i18n_setup("Upgrade");
				break;
			case "migration":
				$sType = i18n_setup("Migration");
				break;
		}
		
		switch ($_SESSION["configmode"]) {
			case "save":
				$sConfigMode = i18n_setup("Save");
				break;
			case "download":
				$sConfigMode = i18n_setup("Download");
				break;
		}
        
		$messages = array(
            i18n_setup("Installation type").":"   => $sType,
			i18n_setup("Database parameters") . ":" => i18n_setup("Database host") . ": " . $_SESSION["dbhost"] . "<br />" . i18n_setup("Database name") . ": " . $_SESSION["dbname"] . "<br />" .i18n_setup("Database username") . ": " . $_SESSION["dbuser"] . "<br />" . i18n_setup("Database prefix") . ": " . $_SESSION["dbprefix"] . "<br />" . i18n_setup("Database mode") . ": MySQL" . substr($_SESSION["dbmode"], 5)
        );
        
		if ($_SESSION["setuptype"] == "setup") {
            $aChoices = array(	"CLIENTEXAMPLES3"   => i18n_setup("Client with example modules and example content, german and english"),
                                "CLIENTEXAMPLES2"   => i18n_setup("Client with example modules and example content, german only"),
                                "CLIENTEXAMPLES1"   => i18n_setup("Client with example modules and example content, english only"),
                                "CLIENTMODULES"     => i18n_setup("Client with example modules, but without example content"),
                                "CLIENT"		    => i18n_setup("Client without examples"),
                                "NOCLIENT"		    => i18n_setup("Don't create client"));
			$messages[i18n_setup("Client installation").":"] = $aChoices[$_SESSION["clientmode"]];
		}
        
        // additional plugins
        $aPlugins = $this->_getSelectedAdditionalPlugins();
        if (count($aPlugins) > 0) {
            $messages[i18n_setup("Additional Plugins").":"] = implode('<br />', $aPlugins);
        }
		
		$cHTMLFoldableErrorMessages = array();
		
		foreach ($messages as $key => $message) {
			$cHTMLFoldableErrorMessages[] = new cHTMLInfoMessage($key, $message);
		}
		
		$cHTMLErrorMessageList->setContent($cHTMLFoldableErrorMessages);
        $cHTMLErrorMessageList->setStyle('width: 100%; height: 218px; overflow: auto; border: 1px solid black;');
		
		$this->_oStepTemplate->set("s", "CONTROL_SETUPSUMMARY", $cHTMLErrorMessageList->render());
		
		$this->setNavigation($previous, $next);
	}
    
    function _getSelectedAdditionalPlugins() {
        $aPlugins = array();
        if ($_SESSION['plugin_newsletter'] == 'true') {
            $aPlugins[] = i18n_setup('Newsletter');
        }
        if ($_SESSION['plugin_content_allocation'] == 'true') {
            $aPlugins[] = i18n_setup('Content Allocation');
        }
        if ($_SESSION['plugin_mod_rewrite'] == 'true') {
            $aPlugins[] = i18n_setup('Mod Rewrite');
        }
        if ($_SESSION['plugin_db_backup'] == 'true') {
            $aPlugins[] = i18n_setup('DB Backup');
        }
        return $aPlugins;
    }
}
?>