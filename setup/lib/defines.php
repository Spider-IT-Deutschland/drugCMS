<?php
/**
 * defines.php
 * 
 * define needed var for drugCMS setup
 * 
 * 
 * @package drugCMS
 * @subpackage Setup
 * @version $Rev$
 * @author 
 * @license http://www.gnu.de/documents/gpl-3.0.de.html GNU General Public License (GPL)
 * @link http://www.drugcms.org drugCMS Portal
 * 
 * $Id$
 */

/**
 * Project:
 * Contenido Content Management System
 *
 * Description:
 *
 * Requirements:
 * @con_php_req 5
 *
 *
 * @package    Contenido Setup
 * @version    0.2.1
 * @author     unknown
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

if (array_key_exists('setuptype', $_SESSION)) {
    switch ($_SESSION['setuptype']) {
        case 'setup':
            define('C_SETUP_STEPS', 8);
            break;
        case 'upgrade':
            define('C_SETUP_STEPS', 7);
            break;
        case 'migration':
            define('C_SETUP_STEPS', 8);
            break;
    }
}

define('C_SETUP_STEPFILE', 'images/steps/s%d.png');
define('C_SETUP_STEPFILE_ACTIVE', 'images/steps/s%da.png');
define('C_SETUP_STEPWIDTH', 28);
define('C_SETUP_STEPHEIGHT', 28);
define('C_SETUP_MIN_PHP_VERSION', '5.2');
define('C_SETUP_VERSION', '2.0.7');
?>