<?php
/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description:
 * 
 *
 * @package    Frontend
 * @subpackage Functions
 * @version    $Rev$
 * @author     unknown
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * 
 *   $Id$:
 */

if(!defined('CON_FRAMEWORK')) {
  die('Illegal call');
}

function getTeaserImage ($text,$return = 'path') {
	$regEx  = "/<img[^>]*?>.*?/i";
    $match  = array();
    preg_match($regEx, $text, $match);
	
	$regEx = "/(src)(=)(['\"]?)([^\"']*)(['\"]?)/i";
    $img = array();
    preg_match($regEx, $match[0], $img);
    
    if ($return == 'path') {
	    return $img[4];
    } else {
    	return $match[0];
    }
}
?>