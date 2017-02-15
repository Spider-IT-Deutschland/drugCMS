<?php
/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * Contenido main file
 * 
 * Requirements: 
 * @con_php_req 5.0
 * 
 *
 * @package    Contenido Backend
 * @version    1.2.3
 * @author     Olaf Niemann, Jan Lengowski
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 * 
 * {@internal 
 *   created  2003-01-20
 *   modified 2008-06-16, Holger Librenz, Hotfix: added check for illegal calling
 *   modified 2008-06-25, Timo Trautmann, Contenido Framework Constand added
 *   modified 2008-07-02, Frederic Schneider, add security fix and include security_class
 *   modified 2008-10-22, Oliver Lohkemper, update default-value for leftframewidth from 250px to 245px
 *   modified 2009-10-16, Ortwin Pinke, added rewrite of ampersand in frameset url
 *   modified 2010-05-20, Murat Purc, standardized Contenido startup and security check invocations, see [#CON-307]
 *
 *   $Id$:
 * }}
 * 
 */

if (!defined("CON_FRAMEWORK")) {
    define("CON_FRAMEWORK", true);
}

// Contenido startup process
include_once ('./includes/startup.php');

page_open(
    array('sess' => 'Contenido_Session',
          'auth' => 'Contenido_Challenge_Crypt_Auth',
          'perm' => 'Contenido_Perm'));

i18nInit($cfg["path"]["contenido"].$cfg["path"]["locale"], $belang);

cInclude ("includes", 'cfg_language_de.inc.php');
cInclude ("includes", 'functions.forms.php');

# Create Contenido classes
$db  = new DB();
$tpl = new Template;

# Build the Contenido
# Content area frameset
$tpl->reset();

if (isset($_GET['idartlang'])) {
	$tpl->set('s', 'LEFT', str_replace("&", "&amp;", $sess->url("frameset_left.php?area=$area&idcat=".$_GET['idcat']."&idart=".$_GET['idart']."&idartlang=".$_GET['idartlang'])));
	$tpl->set('s', 'RIGHT', str_replace("&", "&amp;", $sess->url("frameset_right.php?area=$area&idcat=".$_GET['idcat']."&idart=".$_GET['idart']."&idartlang=".$_GET['idartlang']."&changelang=".$_GET['changelang'])));
	$tpl->set('s', 'WIDTH', getEffectiveSetting("backend", "leftframewidth", 245));
}
elseif (isset($_GET["appendparameters"])) {
	$tpl->set('s', 'LEFT', str_replace("&", "&amp;", $sess->url("frameset_left.php?area=$area&&appendparameters=".$_GET["appendparameters"])));
	$tpl->set('s', 'RIGHT', str_replace("&", "&amp;", $sess->url("frameset_right.php?area=$area&changelang=".$_GET['changelang']."&appendparameters=".$_GET["appendparameters"])));
	$tpl->set('s', 'WIDTH', getEffectiveSetting("backend", "leftframewidth", 245));
}
else {
    $parms = urldecode($_GET["appendparameters"]);
	$tpl->set('s', 'LEFT', str_replace("&", "&amp;", $sess->url("frameset_left.php?area=$area")));
	$tpl->set('s', 'RIGHT', str_replace("&", "&amp;", $sess->url("frameset_right.php?area=$area") . $parms));
	$tpl->set('s', 'WIDTH', getEffectiveSetting("backend", "leftframewidth", 245));
}

$tpl->set('s', 'VERSION', 	$cfg['version']);
$tpl->set('s', 'LOCATION',	$cfg['path']['contenido_fullhtml']);

/* Hide menu-frame for some areas */

/* First of all, fetch the meta data of the area table to check if there's a menuless column */
$aMetadata = $db->metadata($cfg["tab"]["area"]);
$bFound = false;

foreach ($aMetadata as $aFieldDescriptor)
{
	if ($aFieldDescriptor["name"] == "menuless")
	{
		$bFound = true;
		break;
	}
}

$menuless_areas = array();

