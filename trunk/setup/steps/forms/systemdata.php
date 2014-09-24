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

class cSetupSystemData extends cSetupMask {
	function __construct($step, $previous, $next) {
		cSetupMask::__construct("templates/setup/forms/systemdata.tpl", $step);
		
		list($a_root_path, $a_root_http_path) = getSystemDirectories();
		
		cInitializeArrayKey($_SESSION, "dbhost", "");
		cInitializeArrayKey($_SESSION, "dbuser", "");
		cInitializeArrayKey($_SESSION, "dbname", "");
		cInitializeArrayKey($_SESSION, "dbpass", "");
		cInitializeArrayKey($_SESSION, "dbprefix", "");
		cInitializeArrayKey($_SESSION, "dbmode", "");
		
		if ((file_exists($a_root_path."/drugcms/includes/config.php")) || (file_exists($a_root_path."/contenido/includes/config.php"))) {
			global $cfg; // Avoiding error message about "prepend3.php" on update from V4.x
			
			$contenido_host		= ""; // Just define the variables to avoid warnings in IDE
			$contenido_user		= "";
			$contenido_database = "";
			$contenido_password = "";
			
            if (file_exists($a_root_path."/drugcms/includes/config.php")) {
                @include($a_root_path."/drugcms/includes/config.php");
			} else {
                @include($a_root_path."/contenido/includes/config.php");
            }
            
			$aVars = array(	"dbhost"    => $contenido_host,
							"dbuser"    => $contenido_user,
							"dbname"    => $contenido_database,
							"dbpass"    => $contenido_password,
							"dbprefix"  => $cfg["sql"]["sqlprefix"],
                            "dbmode"    => $cfg["database_extension"]);
							
			foreach ($aVars as $aVar => $sValue) {
				if ($_SESSION[$aVar] == "") {
					$_SESSION[$aVar] = $sValue;	
				}	
			}
		}
		
		$this->setHeader(i18n_setup("Database Parameters"));
		$this->_oStepTemplate->set("s", "TITLE", i18n_setup("Database Parameters"));
				
		switch ($_SESSION["setuptype"]) {
			case "setup":
				$this->_oStepTemplate->set("s", "DESCRIPTION", i18n_setup("Please enter the required database information. If you are unsure about the data, ask your provider or administrator.")." ".i18n_setup("If the database does not exist and your database user has the sufficient permissions, setup will create the database automatically."));
				break;
			case "upgrade":
				$this->_oStepTemplate->set("s", "DESCRIPTION", i18n_setup("Please enter the required database information. If the database data of your previous installation could have been read, the data will be inserted automatically. If you are unsure about the data, please ask your provider or administrator."));
				break;
			case "migration":
				$this->_oStepTemplate->set("s", "DESCRIPTION", i18n_setup("Please enter the required database information. Make sure you copied the data from your old installation (e.g. development or staging system) into a new database. Please enter the parameters of the new database."));
				break;
		}
		
		if ($_SESSION["dbprefix"] == "") {
			$_SESSION["dbprefix"] = "drugcms";
		}
		
		unset($_SESSION["install_failedchunks"]);
		unset($_SESSION["install_failedupgradetable"]);
		unset($_SESSION["configsavefailed"]);
		unset($_SESSION["htmlpath"]);
		unset($_SESSION["frontendpath"]);
				
		$dbhost = new cHTMLTextbox("dbhost", $_SESSION["dbhost"], 30, 255);
		$dbname = new cHTMLTextbox("dbname", $_SESSION["dbname"], 30, 255);
		$dbuser = new cHTMLTextbox("dbuser", $_SESSION["dbuser"], 30, 255);
		
		if ($_SESSION["dbpass"] != "") {
			$mpass = str_repeat("*", strlen($_SESSION["dbpass"]));	
		} else {
			$mpass = "";	
		}
		
		$dbpass = new cHTMLPasswordbox("dbpass", $mpass, 30, 255);
		$dbpass->attachEventDefinition("onchange handler", "onchange", "document.setupform.dbpass_changed.value = 'true';");
		$dbpass->attachEventDefinition("onchange handler", "onkeypress", "document.setupform.dbpass_changed.value = 'true';");
		
		$dbpass_hidden = new cHTMLHiddenField("dbpass_changed", "false");
		
		$dbprefix = new cHTMLTextbox("dbprefix", $_SESSION["dbprefix"], 10, 30, '', ($_SESSION["setuptype"] != 'setup'));
		
        if ((strlen($aVars["dbmode"]) == 0) || ($_SESSION["setuptype"] == 'setup')) {
            if ((hasMySQLExtension()) && (hasMySQLiExtension())) {
                $dbmode = new cHTMLSelectElement("dbmode", 30);
                $dbmode->autoFill(array(array("mysql", "MySQL"), array("mysqli", "MySQLi")));
                if (strlen($_SESSION["dbmode"])) {
                    $dbmode->setDefault($_SESSION["dbmode"]);
                }
                $dbmode_hidden = new cHTMLHiddenField("dbmode_text", "");
            } elseif (hasMySQLExtension()) {
                $dbmode = new cHTMLTextbox("dbmode_text", "MySQL", 30, 255, '', true);
                $dbmode_hidden = new cHTMLHiddenField("dbmode", "mysqli");
            } elseif (hasMySQLiExtension()) {
                $dbmode = new cHTMLTextbox("dbmode_text", "MySQLi", 30, 255, '', true);
                $dbmode_hidden = new cHTMLHiddenField("dbmode", "mysqli");
            }
        } else {
            $dbmode = new cHTMLTextbox("dbmode_text", "MySQL" . substr($_SESSION["dbmode"], 5), 30, 255, '', true);
            $dbmode_hidden = new cHTMLHiddenField("dbmode", $_SESSION["dbmode"], 30, 255, '', true);
        }
		
		$this->_oStepTemplate->set("s", "LABEL_DBHOST", i18n_setup("Database Server (IP or name)"));
		
		if ($_SESSION["setuptype"] == "setup") {
			$this->_oStepTemplate->set("s", "LABEL_DBNAME", i18n_setup("Database Name")."<br>".i18n_setup("(use empty or non-existant database)"));
		} else {
			$this->_oStepTemplate->set("s", "LABEL_DBNAME", i18n_setup("Database Name"));
		}
		
		$this->_oStepTemplate->set("s", "LABEL_DBUSERNAME", i18n_setup("Database Username"));
		$this->_oStepTemplate->set("s", "LABEL_DBPASSWORD", i18n_setup("Database Password"));
		$this->_oStepTemplate->set("s", "LABEL_DBPREFIX", i18n_setup("Table Prefix"));
		$this->_oStepTemplate->set("s", "LABEL_DBMODE", i18n_setup("Access mode"));
	
		$this->_oStepTemplate->set("s", "INPUT_DBHOST", $dbhost->render());
		$this->_oStepTemplate->set("s", "INPUT_DBNAME", $dbname->render());
		$this->_oStepTemplate->set("s", "INPUT_DBUSERNAME", $dbuser->render());
		$this->_oStepTemplate->set("s", "INPUT_DBPASSWORD", $dbpass->render().$dbpass_hidden->render());
		$this->_oStepTemplate->set("s", "INPUT_DBPREFIX", $dbprefix->render());
		$this->_oStepTemplate->set("s", "INPUT_DBMODE", $dbmode->render().$dbmode_hidden->render());
		
		$this->setNavigation($previous, $next);
	}

