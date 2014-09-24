<?php
/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * Displays all last edited articles of a category
 * 
 * Requirements: 
 * @con_php_req 5.0
 * 
 *
 * @package    Contenido Backend includes
 * @version    1.0.1
 * @author     Timo A. Hummel
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 * 
 * {@internal 
 *   created 2003-05-08
 *   modified 2008-06-27, Frederic Schneider, add security fix
 *
 *   $Id$:
 * }}
 * 
 */

if(!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}

# Generate template
$tpl->reset();

$message = sprintf(i18n("You can find a lot of information and a community forum on the <a href=\"http://www.drugcms.org\" target=\"_blank\">drugCMS Portal</a>"));

$tpl->set('s', 'VERSION', $cfg['version']);
$tpl->set('s', 'PORTAL', $message); 
$tpl->generate($cfg['path']['templates'] . $cfg['templates']['info']);

?>