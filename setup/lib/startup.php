<?php
/**
 * Project:
 * Contenido Content Management System
 *
 * Description:
 * Main Contenido setup bootstrap file.
 *
 * Requirements:
 * @con_php_req 5.0
 *
 *
 * @package    Contenido setup bootstrap
 * @version    0.0.1
 * @author     Murat Purc <murat@purc.de>
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.8.15
 *
 *   $Id$
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

// uncomment this lines during development if needed
error_reporting(E_ALL ^E_NOTICE);
ini_set("display_errors", true);
ini_set("log_errors", true);
ini_set("error_log", "../drugcms/data/logs/setup_errorlog.txt");

header('Content-Type: text/html; charset=UTF-8');


// Check version in the 'first' line, as class.security.php uses
// PHP5 object syntax not compatible with PHP < 5.2
if (version_compare(PHP_VERSION, '5.2.0', '<')) {
    die("You need PHP >= 5.2.0 for drugCMS. Sorry, even the setup doesn't work otherwise. Your version: " . PHP_VERSION . "\n");
}

// include security class and check request variables
include_once(C_CONTENIDO_PATH . 'classes/class.security.php');
Contenido_Security::checkRequests();

/**
 * Setup file inclusion
 *
 * @param  string  $filename
 * @return void
 */
function checkAndInclude($filename) {
    if (file_exists($filename) && is_readable($filename)) {
            require_once($filename);
    } else {
            echo "<pre>";
            echo "Setup was unable to include neccessary files. The file $filename was not found. Solutions:\n\n";
            echo "- Make sure that all files are correctly uploaded to the server.\n";
            echo "- Make sure that include_path is set to '.' (of course, it can contain also other directories). Your include path is: ".ini_get("include_path")."\n"; 
            echo "</pre>";
    }
}

session_start();

// includes
checkAndInclude('lib/defines.php');
checkAndInclude('../pear/HTML/Common2.php');
checkAndInclude(C_CONTENIDO_PATH . 'classes/class.htmlelements.php');
checkAndInclude(C_CONTENIDO_PATH . 'includes/functions.i18n.php');
checkAndInclude('lib/class.setupcontrols.php');
checkAndInclude('lib/functions.filesystem.php');
checkAndInclude('lib/functions.environment.php');
checkAndInclude('lib/functions.safe_mode.php');
checkAndInclude('lib/functions.mysql.php');
checkAndInclude('lib/functions.phpinfo.php');
checkAndInclude('lib/functions.system.php');
checkAndInclude('lib/functions.libraries.php');
checkAndInclude('lib/functions.sql.php');
checkAndInclude('lib/functions.setup.php');
checkAndInclude('lib/class.template.php');
checkAndInclude('lib/class.setupmask.php');
?>