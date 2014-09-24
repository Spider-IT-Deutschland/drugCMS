<?php
/**
 * Project: 
 * Contenido Content Management System
 *
 * @package    ContenidoBackendArea
 * @version    0.2
 * @author     unknown
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 *
 *   $Id$:
 */
if(!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}


class cSetupResults extends cSetupMask {
	function __construct($step) {
		$this->setHeader(i18n_setup("Results"));
		
		if ((!isset($_SESSION["install_failedchunks"])) && (!isset($_SESSION["install_failedupgradetable"])) && (!isset($_SESSION["configsavefailed"]))) {
			cSetupMask::__construct("templates/setup/forms/setupresults.tpl", $step);
			$this->_oStepTemplate->set("s", "TITLE", i18n_setup("Results"));
			$this->_oStepTemplate->set("s", "DESCRIPTION", i18n_setup("drugCMS was installed and configured successfully on your server."));
            if ($_SESSION["setuptype"] == 'setup') {
                $this->_oStepTemplate->set("s", "LOGIN_INFO", '<p>'.i18n_setup("Please use username <strong>sysadmin</strong> and password <strong>sysadmin</strong> to login into drugCMS's Backend.").'</p>');
            } else {
                $this->_oStepTemplate->set("s", "LOGIN_INFO", '');
            }
			$this->_oStepTemplate->set("s", "CHOOSENEXTSTEP", i18n_setup("Please choose an item to start working") . ':');
			$this->_oStepTemplate->set("s", "FINISHTEXT", i18n_setup("You can now start using drugCMS. <strong>Please delete the folder named 'setup'!</strong>"));
			
			list($root_path, $root_http_path) = getSystemDirectories();
            
			$cHTMLButtonLink = new cHTMLButtonLink($root_http_path."/drugcms/", "Backend - CMS");
			$this->_oStepTemplate->set("s", "BACKEND", $cHTMLButtonLink->render());
			
			if ($_SESSION["setuptype"] == "setup" && $_SESSION["clientmode"] == "CLIENTEXAMPLES") {
				$cHTMLButtonLink = new cHTMLButtonLink($root_http_path."/cms/", "Frontend - Website");
				$this->_oStepTemplate->set("s", "FRONTEND", $cHTMLButtonLink->render());
			} else {
				$this->_oStepTemplate->set("s", "FRONTEND", "");
			}
			
			$cHTMLButtonLink = new cHTMLButtonLink("http://www.drugcms.org/", "drugCMS Website");
			$this->_oStepTemplate->set("s", "WEBSITE", $cHTMLButtonLink->render());			
			
			$cHTMLButtonLink = new cHTMLButtonLink("http://forum.drugcms.org/", "drugCMS Forum");
			$this->_oStepTemplate->set("s", "FORUM", $cHTMLButtonLink->render());

            $cHTMLButtonLink = new cHTMLButtonLink("http://wiki.drugcms.org/", "drugCMS Wiki");
			$this->_oStepTemplate->set("s", "FAQ", $cHTMLButtonLink->render());
		} else {
			cSetupMask::__construct("templates/setup/forms/setupresultsfail.tpl", $step); 
			$this->_oStepTemplate->set("s", "TITLE", i18n_setup("Setup Results"));
			
			list($sRootPath, $rootWebPath) = getSystemDirectories();
			
			if (file_exists($sRootPath . "/drugcms/data/logs/setuplog.txt")) {
				$sErrorLink = '<a target="_blank" href="../drugcms/data/logs/setuplog.txt">setuplog.txt</a>';
			} else {
				$sErrorLink = 'setuplog.txt';	
			}
			
			$this->_oStepTemplate->set("s", "DESCRIPTION", sprintf(i18n_setup("Errors occured during installation. Please take a look at the file %s (located in /drugcms/data/logs/) for more information."), $sErrorLink));
			
			switch ($_SESSION["setuptype"]) {
				case "setup":
					$this->setNavigation("setup1", "");
					break;
				case "upgrade":
					$this->setNavigation("upgrade1", "");
					break;				
				case "migration":	
					$this->setNavigation("migration1", "");
					break;
			}
		}
	}
}
?>