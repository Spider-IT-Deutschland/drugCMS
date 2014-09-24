<?php
/**
 * File:
 * class.phpmailer.php
 *
 * Project:
 * Contenido Content Management System
 *
 * Description:
 *  New wrapper for PHPMailer5 lib in external folder
 *
 * @package     PHPMailer5
 * @version     $Rev$
 * @author      Ortwin Pinke
 * @link        http://www.contenido.org
 *
 * $Id$
 */

// security check
defined('CON_FRAMEWORK') or die('Illegal call');

cInclude('external', 'PHPMailer/class.phpmailer.php');


class PHPMailer extends PHPMailer5 {
    
    public function __construct($exceptions = false) {
        parent::__construct($exceptions);
    }
}
?>