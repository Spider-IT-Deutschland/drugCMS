<?php
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
 * @package    ContenidoBackendArea
 * @version    0.1
 * @author     unknown
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * 
 * 
 * 
 * {@internal 
 *   created  unknown
 *   modified 2008-07-07, bilal arslan, added security fix
 *
 *   $Id$:
 * }}
 * 
 */
if(!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

class cHTMLAlphaImage extends cHTMLImage {
    var $_sClickImage;
    var $_sMouseoverClickImage;
    var $_sMouseoverSrc;

    function __construct() {
        cHTMLImage::__construct();
    }
    
    function setMouseover($sMouseoverSrc) {
        $this->_sMouseoverSrc = $sMouseoverSrc;    
    }

    function setSwapOnClick($sClickSrc, $sMouseoverClickSrc) {
        $this->_sClickImage = $sClickSrc;
        $this->_sMouseoverClickImage = $sMouseoverClickSrc;
    }    
    
    function toHTML() {
        $alphaLoader = 'progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'%s\')';
        $imageLocations = "this.imgnormal = '%s'; this.imgover = '%s'; this.clickimgnormal = '%s'; this.clickimgover = '%s';";
        
        $this->attachStyleDefinition("filter", sprintf($alphaLoader, $this->_src));
        $this->attachEventDefinition("imagelocs", "onload", sprintf($imageLocations, $this->_src, $this->_sMouseoverSrc, $this->_sClickImage, $this->_sMouseoverClickImage));
        $this->attachEventDefinition("swapper", "onload", 'if (!this.init) {IEAlphaInit(this); IEAlphaApply(this, this.imgnormal); this.init = true;}');
        
        if ($this->_sMouseoverSrc != "") {
            if ($this->_sClickImage != "") {
                $this->attachEventDefinition("click", "onclick", "clickHandler(this);");
                $this->attachEventDefinition("mouseover", "onmouseover", "mouseoverHandler(this);");
                $this->attachEventDefinition("mouseover", "onmouseout", "mouseoutHandler(this);");                
            } else {
                $sMouseScript = 'if (isMSIE) { this.style.filter = \'progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\\\'%1$s\\\');\'; } else { this.src=\'%1$s\'; }';
                $this->attachEventDefinition("mouseover", "onmouseover", sprintf($sMouseScript, $this->_sMouseoverSrc) );
                $this->attachEventDefinition("mouseover", "onmouseout", sprintf($sMouseScript, $this->_src) );
            }
        }
        
        return parent::toHTML();
    }
}

class cHTMLErrorMessageList extends cHTMLDiv {
    
    /**
     *  cHTMLErrorMessageList list all errors using a table
     */
    public function __construct() {
        $this->_oTable = new cHTMLTable();
        $this->_oTable->setWidth("100%");        
        cHTMLDiv::__construct();
        $this->setClass("errorlist");
        $this->setStyle("width: 450px; height: 218px; overflow: auto; border: 1px solid black;");
    }
    
    function setContent($content)    {
        $this->_oTable->setContent($content);
    }
    
    function toHTML()    {
        $this->_setContent($this->_oTable->render());
        return parent::toHTML();
    }
}

class cHTMLFoldableErrorMessage extends cHTMLTableRow {
    /**
     *
     * @param string $sTitle
     * @param string $sMessage
     * @param string $sIcon optional
     * @param string $sIconText optional
     */
    function __construct($sTitle, $sMessage, $sIcon = false, $sIconText = false)    {
        $this->_oFolding = new cHTMLTableData;
        $this->_oContent = new cHTMLTableData;
        $this->_oIcon    = new cHTMLTableData;
        $this->_oIconImg = new cHTMLAlphaImage;
        $this->_oTitle     = new cHTMLDiv;
        $this->_oMessage = new cHTMLDiv;

        $alphaImage = new cHTMLAlphaImage;
        $alphaImage->setClass("closer");
        $alphaImage->setStyle('margin-top:4px;');
        $alphaImage->setSrc("../drugcms/images/open_all.gif");
        $alphaImage->setMouseover("../drugcms/images/open_all.gif");
        $alphaImage->setSwapOnClick("../drugcms/images/close_all.gif", "../drugcms/images/close_all.gif");
        $alphaImage->attachEventDefinition("showhide", "onclick", "aldiv = document.getElementById('".$this->_oMessage->getId()."');  showHideMessage(this, aldiv);");

        $this->_oTitle->setContent($sTitle);
        $this->_oTitle->setStyle("cursor: pointer;");
        $this->_oTitle->attachEventDefinition("showhide", "onclick", "alimg = document.getElementById('".$alphaImage->getId()."'); aldiv = document.getElementById('".$this->_oMessage->getId()."'); showHideMessage(alimg, aldiv); clickHandler(alimg);");

        $this->_oMessage->setContent($sMessage);
        $this->_oMessage->setClass("entry_closed");

        $this->_oFolding->setVerticalAlignment("top");
        $this->_oFolding->setContent($alphaImage);
        $this->_oFolding->setClass("icon");

        $this->_oContent->setVerticalAlignment("top");
        $this->_oContent->setClass("entry");
        $this->_oContent->setContent(array($this->_oTitle, $this->_oMessage));

        $this->_oIcon->setClass("icon");
        $this->_oIcon->setVerticalAlignment("top");
        if ($sIcon !== false) {
            $this->_oIconImg->setSrc($sIcon);
            
            if ($sIconText !== false) {
                $this->_oIconImg->setAlt($sIconText);
            }
            
            $this->_oIcon->setContent($this->_oIconImg);    
        } else {
            $this->_oIcon->setContent("&nbsp;");    
        }

        cHTMLTableRow::__construct();
    }
    
    function toHTML()    {
        $this->setContent(array($this->_oFolding, $this->_oContent, $this->_oIcon));
        return parent::toHTML();    
    }
}

class cHTMLInfoMessage extends cHTMLTableRow {
    /**
     *
     * @param string $sTitle
     * @param string $sMessage 
     */
    function __construct($sTitle, $sMessage)    {
        $this->_oTitle = new cHTMLTableData;
        $this->_oMessage = new cHTMLTableData;

        $this->_oTitle->setContent($sTitle);
        $this->_oTitle->setClass("entry_nowrap");
        $this->_oTitle->setAttribute("nowrap", "nowrap");
        $this->_oTitle->setWidth(1);
        $this->_oTitle->setVerticalAlignment("top");
        $this->_oMessage->setContent($sMessage);
        $this->_oMessage->setClass("entry_nowrap");

        cHTMLTableRow::__construct();
    }
    
    function toHTML()    {
        $this->setContent(array($this->_oTitle, $this->_oMessage));
        return parent::toHTML();
    }
}

class cHTMLLanguageSelector {
    private $m = '';
    
    function __construct() {
        # List all languages for selection
        $langs = array('de_DE' => 'Deutsch', 'en_US' => "English", 'nl_NL' => 'Nederlands');#, 'el_GR' => 'ελληνικά');
        
        # Preset a language
        if (array_key_exists('language', $_POST)) {
            # Preset the selected language
            $_SESSION['language'] = $_POST['language'];
        } else {
            # Preset the most appropriate accepted language (browser setting)
            $aAcceptedLangs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']); # de-de,de;q=0.8,en-us;q=0.5,en;q=0.3
            for ($i = 0, $n = count($aAcceptedLangs); $i < $n; $i ++) {
                $aAcceptedLangs[$i] = explode(';', $aAcceptedLangs[$i]);
                $p = strpos($aAcceptedLangs[$i][0], '-');
                if ($p !== false) {
                    $aAcceptedLangs[$i][0] = strtolower(substr($aAcceptedLangs[$i][0], 0, $p)) . '_' . strtoupper(substr($aAcceptedLangs[$i][0], ($p + 1)));
                }
                if (isset($aAcceptedLangs[$i][1])) {
                    $aAcceptedLangs[$i][1] = floatval(substr($aAcceptedLangs[$i][1], 2));
                } else {
                    $aAcceptedLangs[$i][1] = floatval(1);
                }
            }
            $q = floatval(0);
            for ($i = 0, $n = count($aAcceptedLangs); $i < $n; $i ++) {
                if ($aAcceptedLangs[$i][1] > $q) {
                    if (array_key_exists($aAcceptedLangs[$i][0], $langs)) {
                        # Preset the exact language and country code
                        $_SESSION['language'] = $aAcceptedLangs[$i][0];
                        $q = $aAcceptedLangs[$i][1];
                    } else {
                        foreach ($langs as $key => $value) {
                            if (substr($key, 0, strlen($aAcceptedLangs[$i][0])) == $aAcceptedLangs[$i][0]) {
                                # Preset just the language code, the country code may be different (e.g. 'de_DE' for 'de' after 'de_CH')
                                $_SESSION['language'] = $key;
                                $q = $aAcceptedLangs[$i][1];
                            }
                        }
                    }
                }
            }
        }
        
        # Show the available languages in a random order, but the selected language first
        $langs = $this->array_rand_keys($langs, count($langs));
        foreach ($langs as $entity => $lang) {
            if ($entity == $_SESSION['language']) {
                $test = new cHTMLLanguageLink($entity, $lang, "setuptype");
                $this->m .= $test->render();
                break;
            }
        }
        foreach ($langs as $entity => $lang) {
            if ($entity != $_SESSION['language']) {
                $test = new cHTMLLanguageLink($entity, $lang, "setuptype");
                $this->m .= $test->render();
            }
        }
    }
    
    /**
     * Returns a number of random elements from an array.
     *
     * It returns the number (specified in $limit) of elements from
     * $array. The elements are returned in a random order, exactly
     * as it was passed to the function. (So, it's safe for multi-
     * dimensional arrays, aswell as array's where you need to keep
     * the keys)
     *
     * @author Brendan Caffrey  <bjcffnet at gmail dot com>
     * @modified 2013-08-22 René Mansveld <R.Mansveld@Spider-IT.de>
     * @param  array  $array  The array to return the elements from
     * @param  int    $limit  The number of elements to return from
     *                            the array
     * @return array  The randomized array
     */
    function array_rand_keys($array, $limit = 1) {
        if (!is_array($array)) {
            return array();
        }
        
        $count = @count($array);
        
        // Sanity checks
        if ($limit == 0 || !is_array($array) || $limit > $count) return array();
        if ($count == 1) return $array;
        
        // Loop through and get the random numbers
        for ($x = 0; $x < $limit; $x++) {
            $rand = rand(0, ($count - 1));
            
            // Can't have double randoms, right?
            while (isset($rands[$rand])) $rand = rand(0, ($count - 1));
            
            $rands[$rand] = $rand;
        }
        
        $return = array();
        $curr = current($rands);
        
        // I think it's better to return the elements in a random
        // order, which is why I'm not just using a foreach loop to
        // loop through the random numbers
        while (count($return) != $limit) {
            $cur = 0;
            
            foreach ($array as $key => $val) {
                if ($cur == $curr) {
                    $return[$key] = $val;
                    
                    // Next...
                    $curr = next($rands);
                    continue 2;
                } else {
                    $cur++;
                }
            }
        }
        
        return $return;
    }
    
    function render() {
        return $this->m;
    }
}

class cHTMLLanguageLink extends cHTMLDiv {
    /**
     *
     * @param string $langcode
     * @param string $langname
     * @param int $stepnumber 
     */
    function __construct($langcode, $langname, $stepnumber) {
        cHTMLDiv::__construct();
        
        $linkImage = new cHTMLAlphaImage();
        $linkImage->setAlt($langname);
        $linkImage->setSrc('images/flags/' . $langcode . '.png');
        $linkImage->setWidth(29);
        $linkImage->setHeight(19);
        
        $this->setStyle('float: left; margin: 4px 12px 0px 0px;');
        $link = new cHTMLLink("#");
        $link->setContent($linkImage);
        #$link->attachEventDefinition('stepAttach', 'onclick', 'document.setupform.step.value="' . $stepnumber . '";');
        $link->attachEventDefinition('languageAttach', 'onclick', 'document.setupform.elements.language.value="' . $langcode . '";');
        $link->attachEventDefinition('submitAttach', 'onclick', 'document.setupform.submit();');        
        
        $this->setContent($link->render());
    }
}

class cHTMLButtonLink extends cHTMLDiv {
    /**
     *
     * @param string $href
     * @param string $title 
     */
    function __construct($href, $title)    {
        cHTMLDiv::__construct();
        
        $linkImage = new cHTMLAlphaImage();
        $linkImage->setAlt($title);
        $linkImage->setSrc("../drugcms/images/submit.gif");
        $linkImage->setMouseover("../drugcms/images/submit_hover.gif");
        $linkImage->setWidth(16);
        $linkImage->setHeight(16);


        $this->setStyle("vertical-align: center; height: 40px; width: 150px;");
        $link = new cHTMLLink($href);
        $link->setAttribute("target", "_blank");
        $link->setContent($title);


        $link2 = new cHTMLLink($href);
        $link2->setAttribute("target", "_blank");
        $link2->setContent($title);

        $link->attachEventDefinition("mouseover", "onmouseover", sprintf("mouseoverHandler(document.getElementById('%s'));", $linkImage->getId()));
        $link->attachEventDefinition("mouseout", "onmouseout", sprintf("mouseoutHandler(document.getElementById('%s'));", $linkImage->getId()));
        $link2->setContent($linkImage);



        $alignment = '<table border="0" width="100%%" cellspacing="0" cellpadding="0"><tr><td valign="middle">%s</td><td valign="middle" align="right">%s</td></tr></table>';
        $this->setContent(sprintf($alignment, $link->render(), $link2->render()));
    }
}
?>