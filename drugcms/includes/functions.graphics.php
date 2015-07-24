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
 * @modified   2015-07-23 René Mansveld :: Added function ScaleImage()
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

/**
 * ScaleImage()
 *
 * Scales and/or zooms an image while keeping it's transparency, saves the new
 * image in the client's cache folder
 *
 * @param string $sImg - Path and file name relative to the client directory
 * @param int $iMaxX - Max width of the new image
 * @param int $iMaxY - Max height of the new image
 * @param bool $bCrop - Image may be cropped (optional)
 * @param bool $bExpand - Image may be expanded (optional)
 * @param float $fGamma - Gamma correction value (1.0 - 1.9) (optional)
 * @param int $iCacheTime - Time setting to overwrite previously generated files (optional)
 * @param bool $bKeepType - New image must be of same type (false generates PNG) (optional)
 * @param bool $bFixedSize - New image must have the exact size with transparent (GIF and PNG) or colored background (optional)
 * @param string $sFixedBG - With $bFixedSize and JPG, this ist the hex color number for the background (optional)
 * @param int $iCropLeft - With $bCrop = true, this is the setting for the left cutting position (-1 = center image) (optional)
 * @param int $iCropTop - With $bCrop = true, this is the setting for the top cutting position (-1 = center image) (optional)
 */
