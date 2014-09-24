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

$lang = $_SESSION['language'];
session_unset();
if (strlen($lang)) {
    $_POST['language'] = $lang;
}

class cSetupLanguageChooser extends cSetupMask {
	function __construct() {
        cSetupMask::__construct("templates/languagechooser.tpl");
		
        # List all languages for selection
        $ls = new cHtmlLanguageSelector();
        $this->_oTpl->set('s', 'STEPS', $ls->render());
		$this->_oStepTemplate->set("s", "LANG", $_SESSION['language']);
        i18nRegisterDomain("setup", 'locale/');
        if (array_key_exists('language', $_SESSION)) {
            i18nInit('locale/', $_SESSION['language']);
        }
        
		$this->setHeader(sprintf(i18n_setup('Version %s'), C_SETUP_VERSION));
        $this->_oStepTemplate->set("s", "WELCOME", i18n_setup("Welcome to setup!"));
        $this->_oStepTemplate->set("s", "HINT", i18n_setup("This application will guide you trough the setup process."));
		$this->_oStepTemplate->set("s", "HINT_LANG", i18n_setup("Please choose your prefered language for the setup (if it's not this one) and click 'next' to start it."));
        
		$this->setNavigation('', 'setuptype');
	}
}

$cSetupStep1 = new cSetupLanguageChooser;
$cSetupStep1->render();
?>