<?php
/**
 * Project: 
 * drugCMS Content Management System
 * 
 * Description: 
 * Upload files
 * 
 * Requirements: 
 * @con_php_req 5.0
 * 
 *
 * @package     drugCMS Backend includes
 * @author      René Mansveld (R.Mansveld@Spider-IT.de)
 * @copyright   Spider IT Deutschland <www.spider-it.de>
 * @license     http://www.contenido.org/license/LIZENZ.txt
 * @link        http://www.spider-it.de
 * @link        http://www.drugcms.org
 * @since       file available since contenido release <= 4.6
 *              redeveloped for drugCMS 2.0.7
 * 
 * {@internal 
 *   created 2003-12-30
 *   modified 2008-06-27, Frederic Schneider (4fb), add security fix
 *   modified 2010-09-20, Dominik Ziegler (4fb), added path to error message when directory is not writable - CON-319
 *   redeveloped 2015-07-20, René Mansveld (sit), now uses a mass upload template and pre-settings
 * }}
 * 
 */

if(!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}


cInclude("includes", "functions.upl.php");

/*
$page = new UI_Page;

if ((is_writable($cfgClient[$client]["upl"]["path"].$path) || is_dbfs($path)) && (int) $client > 0)
{
    $form = new UI_Table_Form("properties");
    $form->setVar("frame", $frame);
    $form->setVar("area", "upl");
    $form->setVar("path", $path);
    $form->setVar("file", $file);
    $form->setVar("action", "upl_upload");
    $form->setVar("appendparameters", $_REQUEST["appendparameters"]);
    
    $form->addHeader(i18n("Upload"));
	
    if (is_dbfs($path))
		$mpath = $path."/";	
	else 
		$mpath = "upload/".$path;
		
    $sDisplayPath = generateDisplayFilePath($mpath, 85);
    $form->add(i18n("Path:"), $sDisplayPath);
	
    $uplelement = new cHTMLUpload("file[]",40);
    $num_upload_files = getEffectiveSetting('backend','num_upload_files',10);
    $form->add(i18n("Upload files"), str_repeat($uplelement->render()."<br>"	,$num_upload_files));
    
    $page->setContent($form->render());
} else {
	$page->setContent($notification->returnNotification("error", i18n("Directory not writable") . ' (' . $cfgClient[$client]["upl"]["path"].$path . ')'));
}	
$page->render();
*/

$oTpl = new Template();

$oTpl->set('s', 'SCALE_SETTINGS', i18n("Image scaling pre-settings"));
$oTpl->set('s', 'ADD_FILES', i18n("Add files"));
$oTpl->set('s', 'START_UPLOAD', i18n("Start upload"));
$oTpl->set('s', 'CANCEL_UPLOAD', i18n("Clear list"));
$oTpl->set('s', 'PROCESSING', i18n("Processing"));
$oTpl->set('s', 'START', i18n("Start"));
$oTpl->set('s', 'CANCEL', i18n("Entfernen"));
$oTpl->set('s', 'ERROR', i18n("Error"));
$oTpl->set('s', 'DELETE', i18n("Delete"));
$oTpl->set('s', 'REMOVE', i18n("Remove"));
$oTpl->set('s', 'DONE', i18n("Upload done, please check results"));
$oTpl->set('s', 'TEXT_MEDIANAME', i18n("Medianame"));
$oTpl->set('s', 'TEXT_DESCRIPTION', i18n("Description"));
$oTpl->set('s', 'TEXT_KEYWORDS', i18n("Keywords"));
$oTpl->set('s', 'TEXT_INTERNAL_NOTE', i18n("Internal notes"));
$oTpl->set('s', 'TEXT_COPYRIGHT', i18n("Copyright"));

$iDefault = intval(getEffectiveSetting('image-upload', 'default', '-1'));
for ($i = 0; $i < 100; $i ++) {
    # Get the language dependant name of the option
    $sName = getEffectiveSetting('image-upload-' . $i, 'name-' . $lang);
    if (strlen($sName) == 0) {
        # Get the language independant name of the option
        $sName = getEffectiveSetting('image-upload-' . $i, 'name');
    }
    if (strlen($sName)) {
        # We have a name, create the option for selection
        $oTpl->set('d', 'NAME', $sName);
        $sDefaults = getEffectiveSetting('image-upload-' . $i, 'width') . ';' .
                     getEffectiveSetting('image-upload-' . $i, 'height') . ';' .
                     ((in_array(getEffectiveSetting('image-upload-' . $i, 'crop', '0'), array('true', '1'))) ? '1' : '0') . ';' .
                     ((in_array(getEffectiveSetting('image-upload-' . $i, 'expand', '0'), array('true', '1'))) ? '1' : '0') . ';' .
                     ((in_array(getEffectiveSetting('image-upload-' . $i, 'fixed', '0'), array('true', '1'))) ? '1' : '0') . ';' .
                     getEffectiveSetting('image-upload-' . $i, 'color', 'FFFFFF') . ';' .
                     getEffectiveSetting('image-upload-' . $i, 'gamma', '0');
        $oTpl->set('d', 'VALUE', $sDefaults);
        $oTpl->set('d', 'DEFAULT', (($i == $iDefault) ? ' checked="checked"' : ''));
        $oTpl->next();
        # If we have a default option, we must set it's values at startup
        if ($i == $iDefault) {
            $oTpl->set('s', 'SET_DEFAULTS', 'setDefaults(\'' . $sDefaults . '\');');
        }
    }
}
if ($iDefault == -1) {
    $oTpl->set('s', 'SET_DEFAULTS', '');
}
if (is_dbfs($path)) {
    $mpath = $path."/";	
} else {
    $mpath = "upload/".$path;
}
$oTpl->set('s', 'DISPLAY_PATH', i18n("Path") . ': ' . generateDisplayFilePath($mpath, 85));
$oTpl->set('s', 'BACKEND', $cfg['path']['contenido_fullhtml']);
$oTpl->set('s', 'FRAME', $frame);
$oTpl->set('s', 'PATH', $path);
$oTpl->set('s', 'FILE', $file);
$oTpl->set('s', 'APPEND_PARAMETERS', $_REQUEST["appendparameters"]);
$oTpl->set('s', 'CONTENIDO', $contenido);

$oTpl->generate($cfg['path']['templates'] . $cfg['templates']['upl_files_upload'], false, false);
?>