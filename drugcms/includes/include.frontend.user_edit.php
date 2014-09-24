<?php
/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * Frontend user editor
 *
 * @package    Contenido Backend includes
 * @version    1.1.10
 * @author     unknown
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 *
 *   $Id$:
 */

if(!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}

$oPage = new cPage();
$oPage->setHtml5();
          
$oFeUsers = new FrontendUserCollection();
if (is_array($cfg['plugins']['frontendusers'])) {
    foreach ($cfg['plugins']['frontendusers'] as $plugin)	{
        plugin_include("frontendusers", $plugin."/".$plugin.".php");
    }
}

$oFeUser = $feuser = new FrontendUser($idfrontenduser);
$oFEGroupMemberCollection = new FrontendGroupMemberCollection;
$oFEGroupMemberCollection->setWhere('idfrontenduser', $idfrontenduser);
$oFEGroupMemberCollection->addResultField('idfrontendgroup');
$oFEGroupMemberCollection->query();

# Fetch all groups the user belongs to (no goup, one group, more than one group).
# The array $aFEGroup can be used in frontenduser plugins to display selfdefined user properties group dependent.
$aFEGroup = array();

while($oFEGroup = $oFEGroupMemberCollection->next()) {
    $aFEGroup[] = $oFEGroup->get("idfrontendgroup");
}

if ($action == "frontend_create" && $perm->have_perm_area_action("frontend", "frontend_create")) {
		$oFeUser = $oFeUsers->create(" ".i18n("-- new user --"));
		$idfrontenduser = $oFeUser->get("idfrontenduser");
}

if ($idfrontenduser && $action != '') {
    $sReloadScript = "<script type=\"text/javascript\">
                         var left_bottom = parent.parent.frames['left'].frames['left_bottom'];
                         if (left_bottom) {
                             var href = left_bottom.location.href;
                             href = href.replace(/&frontenduser.*/, '');
                             left_bottom.location.href = href+'&frontenduser='+".$idfrontenduser.";
                             top.content.left.left_top.refresh();
                         }
                     </script>";
} else {
    $sReloadScript = "";
}

$sFormCheck = '
    <script type="text/javascript">
        $(document).ready(function() {
            
        });

        var defaultName = "'.  i18n("-- new user --").'";
        var PwEmpty = '.(($oFeUser->get("password") == "d41d8cd98f00b204e9800998ecf8427e")?"true":"false").';
        function checkUserForm() {
            if(document.getElementById("m1").value.match(/defaultName/i)) {
                alert("Default username, please use a new one!");
                return false;
            }
            if(PwEmpty == true && document.getElementById("m2").value == "") {
                alert("Password not set yet! You have to set a new password!");
                return false;
            }
            return true;
        }
    </script>
';

$oPage->addScript("formcheck", $sFormCheck);

// Delete FE-User
if ($action == "frontend_delete" && $perm->have_perm_area_action("frontend", "frontend_delete")) {
    $oFeUsers->delete($idfrontenduser);
    $iterator = $_cecRegistry->getIterator("Contenido.Permissions.FrontendUser.AfterDeletion");
    while ($chainEntry = $iterator->next()) {
        $chainEntry->execute($idfrontenduser);
    }
    
    $idfrontenduser = 0;
    $oFeUser = new FrontendUser();
    $oPage->addScript('reload', $sReloadScript);
}