if ($bFound == true)
{
	/* Yes, a menuless column does exist */
	$sql = "SELECT name FROM ".$cfg["tab"]["area"]." WHERE menuless='1'";
	$db->query($sql);

	while ($db->next_record())
	{
	    $menuless_areas[] = $db->f("name");
	}
} else {
	/* No, use old style hard-coded menuless area stuff */
	$menuless_areas = array("str", "logs", "debug", "system");
}

if ( in_array($area, $menuless_areas) || (isset($menuless) && $menuless == 1)) {
    $menuless = true;
    if (isset($_GET["appendparameters"]))
	{
        if ($area == '') {
            $area = $sess_area;
        }
        $appendparameters_tmp = explode('&', $_GET["appendparameters"]);
        $appendparameters = array();
        
        foreach ($appendparameters_tmp as $param) {
            $param_tmp = explode('=', $param);
            $appendparameters[$param_tmp[0]] = $param_tmp[1];
        }
        
        if ($appendparameters['idcat'] > 0) {
            $idcat = $appendparameters['idcat'];
        }
        else {
            $idcat = $_GET['idcat'];
        }
        
        if ($appendparameters['ation'] && $appendparameters['ation'] != '') {
            $action = $appendparameters['action'];
        }
        else {
            $action = $_GET['action'];
        }
        if ($appendparameters['area'] && $appendparameters['area'] != '') {
            $area = $appendparameters['area'];
        }
        else {
            $area = $_GET['area'];
        }

        if ($appendparameters['client'] && $appendparameters['client'] != '') {
            $client = $appendparameters['client'];
        }
        else {
            $client = $_GET['client'];
        }
        
        if ($appendparameters['changeview'] && $appendparameters['changeview'] != '') {
            $changeview = $appendparameters['changeview'];
        }
        elseif ($_GET['changeview'] && $_GET['changeview'] != '') {
            $changeview = $_GET['changeview'];
        } else {
            $changeview = 'edit';
        }
        
		$tpl->set('s', 'FRAME[1]', str_replace("&", "&amp;", $sess->url("main.php?area=".$area."&frame=1&action=".$action."&changeview=".$changeview."&idcat=".$idcat."&client=".$client."&idart=".$appendparameters['idart'])));
		$tpl->set('s', 'FRAME[2]', str_replace("&", "&amp;", $sess->url("main.php?area=".$area."&frame=2&idtpl=1&action=".$action."&changeview=".$changeview."&idcat=".$idcat."&client=".$client."&idart=".$appendparameters['idart'])));
		$tpl->set('s', 'FRAME[3]', str_replace("&", "&amp;", $sess->url("main.php?area=".$area."&frame=3&syncoptions=0&idtpl=1&action=".$action."&changeview=".$changeview."&idcat=".$idcat."&client=".$client."&idart=".$appendparameters['idart'])));
		$tpl->set('s', 'FRAME[4]', str_replace("&", "&amp;", $sess->url("main.php?area=".$area."&frame=4&action=".$action."&changeview=".$changeview."&idcat=".$idcat."&client=".$client."&idart=".$appendparameters['idart'])));
        $menuless = false;
	} else {
		$tpl->set('s', 'FRAME[1]', str_replace("&", "&amp;", $sess->url("main.php?area=$area&frame=1")));
		$tpl->set('s', 'FRAME[2]', str_replace("&", "&amp;", $sess->url("main.php?area=$area&frame=2")));
		$tpl->set('s', 'FRAME[3]', str_replace("&", "&amp;", $sess->url("main.php?area=$area&frame=3")));
		$tpl->set('s', 'FRAME[4]', str_replace("&", "&amp;", $sess->url("main.php?area=$area&frame=4")));
	}
    
}
$tpl->set('s', 'CONTENIDOPATH', $cfg["path"]["contenido_fullhtml"]."favicon.ico");

if ((isset($menuless) && $menuless == 1)) {
    $tpl->generate($cfg['path']['templates'] . $cfg['templates']['frameset_menuless_content']);
} else {
    $tpl->generate($cfg['path']['templates'] . $cfg['templates']['frameset_content']);
}

page_close();

?>