function ScaleImage($sImg, $iMaxX = 0, $iMaxY = 0, $bCrop = false, $bExpand = false, $fGamma = 1.0, $iCacheTime = 10, $bKeepType = false, $bFixedSize = false, $sFixedBG = 'FFFFFF', $iCropLeft = -1, $iCropTop = -1) {
	global $cfgClient, $client, $lang;
    
    $cl = $iCropLeft;
    $ct = $iCropTop;
    
    # Cache
    $md5 = capiImgScaleGetMD5CacheFile($cfgClient[$client]['path']['frontend'] . $sImg, $iMaxX, $iMaxY, $bCrop, $bExpand);
    list($oWidth, $oHeight, $oType) = @getimagesize($cfgClient[$client]['path']['frontend'] . $sImg);
    if (($oType != IMAGETYPE_GIF) && ($oType != IMAGETYPE_JPEG) && ($oType != IMAGETYPE_PNG)) {
        return false;
    }
    if ($bKeepType) {
        switch ($oType) {
            case IMAGETYPE_GIF:
                $cfileName = $md5 . '.gif';
                break;
            case IMAGETYPE_JPEG:
                $cfileName = $md5 . '.jpg';
                break;
            case IMAGETYPE_PNG:
                $cfileName = $md5 . '.png';
                break;
        }
	} else {
        $cfileName = $md5 . '.png';
    }
    $cacheFile  = $cfgClient[$client]['path']['frontend'] . 'cache/' . $cfileName;
	$webFile    = $cfgClient[$client]['path']['htmlpath'] . 'cache/' . $cfileName;
    if (file_exists($cacheFile)) {
        if ($iCacheTime == 0) {
            # File will never be out of date, return it
            return $webFile;
        } elseif ((filemtime($cacheFile) + (60 * $iCacheTime)) < time()) {
            # File is out of date
            unlink($cacheFile);
        } else {
            # File is still valid, return it
            return $webFile;
        }
    }
    
    # If no size is specified, use the original size
    if ($iMaxX <= 0) {
        $iMaxX = $oWidth;
    }
    if ($iMaxY <= 0) {
        $iMaxY = $oHeight;
    }
    
    # Rebuild the image
    $nLeft      = 0;
    $nTop       = 0;
    $nWidth     = 0;
    $nHeight    = 0;
    $faktor     = 1;
    if ($bFixedSize) {
        $iWidth = $iMaxX;
        $iHeight = $iMaxY;
        # Calculate size and position in the new image
        if (($oWidth > $iMaxX) || ($oHeight > $iMaxY) || ($bExpand)) {
            # Image is larger or must be enhanced
            if ($bCrop) {
                $faktor = max(($iMaxX / $oWidth), ($iMaxY / $oHeight));
            } else {
                $faktor = min($iMaxX / $oWidth, $iMaxY / $oHeight);
                $iCropLeft = 0;
                $iCropTop = 0;
            }
            if ($faktor == ($iMaxX / $oWidth)) {
                $nLeft = 0;
                $nWidth = $iMaxX;
                $nHeight = ceil($oHeight * $faktor);
                if ($ct == -1) {
                    $nTop = floor(($iMaxY - $nHeight) / 2);
                }
            } else {
                $nTop = 0;
                $nHeight = $iMaxY;
                $nWidth = ceil($oWidth * $faktor);
                if ($cl == -1) {
                    $nLeft = floor(($iMaxX - $nWidth) / 2);
                }
            }
            if ($bCrop) {
                if ($iCropLeft == -1) {
                    $iCropLeft = 0;
                } else {
                    $nLeft = 0;
                }
                if ($iCropTop == -1) {
                    $iCropTop = 0;
                } else {
                    $nTop = 0;
                }
            }
        } else {
            $nLeft = floor(($iMaxX - $oWidth) / 2);
            $nTop = floor(($iMaxY - $oHeight) / 2);
            $nWidth = $oWidth;
            $nHeight = $oHeight;
        }
    } else {
        # Calculate the size of the new image
        if (($oWidth > $iMaxX) || ($oHeight > $iMaxY) || ($bExpand)) {
            if ($bCrop) {
                $faktor = max($iMaxX / $oWidth, $iMaxY / $oHeight);
            } else {
                $faktor = min($iMaxX / $oWidth, $iMaxY / $oHeight);
                $iCropLeft = 0;
                $iCropTop = 0;
            }
            if ($faktor == ($iMaxX / $oWidth)) {
                $nWidth = $iMaxX;
                $nHeight = ceil($oHeight * $faktor);
                $iWidth = $iMaxX;
                $iHeight = (($nHeight > $iMaxY) ? $iMaxY : $nHeight);
                if ($ct == -1) {
                    $nTop = (($nHeight > $iMaxY) ? floor(($iMaxY - $nHeight) / 2) : 0);
                }
            } else {
                $nHeight = $iMaxY;
                $nWidth = ceil($oWidth * $faktor);
                $iHeight = $iMaxY;
                $iWidth = (($nWidth > $iMaxX) ? $iMaxX : $nWidth);
                if ($cl == -1) {
                    $nLeft = (($nWidth > $iMaxX) ? floor(($iMaxX - $nWidth) / 2) : 0);
                }
            }
            if ($bCrop) {
                if ($iCropLeft == -1) {
                    $iCropLeft = 0;
                } else {
                    $nLeft = 0;
                }
                if ($iCropTop == -1) {
                    $iCropTop = 0;
                } else {
                    $nTop = 0;
                }
            }
        } else {
            # Image is smaler and must not be enlarged
            $iWidth = $nWidth = $oWidth;
            $iHeight = $nHeight = $oHeight;
        }
    }
    if ($iCropLeft < 0) $iCropLeft = 0;
    if ($iCropTop < 0) $iCropTop = 0;
    # Read the original image
    switch ($oType) {
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($cfgClient[$client]['path']['frontend'] . $sImg);
            break;
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($cfgClient[$client]['path']['frontend'] . $sImg);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($cfgClient[$client]['path']['frontend'] . $sImg);
            break;
        default:
            return false;
    }
    # Generate the new image and fill the background
    $nImage = imagecreatetruecolor($iWidth, $iHeight);
    if (($oType == IMAGETYPE_GIF) || ($oType == IMAGETYPE_PNG)) {
        $transIdx = imagecolortransparent($image);
        if ($transIdx >= 0) {
            # A transparent color exists (GIF or PNG8)
            $transColor = imagecolorsforindex($image, $transIdx);
            $transIdx = imagecolorallocate($nImage, $transColor['red'], $transColor['green'], $transColor['blue']);
            imagefilledRectangle($nImage, 0, 0, $iWidth, $iHeight, $transIdx);
            imagecolortransparent($nImage, $transIdx);
        } elseif (($oType == IMAGETYPE_PNG) || (!$bKeepType)) {
            # PNG24 gets a transparent background per Alpha channel
            $oType = IMAGETYPE_PNG;
            imagealphablending($nImage, false);
            $oColor = imagecolorallocatealpha($nImage, 0, 0, 0, 127);
            imagefilledRectangle($nImage, 0, 0, $iWidth, $iHeight, $oColor);
            imagesavealpha($nImage, true);
        } else {
            # Other images get a background color
            $oColor = imagecolorallocate($nImage, hexdec(substr($sFixedBG, 0, 2)), hexdec(substr($sFixedBG, 2, 2)), hexdec(substr($sFixedBG, 4, 2)));
            imagefilledrectangle($nImage, 0, 0, $iWidth, $iHeight, $oColor);
        }
    } else {
        if ($bKeepType) {
            # JPG images get a background color
            $oColor = imagecolorallocate($nImage, hexdec(substr($sFixedBG, 0, 2)), hexdec(substr($sFixedBG, 2, 2)), hexdec(substr($sFixedBG, 4, 2)));
            imagefilledrectangle($nImage, 0, 0, $iWidth, $iHeight, $oColor);
        } else {
            # JPG images get converted to PNG24
            $oType = IMAGETYPE_PNG;
            imagealphablending($nImage, false);
            $oColor = imagecolorallocatealpha($nImage, 0, 0, 0, 127);
            imagefilledRectangle($nImage, 0, 0, $iWidth, $iHeight, $oColor);
            imagesavealpha($nImage, true);
        }
    }
    # Copy the original images scaled into the new one
    imagecopyresampled($nImage, $image, $nLeft, $nTop, floor($iCropLeft / $faktor), floor($iCropTop / $faktor), ($nWidth + 2), ($nHeight + 2), $oWidth, $oHeight);
    # Gamma-correct the new image
    if (($fGamma > 1) && ($fGamma <= 1.9)) {
        imagegammacorrect($nImage, 1, $fGamma);
    }
    # Save the new image
    switch ($oType) {
        case IMAGETYPE_GIF:
            imagegif($nImage, $cacheFile);
            break;
        case IMAGETYPE_JPEG:
            imagejpeg($nImage, $cacheFile);
            break;
        case IMAGETYPE_PNG:
            imagepng($nImage, $cacheFile);
            break;
    }
    # Cleanup
    imagedestroy($image);
    imagedestroy($nImage);
    # Return the new image's path
    return $webFile;
}
?>