	function _createNavigation () {
		$link = new cHTMLLink("#");
		
		if ($_SESSION["setuptype"] == "setup")
		{		
			$checkScript = sprintf('var msg = ""; if (document.setupform.dbhost.value == "") { msg += "%s "; } if (document.setupform.dbname.value == "") { msg += "%s "; } if (document.setupform.dbuser.value == "") { msg += "%s "; } if (document.setupform.dbhost.value != "" && document.setupform.dbname.value != "" && document.setupform.dbuser.value != "") { document.setupform.submit(); } else { alert(msg); }',
									html_entity_decode(i18n_setup("You need to enter a database host."), ENT_COMPAT, 'UTF-8'),
									html_entity_decode(i18n_setup("You need to enter a database name."), ENT_COMPAT, 'UTF-8'),
									html_entity_decode(i18n_setup("You need to enter a database user."), ENT_COMPAT, 'UTF-8'));
			$link->attachEventDefinition("pageAttach", "onclick", "document.setupform.step.value = '".$this->_bNextstep."';");
			$link->attachEventDefinition("submitAttach", "onclick", "$checkScript");
		} else {
			$link->attachEventDefinition("pageAttach", "onclick", "document.setupform.step.value = '".$this->_bNextstep."'; document.setupform.submit();");
		}
		
		$nextSetup = new cHTMLAlphaImage;
		$nextSetup->setSrc("../drugcms/images/submit.gif");
		$nextSetup->setMouseOver("../drugcms/images/submit_hover.gif");
		$nextSetup->setClass("button");
		
		$link->setContent($nextSetup);
		
		$this->_oStepTemplate->set("s", "NEXT", $link->render());	
		
		$backlink = new cHTMLLink("#");
		$backlink->attachEventDefinition("pageAttach", "onclick", "document.setupform.step.value = '".$this->_bBackstep."';");
		$backlink->attachEventDefinition("submitAttach", "onclick", "document.setupform.submit();");
		
		$backSetup = new cHTMLAlphaImage;
		$backSetup->setSrc("images/controls/back.gif");
		$backSetup->setMouseOver("images/controls/back.gif");
		$backSetup->setClass("button");
		$backSetup->setStyle("margin-right: 10px");
		$backlink->setContent($backSetup);		
		$this->_oStepTemplate->set("s", "BACK", $backlink->render());
	}			
}
?>