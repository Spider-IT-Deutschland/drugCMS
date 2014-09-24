<?php
/**
 * Project: 
 * drugCMS Content Management System
 * 
 * Description: 
 * Class for graphics drawings
 * See the accompanying file usage.sample.php for sample usage and documentation
 * 
 * Requirements: 
 * @con_php_req 5.0
 * 
 * 
 * @package     drugCMS Backend classes
 * @version     1.0.0
 * @author      René Mansveld
 * @copyright   Spider IT Deutschland <www.Spider-IT.de>
 * @base        Jack Herrington <www.ibm.com/developerworks/library/os-objorient/>
 * @license     http://www.drugCMS.org/license/Licence.txt
 * @link        http://www.Spider-IT.de
 * @link        http://www.drugCMS.org
 * @since       file available since drugCMS release 2.0.3
 * 
 * {@internal 
 *   created 2014-08-14
 *
 *   $Id$;
 * }}
 *
 */

if (!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}

/**
 * helper function zsort
 */
function zsort($a, $b) {
    return (($a->z() < $b->z()) ? -1 : (($a->z() > $b->z()) ? 1 : 0));
}

/**
 * class GraphicsEnvironment
 * 
 * Class holding layers of graphics objects and colors, managing the output
 */
class GraphicsEnvironment {
    public $vsx;
    public $vsy;
    public $vex;
    public $vey;
    public $width;
    public $height;
    public $widthOut;
    public $heightOut;
    public $gdo;
    public $colors = array();
    public $fonts = array();
    protected $members = array();
    
    public function __construct($width, $height, $vsx, $vsy, $vex, $vey) {
        $this->vsx = $vsx;
        $this->vsy = $vsy;
        $this->vex = $vex;
        $this->vey = $vey;
        $this->widthOut = $width;
        $this->heightOut = $height;
        $this->width = ($vex - $vsx);
        $this->height = ($vey - $vsy);
        $this->gdo = imagecreatetruecolor($this->width, $this->height);
        imagefilledrectangle($this->gdo, 0, 0, $this->width, $this->height, imagecolorallocate($this->gdo, 255, 255, 255));
        $this->addFont('Arial', dirname(__FILE__) . '/arial.ttf');
    }
    
    public function __destruct() {
        foreach ($this->members as $gobj) {
            $gobj->__destruct();
        }
        foreach ($this->colors as $gobj) {
            imagecolordeallocate($this->gdo, $obj);
        }
        imagedestroy($this->gdo);
        unset($this->vsx);
        unset($this->vsy);
        unset($this->vex);
        unset($this->vey);
        unset($this->width);
        unset($this->height);
        unset($this->widthOut);
        unset($this->heightOut);
        unset($this->gdo);
        unset($this->colors);
        unset($this->fonts);
        unset($this->members);
    }
    
    public function left() {
        return $this->vsx;
    }
    
    public function top() {
        return $this->vsy;
    }
    
    public function width() {
        return $this->width;
    }
    
    public function height() {
        return $this->height;
    }
    
    public function widthOut() {
        return $this->widthOut;
    }
    
    public function heightOut() {
        return $this->heightOut;
    }
    
    public function add($member) {
        $this->members[] = $member;
    }
    
    public function addColor($name, $r, $g, $b, $a = 1) {
        $this->colors[$name] = imagecolorallocatealpha($this->gdo, $r, $g, $b, ((1 - $a) * 127));
    }
    
    public function addFont($name, $fontFile) {
        $this->fonts[$name] = $fontFile;
    }
    
    public function getGraphicObject() {
        return $this->gdo;
    }
    
    public function getColor($name) {
        return $this->colors[$name];
    }
    
    public function getFont($name) {
        return $this->fonts[$name];
    }
    
    public function render() {
        usort($this->members, "zsort");
        foreach ($this->members as $gobj) {
            $gobj->render($this);
        }
    }
    
    public function getImage() {
        $img = imagecreatetruecolor($this->widthOut, $this->heightOut);
        imagecopyresampled($img, $this->gdo, 0, 0, 0, 0, $this->widthOut, $this->heightOut, $this->width, $this->height);
        return $img;
    }
    
    public function saveAsPng($filename) {
        $img = imagecreatetruecolor($this->widthOut, $this->heightOut);
        imagecopyresampled($img, $this->gdo, 0, 0, 0, 0, $this->widthOut, $this->heightOut, $this->width, $this->height);
        imagepng($img, $filename);
        imagedestroy($img);
    }
    
    public function tx($x) {
        return ($x + abs($this->vsx));
    }
    
    public function ty($y) {
        return ($y + abs($this->vsy));
    }
}

abstract class GraphicsObject {
    abstract public function __destruct();
    abstract public function render($ge);
    abstract public function z();
}

