<?php
/**
 * Project: 
 * drugCMS Content Management System
 * 
 * Description: 
 * Defines the general drugCMS functions
 *
 * @package    drugCMS Backend includes
 * @version    1.0.0
 * @author     René Mansveld
 * @copyright  Spider IT Deutschland <www.spider-it.de>
 * @license    http://www.drugcms.org/license/license.txt
 * @link       http://www.spider-it.de
 * @link       http://www.drugcms.org
 * @since      file available since drugCMS release 2.0.3
 * 
 *   $Id$:
 */

if(!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}

/**
 * convertCmykJpgToSrgbJpg()
 *
 * Converts JPG images using the CMYK color profile to the sRGB color profile
 *
 * @param string $path Full path to the image file
 * @return void
 */
function convertCmykJpgToSrgbJpg($path) {
    if ((strtolower(substr($path, -4)) == '.jpg') || (strtolower(substr($path, -5)) == '.jpeg')) {
        exec('identify -verbose ' . $path . ' >' . $path . '.txt');
        $tmp = file($path . '.txt');
        unlink($path . '.txt');
        for ($i = 0, $n = count($tmp); $i < $n; $i ++) {
            $a = explode(':', $tmp[$i]);
            if (trim($a[0]) == 'Colorspace') {
                if (strpos($a[1], 'RGB') === false) {
                    exec('convert ' . $path . ' -profile sRGB.icc -colorspace sRGB ' . $path . '.jpg');
                    unlink($path);
                    rename($path . '.jpg', $path);
                }
                break;
            }
        }
    }
}

?>