if ($oFeUser->virgin == false && $oFeUser->get("idclient") == $client) {
    // check and save changes
    if ($action == "frontend_save_user") {
        $oPage->addScript('reload', $sReloadScript);
        $messages = array();
        $bStore = true;
        $initname = i18n("-- new user --");
        $bNewUser = (strstr($username, $initname) === false)?false:true;
        if(empty($username) || $bNewUser) {
            $messages[] = i18n("Username empty or not set! Please choose one.");
            $bStore = false;
        }
        // check for empty password
        if($bStore && empty($newpd) && $oFeUser->get("password") == "d41d8cd98f00b204e9800998ecf8427e") {
            $messages[] = i18n("Password not set right now!");
            $bStore = false;
        }
        if ($bStore && $oFeUser->get("username") != stripslashes($username)) {
            $oFeUsers->select("username = '".urlencode($username)."' and idclient='$client'");
            if ($oFeUsers->next()) {
                $messages[] = i18n("Could not set new username: Username already exists");
            } else {
                $oFeUser->set("username", stripslashes($username));
            }
        }
        
        if ($newpd != $newpd2) {
            $messages[] = i18n("Could not set new password: Passwords don't match");
        } else {
            if ($newpd != "") {
                $oFeUser->set("password", $newpd);
            }
        }
        
        $oFeUser->set("active", $active);

        /* Check out if there are any plugins */
        if (is_array($cfg['plugins']['frontendusers'])) {
            foreach ($cfg['plugins']['frontendusers'] as $plugin) {
                if (function_exists("frontendusers_".$plugin."_wantedVariables") &&
                        function_exists("frontendusers_".$plugin."_store")) {
                    # check if user belongs to a specific group 
                    # if true store values defined in frontenduser plugin
                    if (function_exists("frontendusers_".$plugin."_checkUserGroup")) {
                        $bCheckUserGroup = call_user_func("frontendusers_".$plugin."_checkUserGroup");
                    } else {
                        $bCheckUserGroup = true;
                    }
                    if ($bCheckUserGroup) {
                        $wantVariables = call_user_func("frontendusers_".$plugin."_wantedVariables");
                        if (is_array($wantVariables)) {
                            $varArray = array();
                            foreach ($wantVariables as $value) {
                                $varArray[$value] = stripslashes($GLOBALS[$value]);
                            }
                        }
                        $store = call_user_func("frontendusers_".$plugin."_store", $varArray);
                    }
                }
            }
        }
        if($bStore) $oFeUser->store();
    }
    
    if (count($messages) > 0)	{
        $notis = $notification->returnNotification("warning", implode("<br>", $messages)) . "<br>";
    }
	
	
	$form = new UI_Table_Form("properties");
 $form->setSubmitJS("return checkUserForm();");
	$form->setVar("frame", $frame);
    $form->setVar("area", $area);
    $form->setVar("action", "frontend_save_user");
    $form->setVar("idfrontenduser", $idfrontenduser);

	$form->addHeader(i18n("Edit user"));
	
	$username = new cHTMLTextbox("username", ((isset($_POST['username']) && $bStore === false)?$_POST['username']:$oFeUser->get("username")),40);
	$newpw    = new cHTMLPasswordBox("newpd","",40);
	$newpw2   = new cHTMLPasswordBox("newpd2","",40);
	$active   = new cHTMLCheckbox("active","1");
	$active->setChecked($oFeUser->get("active"));
	
	$form->add(i18n("User name"), $username->render());
	$form->add(i18n("New password"), $newpw->render());
	$form->add(i18n("New password (again)"), $newpw2->render());
	$form->add(i18n("Active"), $active->toHTML(false));
	
	$pluginOrder = trim_array(explode(",",getSystemProperty("plugin", "frontendusers-pluginorder")));
	
	/* Check out if there are any plugins */
	if (is_array($pluginOrder)) {
     foreach ($pluginOrder as $plugin) {
         if (function_exists("frontendusers_".$plugin."_getTitle") &&
                 function_exists("frontendusers_".$plugin."_display")) {
             # check if user belongs to a specific group 
             # if true display frontenduser plugin
             if (function_exists("frontendusers_".$plugin."_checkUserGroup")) {
                 $bCheckUserGroup = call_user_func("frontendusers_".$plugin."_checkUserGroup");
             } else {
                 $bCheckUserGroup = true;
             }
             
             if ($bCheckUserGroup) {
                 $plugTitle = call_user_func("frontendusers_".$plugin."_getTitle");
                 $display = call_user_func("frontendusers_".$plugin."_display", $oFeUser);

                 if (is_array($plugTitle) && is_array($display)) {
                     foreach ($plugTitle as $key => $value) {
                         $form->add($value, $display[$key]);
                     }
                 } else {
                     if (is_array($plugTitle) || is_array($display)) {
                         $form->add(i18n("WARNING"), sprintf(i18n("The plugin %s delivered an array for the displayed titles, but did not return an array for the contents."), $plugin));
                     } else {
                         $form->add($plugTitle, $display);
                     }
                 }
             }
         }
     }
     
     $arrGroups = $oFeUser->getGroupsForUser();
     
     if(count($arrGroups) > 0) {
        $aMemberGroups = array();
         
        foreach($arrGroups as $iGroup) {
            $oMemberGroup = new FrontendGroup($iGroup);
            $aMemberGroups[] = $oMemberGroup->get("groupname");
        }
        asort($aMemberGroups);

        $sTemp = implode('<br/>', $aMemberGroups);
    } else {
        $sTemp = i18n("none");
    }
    
    $form->add(i18n("Group membership"), $sTemp ); 
    $form->add(i18n("Author"), $classuser->getUserName($oFeUser->get("author")) . " (". $oFeUser->get("created").")" ); 
    $form->add(i18n("Last modified by"), $classuser->getUserName($oFeUser->get("modifiedby")). " (". $oFeUser->get("modified").")" );
}
//$sDialog = '<div id="dialog" title="Dialog Title">I\'m in a dialog</div>';
$sDialog = "";
$oPage->setContent(	$notis .	$form->render(true). $sDialog);
$oPage->addScript('reload', $sReloadScript);
} else {
	$oPage->setContent("");	
}
$oPage->render();
?>