class GraphicsGroup extends GraphicsObject {
    private $z;
    protected $members = array();
    
    public function __construct($z) {
        $this->z = $z;
    }
    
    public function __destruct() {
        foreach($this->members as $gobj) {
            $gobj->__destruct();
        }
        unset($this->members);
        unset($this->z);
    }
    
    public function add($member) {
        $this->members[] = $member;
    }
    
    public function render($ge) {
        usort($this->members, "zsort");
        foreach($this->members as $gobj) {
            $gobj->render($ge);
        }
    }
    
    public function z() {
        return $this->z;
    }
}

abstract class GraphicsBoxObject extends GraphicsObject {
    protected $color;
    protected $points;
    protected $z;
    
    public function __construct($z, $color, $points = array()) {
        $this->z = $z;
        $this->color = $color;
        $this->points = $points;
    }
    
    public function __destruct() {
        unset($this->color);
        unset($this->points);
        unset($this->z);
    }
    
    public function render($ge) {
        $points = array();
        for ($i = 0, $n = count($this->points); $i < $n; $i += 2) {
            $points[$i] = $ge->tx($this->points[$i]);
            $points[($i + 1)] = $ge->ty($this->points[($i + 1)]);
        }
        $this->draw($points, $ge->getGraphicObject(), $ge->getColor($this->color));
    }
    
    abstract public function draw($points, $gobj, $color);
    
    public function z() {
        return $this->z;
    }
}

class GraphicsLine extends GraphicsBoxObject {
    public function draw($points, $gobj, $color) {
        imageline($gobj, $points[0], $points[1], $points[2], $points[3], $color);
    }
}

class GraphicsRectangle extends GraphicsBoxObject {
    public function draw($points, $gobj, $color) {
        imagerectangle($gobj, $points[0], $points[1], $points[2], $points[3], $color);
    }
}

class GraphicsFilledRectangle extends GraphicsBoxObject {
    public function draw($points, $gobj, $color) {
        imagefilledrectangle($gobj, $points[0], $points[1], $points[2], $points[3], $color);
    }
}

class GraphicsOval extends GraphicsBoxObject {
    public function draw($points, $gobj, $color) {
        $w = ($points[2] - $points[0]);
        $h = ($points[3] - $points[1]);
        imageellipse($gobj, $points[0] + ($w / 2), $points[1] + ($h / 2), $w, $h, $color);
    }
}

class GraphicsFilledOval extends GraphicsBoxObject {
    public function draw($points, $gobj, $color) {
        $w = ($points[2] - $points[0]);
        $h = ($points[3] - $points[1]);
        imagefilledellipse($gobj, $points[0] + ($w / 2), $points[1] + ($h / 2), $w, $h, $color);
    }
}

class GraphicsPolygon extends GraphicsBoxObject {
    public function draw($points, $gobj, $color) {
        imagepolygon($gobj, $points, (count($points) / 2), $color);
    }
}

class GraphicsFilledPolygon extends GraphicsBoxObject {
    public function draw($points, $gobj, $color) {
        imagefilledpolygon($gobj, $points, (count($points) / 2), $color);
    }
}

class GraphicsText extends GraphicsObject {
    protected $align;
    protected $angle;
    protected $color;
    protected $coords;
    protected $text;
    protected $valign;
    protected $z;

    public function __construct($z, $color, $text, $coords = array(), $angle = 0, $align = 'center', $valign = 'center') {
        $this->z = $z;
        $this->color = $color;
        $this->text = $text;
        $this->coords = $coords;
        $this->angle = $angle;
        $this->align = $align; # left, center, or right
        $this->valign = $valign; # top, center, or bottom
    }

    public function __destruct() {
        unset($this->align);
        unset($this->angle);
        unset($this->color);
        unset($this->coords);
        unset($this->text);
        unset($this->valign);
        unset($this->z);
    }
    
