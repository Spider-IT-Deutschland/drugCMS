<?php
 /**
 * Project: 
 * Contenido Content Management System
 * 
 * Description:
 *
 * @package    Setup
 * @version    $Rev$
 * @author     unknown
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 *
 *   $Id$:
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

class cSetupNotInstallable extends cSetupMask {
	function __construct($sReason) {
		cSetupMask::__construct("templates/notinstallable.tpl");
		
        # List all languages for selection
        $ls = new cHtmlLanguageSelector();
        $this->_oTpl->set('s', 'STEPS', $ls->render());
		$this->_oStepTemplate->set("s", "LANG", $_SESSION['language']);
        i18nRegisterDomain("setup", 'locale/');
        if (array_key_exists('language', $_SESSION)) {
            i18nInit('locale/', $_SESSION['language']);
        }
		
		$this->setHeader(sprintf(i18n_setup('Version %s'), C_SETUP_VERSION));
        $this->_oStepTemplate->set("s", "TITLE", i18n_setup("Welcome to setup!"));
		$this->_oStepTemplate->set("s", "ERRORTEXT", i18n_setup("Setup not runnable"));
		if ($sReason === 'session_use_cookies') {
            $this->_oStepTemplate->set("s", "REASONTEXT", i18n_setup("You need to set the PHP configuration directive 'session.use_cookies' to 1 and enable cookies in your browser. This setup won't work without that."));
		} elseif ($sReason === 'database_extension') {
            $this->_oStepTemplate->set("s", "REASONTEXT", i18n_setup("Couldn't detect neither MySQLi extension nor MySQL extension. You need to enable one of them in the PHP configuration (see dynamic extensions section in your php.ini). drugCMS won't work without that."));
		} elseif ($sReason === 'php_version') {
            $this->_oStepTemplate->set("s", "REASONTEXT", sprintf(i18n_setup("Unfortunately your webserver doesn't match the minimum requirement of PHP %s or higher. Please install PHP %s or higher and then run the setup again."), C_SETUP_MIN_PHP_VERSION, C_SETUP_MIN_PHP_VERSION));
        } else {
            // this should not happen
            $this->_oStepTemplate->set("s", "REASONTEXT", i18n_setup("Reason unknown"));
        }
	}
}

global $sNotInstallableReason;

$cNotInstallable = new cSetupNotInstallable($sNotInstallableReason);
$cNotInstallable->render();

session_unset();

die();
?>