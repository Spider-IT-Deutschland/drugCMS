<?php
/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * Help system 
 *
 * @package    Contenido Backend scripts
 * @subpackage Helpsystem
 * @version    $Id$:
 * @author     Ortwin Pinke
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 *
 */

/**
 * Security define
 */
if (!defined("CON_FRAMEWORK")) {
    define("CON_FRAMEWORK", true);
}

// Contenido startup process
include_once ('../includes/startup.php');

header("Content-Type: text/javascript");

page_open(array('sess' => 'Contenido_Session',
    'auth' => 'Contenido_Challenge_Crypt_Auth',
    'perm' => 'Contenido_Perm'));

i18nInit($cfg["path"]["contenido"] . $cfg["path"]["locale"], $belang);
page_close();

$baseurl = $cfg["help_url"] . "front_content.php?version=" . $cfg['version'] . "&lang=" . $belang;
if(isset($cfg['help_hash']) && !empty($cfg['help_hash'])) $baseurl .= "&hash=".$cfg['help_hash'];
$baseurl .= "&help=";
?>
//<script type="text/javascript">
// please do not remove script-tag, used to enable syntax-highlighting within some IDE like Netbeans

var help = {
    
    /**
     * Opens a popup-window with help context
     * 
     * @param string area
     */
    popup: function(area) {
        helpwindow = window.open('<?php echo $baseurl; ?>' + area, 'contenido_help', 'height=500,width=600,resizable=yes,scrollbars=yes,location=no,menubar=no,status=no,toolbar=no');
        helpwindow.focus();
    },
    
    /**
     * Set the data-attribute of help link
     * 
     * @param string area
     */
    setArea: function(area) {
        document.getElementById('help').setAttribute('data', area);
    }
}

/**
* @deprecated since 4.8.15, use new help-object instead help.popup(area)
* 
*  @param string path 
 */
function callHelp (path) {
    help.popup(path);
}
//</script>