    public function render($ge) {
        $font_size = 4;
        # Calculate the approx. width of the text respecting multiline text
        $ts = explode("\n", str_replace(array("\r\n", "\r"), "\n", $this->text));
        $width = 0;
        foreach ($ts as $k => $string) {
            $width = max($width, strlen($string));
        }
        # Create an image with the text sizes
        $width  = (imagefontwidth($font_size) * $width);
        $height = (imagefontheight($font_size) * count($ts));
        $em = imagefontwidth($font_size);
        $el = imagefontheight($font_size);
        $img = imagecreatetruecolor($width, $height);
        # Draw the background transparent
        imagealphablending($img, false);
        $bg = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefilledrectangle($img, 0, 0, $width, $height, $bg);
        imagesavealpha($img, true);
        # Draw the text onto the transparent image
        foreach ($ts as $k => $string) {
            $len = strlen($string);
            switch ($this->align) {
                case 'left':
                    $ypos = 0;
                    break;
                case 'right':
                    $ypos = ($width - (imagefontwidth($font_size) * $len));
                    break;
                case 'center':
                default:
                    $ypos = (($width - (imagefontwidth($font_size) * $len)) / 2);
                    break;
            }
            for ($i = 0; $i < $len; $i ++) {
                $xpos = ($i * $em);
                $ypos = ($k * $el);
                imagechar($img, $font_size, $xpos, $ypos, $string, $ge->getColor($this->color));
                $string = substr($string, 1);     
            }
        }
        # Rotate the image according the set angle
        if ($this->angle != 0) {
            $img2 = imagerotate($img, $this->angle, $bg);
            if ($img2 !== false) {
                imagedestroy($img);
                $img = $img2;
                unset($img2);
            }
        }
        # Copy the image (with transparent background) to the provided graphics object
        $width = imagesx($img);
        $height = imagesy($img);
        switch ($this->align) {
            case 'left':
                $left = 0;
                break;
            case 'right':
                $left = -($width);
                break;
            case 'center':
            default:
                $left = -($width / 2);
                break;
        }
        switch ($this->valign) {
            case 'top':
                $top = 0;
                break;
            case 'bottom':
                $top = -($height);
                break;
            case 'center':
            default:
                $top = -($height / 2);
                break;
        }
        $gobj = $ge->getGraphicObject();
        imagecopyresampled($gobj, $img, ($ge->tx($this->coords[0] + ($left * 2))), ($ge->ty($this->coords[1] + ($top * 2))), 0, 0, ($width * 2), ($height * 2), $width, $height);
        imagedestroy($img);
    }

    public function z() {
        return $this->z;
    }
}

class GraphicsTextTTF extends GraphicsObject {
    protected $align;
    protected $angle;
    protected $color;
    protected $coords;
    protected $fontName;
    protected $fontSize;
    protected $text;
    protected $valign;
    protected $z;
    
    public function __construct($z, $color, $text, $coords = array(), $angle = 0, $fontName = 'Arial', $fontSize = 20, $align = 'center', $valign = 'center') {
        $this->z = $z;
        $this->color = $color;
        $this->text = $text;
        $this->coords = $coords;
        $this->angle = $angle;
        $this->fontName = $fontName;
        $this->fontSize = $fontSize;
        $this->align = $align; # left, center, or right
        $this->valign = $valign; # top, center, or bottom
    }
    
    public function __destruct() {
        unset($this->align);
        unset($this->angle);
        unset($this->color);
        unset($this->coords);
        unset($this->fontName);
        unset($this->fontSize);
        unset($this->text);
        unset($this->valign);
        unset($this->z);
    }
    
    public function render($ge) {
        # Create a graphic in memory to hold the text on a transparent background
        $img = imagecreatetruecolor(($ge->width() * 2), ($ge->height() * 2));
        # Fill the mem graphic with a transparent background
        imagealphablending($img, false);
        $bg = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefilledrectangle($img, 0, 0, ($ge->width() * 2), ($ge->height() * 2), $bg);
        imagesavealpha($img, true);
        # Draw the text onto the transparent image and get the text dimensions
        $coords = imagefttext($img, $this->fontSize, $this->angle, $ge->width(), $ge->height(), $ge->getColor($this->color), $ge->getFont($this->fontName), $this->text);
        # Determin the size of the text
        $width = (max($coords[0], $coords[2], $coords[4], $coords[6]) - min($coords[0], $coords[2], $coords[4], $coords[6]));
        $height = (max($coords[1], $coords[3], $coords[5], $coords[7]) - min($coords[1], $coords[3], $coords[5], $coords[7]));
        # Determin the position to copy the text to the output graphics
        switch ($this->align) {
            case 'left':
                $left = 0;
                break;
            case 'right':
                $left = -($width);
                break;
            case 'center':
            default:
                $left = -($width / 2);
                break;
        }
        switch ($this->valign) {
            case 'top':
                $top = 0;
                break;
            case 'bottom':
                $top = -($height);
                break;
            case 'center':
            default:
                $top = -($height / 2);
                break;
        }
        # Copy the text to the output graphics
        imagecopyresampled($ge->getGraphicObject(), $img, ($ge->tx($this->coords[0]) + $left), ($ge->ty($this->coords[1]) + $top), min($coords[0], $coords[2], $coords[4], $coords[6]), min($coords[1], $coords[3], $coords[5], $coords[7]), $width, $height, $width, $height);
        imagedestroy($img);
    }
    
    public function z() {
        return $this->z;
    }
}
?>