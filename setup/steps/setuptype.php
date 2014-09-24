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
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

class cSetupTypeChooser extends cSetupMask {
    function __construct() {
        cSetupMask::__construct("templates/setuptype.tpl");
        $this->setHeader(i18n_setup("Please choose your setup type"));
        
        $this->_oStepTemplate->set("s", "SETUPTYPE", ((isset($_SESSION['setuptype'])) ? $_SESSION["setuptype"] : 'setup'));
        
        $link = new cHTMLLink("#");
        $nextSetup = new cHTMLRadiobutton('select', i18n_setup("Install new version"), '', (($_SESSION["setuptype"] == 'setup') || (!isset($_SESSION['setuptype']))));
        $nextSetup->setStyle("margin: 0px 6px 0px 0px; width: 12px;");
        $link->setContent($nextSetup);
        $link->attachEventDefinition("stepAttach", "onclick", "document.setupform.step.value='setup1';");
        $link->attachEventDefinition("setuptypeAttach", "onclick", "document.setupform.setuptype.value='setup';");
        $this->_oStepTemplate->set("s", "OPTION_SETUP", $link->render());
        $this->_oStepTemplate->set("s", "DESCRIPTION_SETUP", sprintf(i18n_setup("This setup type will install %s."), C_SETUP_VERSION)." ".i18n_setup("Please choose this type if you want to start with an empty or an example installation.")."<br />".i18n_setup("Recommended for new projects."));
        
        $link = new cHTMLLink("#");
        $nextSetup = new cHTMLRadiobutton('select', i18n_setup("Upgrade existing installation"), '', ($_SESSION["setuptype"] == 'upgrade'));
        $nextSetup->setStyle("margin: 0px 6px 0px 0px; width: 12px;");
        $link->setContent($nextSetup);
        $link->attachEventDefinition("stepAttach", "onclick", "document.setupform.step.value='upgrade1';");
        $link->attachEventDefinition("setuptypeAttach", "onclick", "document.setupform.setuptype.value='upgrade';");
        $this->_oStepTemplate->set("s", "OPTION_UPGRADE", $link->render());
        $this->_oStepTemplate->set("s", "DESCRIPTION_UPGRADE", sprintf(i18n_setup("This setup type will upgrade your existing installation to %s (ConLite 1.0.x or later/Contenido 4.6.x or later required)."), C_SETUP_VERSION)."<br />".i18n_setup("Recommended for existing projects."));
        
        $link = new cHTMLLink("#");
        $nextSetup = new cHTMLRadiobutton('select', i18n_setup("Migrate existing installation"), '', ($_SESSION["setuptype"] == 'migration'));
        $nextSetup->setStyle("margin: 0px 6px 0px 0px; width: 12px;");
        $link->setContent($nextSetup);
        $link->attachEventDefinition("stepAttach", "onclick", "document.setupform.step.value='migration1';");
        $link->attachEventDefinition("setuptypeAttach", "onclick", "document.setupform.setuptype.value='migration';");
        $this->_oStepTemplate->set("s", "OPTION_MIGRATION", $link->render());
        $this->_oStepTemplate->set("s", "DESCRIPTION_MIGRATION", sprintf(i18n_setup("This setup type will help you migrating an existing installation (Version %s) to another server."), C_SETUP_VERSION)."<br />".i18n_setup("Recommended for moving projects across servers."));
        
        $this->setNavigation('languagechooser', '');
    }
}
$cSetupStep1 = new cSetupTypeChooser;
$cSetupStep1->render();
?>