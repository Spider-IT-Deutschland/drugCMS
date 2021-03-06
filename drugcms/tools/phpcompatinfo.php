<?php
/**
 * Project:
 * Contenido Content Management System
 *
 * Description:
 * Detection of PHP dependency in Contenido.
 *
 * Uses PEAR package PHP_CompatInfo, see http://pear.php.net/package/PHP_CompatInfo
 * Requires the PEAR package PHP_CompatInfo!
 *
 * PHP_CompatInfo parses the complete contenido project folder recursively and 
 * collects all dependency informations.
 *
 * Usage:
 * ------
 * Call this script from command line as follows:
 *     $ php phpcompat.php
 *
 * NOTE:
 * Pass the output into a file using following commnad:
 *     $ php phpcompat.php > phpcompat_info.txt
 *
 * Requirements:
 * @con_php_req 5.0
 *
 * @package    Contenido Tools
 * @version    0.0.1
 * @author     Murat Purc <murat@purc.de>
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release 4.8.15
 *
 * {@internal
 *   created  2011-02-23
 *   $Id$:
 * }}
 *
 */


// allow execution only thru cli mode
(substr(PHP_SAPI, 0, 3) == 'cli') or die('Illegal call');


################################################################################
##### Initialization/Settings

// create a page context class, better than spamming global scope
$context = new stdClass();

// contenido installation path (folder which contains "cms", "conlib", "contenido", "docs", "pear", "setup", etc...)
$context->contenidoInstallPath = str_replace('\\', '/', realpath(dirname(__FILE__) . '/../../')) . '/';


################################################################################
##### Proccess

require_once 'PHP/CompatInfo.php';
$context->info = new PHP_CompatInfo();
$context->info->parseDir($context->contenidoInstallPath);


################################################################################
##### Shutdown

unset($context);