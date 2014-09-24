<?php
/**
 * File:
 * class.cHTML.php
 *
 * Description:
 *  + add filediscription +
 * 
 * @package Core
 * @subpackage Expression clsubpackage is undefined on line 10, column 18 in Templates/PHP-Test/conliteclass.php.
 * @version $Rev$
 * @since Expression clactversion is undefined on line 12, column 13 in Templates/PHP-Test/conliteclass.php.
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright c) 2012, conlite.org
 * @license http://www.gnu.de/documents/gpl.en.html GPL v3 (english version)
 * @license http://www.gnu.de/documents/gpl.de.html GPL v3 (deutsche Version)
 * @link http://www.conlite.org drugCMS.org
 * 
 * $Id$
 */

// security check
defined('CON_FRAMEWORK') or die('Illegal call');

/**
 * Description of cHTML
 *
 * @author Ortwin Pinke <o.pinke@conlite.org>
 */
class cHTML extends HTML_Common2 {
    
    /**
     * Storage of the open SGML tag template
     * @var string 
     */
    protected $_skeleton_open;

    /**
     * Storage of a single SGML tag template
     * @var string
     */
    protected $_skeleton_single;

    /**
     * Storage of the close SGML tag
     * @var string
     */
    protected $_skeleton_close;

    /**
     * Defines which tag to use
     * @var string
     */
    protected $_tag;

    /**
     * Defines the style definitions
     * @var string
     */
    protected $_styledefs;

    /**
     * Defines all scripts which are required by the current element
     * @var array
     */
    protected $_requiredScripts;

    /**
     * @var boolean Defines if the current tag is a contentless tag
     */
    protected $_contentlessTag;

    /**
     * @var array Defines which JS events contain which scripts
     */
    protected $_aEventDefinitions;

    /**
    * @var array Style definitions 
    */
    protected $_aStyleDefinitions;

    /**
    * @var string The content itself
    */
    protected $_content;
    
    protected $_aCfg;
	
    /**
    * Constructor Function
    * Initializes the SGML open/close tags
    * 
    * @param none
    * @return void
    */
    public function __construct()	{
        global $cfg;
        $this->_aCfg = $cfg;
        
        parent::__construct();
        $this->_skeleton_open = '<%s%s>';
        $this->_skeleton_close = '</%s>';


        /* Cache the XHTML setting for performance reasons */
        if (!is_array($this->_aCfg) || !array_key_exists("generate_xhtml", $this->_aCfg)) {
            if (function_exists("getEffectiveSetting")) {
                $this->_aCfg["generate_xhtml"] = (getEffectiveSetting("generator", "xhtml", false))?true:false;
            } else {
                $this->_aCfg["generate_xhtml"] = false;	
            }
        }

        if($this->_aCfg["generate_xhtml"] === true) {
            $this->_skeleton_single = '<%s%s />';
        } else {
            $this->_skeleton_single = '<%s%s>';
        }

        $this->_styledefs = array ();
        $this->_aStyleDefinitions = array();
        $this->setContentlessTag();

        $this->advanceID();
        $this->_requiredScripts = array ();
        $this->_aEventDefinitions = array ();
    }

    /**
     *
     * @param type $contentlessTag 
     */
    public function setContentlessTag($contentlessTag = true)	{
        $this->_contentlessTag = $contentlessTag;
    }

    /**
     * advances to the next ID available in the system.
     * 
     * This function is useful if you need to use HTML elements
     * in a loop, but don't want to re-create new objects each time.
     *
     * @return void 
     */
    public function advanceID() {
        global $cHTMLIDCount;

        $cHTMLIDCount ++;
        $this->updateAttributes(array ("id" => "m".$cHTMLIDCount));
    }

    /**
     * getID: returns the current ID
     *
     * @return string current ID
     */
    public function getID()	{
        return $this->getAttribute("id");
    }

    /**
     * setAlt: sets the alt and title attributes
     *
     * Sets the "alt" and "title" tags. Usually, "alt" is used 
     * for accessibility and "title" for mouse overs.
     * 
     * To set the text for all browsers for mouse over, set "alt"
     * and "title". IE behaves incorrectly and shows "alt" on 
     * mouse over. Mozilla browsers only show "title" as mouse over.
     *
     * @param string $alt Text to set as the "alt" attribute
     */
    public function setAlt($alt)	{
        $attributes = array ("alt" => $alt, "title" => $alt);
        $this->updateAttributes($attributes);
    }

    /**
     * sets the ID class
     *
     * @param string $class Text to set as the "id"
     */
    public function setID($id) {
        $this->updateAttributes(array ("id" => $id));
    }

    /**
     * sets the CSS class
     *
     * @param string $class Text to set as the "class" attribute
     */
    public function setClass($class) {
        $this->updateAttributes(array ("class" => $class));
    }

    /**
     * sets the CSS style
     *
     * @param $class string Text to set as the "style" attribute
     */
    public function setStyle($style) {
        $this->updateAttributes(array ("style" => $style));
    }

    /**
     * adds an "onXXX" javascript event handler
     *
     * example:
     * $item->setEvent("change","document.forms[0].submit");
     *
     * @param $event string Type of the event
     * @param $action string Function or action to call (JavaScript Code)
     */
    public function setEvent($event, $action) {
        if (substr($event, 0, 2) != "on") {
            $this->updateAttributes(array ("on".$event => $action));
        } else {
            $this->updateAttributes(array ($event => $action));
        }
    }

    /**
     * removes an event handler
     *
     * example:
     * $item->unsetEvent("change");
     *
     * @param $event string Type of the event
     */
    public function unsetEvent($event) {
        if (substr($event, 0, 2) != "on") {
            $this->removeAttribute("on".$event);
        } else {
            $this->removeAttribute($event);
        }
    }

    /**
     * fillSkeleton: Fills the open SGML tag skeleton
     * 
     * fillSkeleton fills the SGML opener tag with the
     * specified attributes. Attributes need to be passed
     * in the stringyfied variant.
     *
     * @param $attributes string Attributes to set
     * @return string filled SGML opener skeleton
     */
    public function fillSkeleton($attributes) {
        if ($this->_contentlessTag == true) {
            return sprintf($this->_skeleton_single, $this->_tag, $attributes);
        } else {
            return sprintf($this->_skeleton_open, $this->_tag, $attributes);
        }
    }

    /**
     * fillCloseSkeleton: Fills the close skeleton
     *
     * @param none
     * @return string filled SGML closer skeleton
     */
    public function fillCloseSkeleton() {
        return sprintf($this->_skeleton_close, $this->_tag);
    }

    /**
     * addStyleDefinition
     *
     * @deprecated name change, use attachStyleDefinition
     * @param $entity string Entity to define
     * @param $definition string Definition for the given entity 
     * @return string filled SGML closing skeleton
     */
    public function setStyleDefinition($entity, $definition) {
        $this->_styledefs[$entity] = $definition;
    }
	
    /**
     * attachStyleDefinition: Attaches a style definition.
     * 
     * This function is not restricted to a single style, e.g.
     * you can set multiple style definitions as-is to the handler.
     * 
     * $example->attachStyle("myIdentifier",
     * 			"border: 1px solid black; white-space: nowrap");
     * $example->attachStyle("myIdentifier2",
     * 						"padding: 0px");
     * 
     * Results in:
     * 
     * style="border: 1px solid black; white-space: nowrap; padding: 0px;"
     *
     * @param $sName   		string Name for a style definition
     * @param $sDefinition 	string Definition for the given entity 
     * @return string filled SGML closing skeleton
     */
    public function attachStyleDefinition($sName, $sDefinition) {
        $this->_aStyleDefinitions[$sName] = $sDefinition;
    }

    /**
     * 
     * @param string $script
     */
    public function addRequiredScript($script) {
        if (!is_array($this->_requiredScripts)) {
            $this->_requiredScripts = array ();
        }
        $this->_requiredScripts[] = $script;
        $this->_requiredScripts = array_unique($this->_requiredScripts);
    }
        
    /**
     * 
     * @param array $aAttributes
     * @return array
     */
    public function updateAttributes($aAttributes) {
        return $this->mergeAttributes($aAttributes);
    }

    /**
     * _setContent: Sets the content of the object
     *
     * @param $content string/object String with the content or an object to render.
     *
     */
    public function _setContent($content) {
        $this->setContentlessTag(false);
        /* Is it an array? */
        if(is_array($content)) {
            unset ($this->_content);
            $this->_content = "";
            
            foreach($content as $item) {
                if(is_object($item)) {
                    if(method_exists($item, "render")) {
                        $this->_content .= $item->render();
                    }
                    
                    if(count($item->_requiredScripts) > 0) {
                        $this->_requiredScripts = array_merge($this->_requiredScripts, $item->_requiredScripts);
                    }
                } else {
                    $this->_content .= $item;
                }
            }
        } else {
            if(is_object($content)) {
                if(method_exists($content, "render")) {
                    $this->_content = $content->render();
                }
                
                if(count($content->_requiredScripts) > 0) {
                    $this->_requiredScripts = array_merge($this->_requiredScripts, $content->_requiredScripts);
                }
                return;
            } else {
                $this->_content = $content;
            }
        }
    }

    /**
     * attachEventDefinition: Attaches the code for an event
     * 
     * Example to attach an onClick handler:
     * setEventDefinition("foo", "onClick", "alert('foo');");
     * 
     * @param $sName string defines the name of the event
     * @param $sEvent string defines the event (e.g. onClick)
     * @param $sCode string defines the code
     */
    public function attachEventDefinition($sName, $sEvent, $sCode) {
        $this->_aEventDefinitions[strtolower($sEvent)][$sName] = $sCode;
    }

    /**
     * setAttribte: Sets a specific attribute
     * 
     * @param $sAttributeName string Name of the attribute
     * @param $sValue string Value of the attribute
     */
    public function setAttribute($sAttributeName, $sValue) {
        $this->updateAttributes(array ($sAttributeName => $sValue));
    }
        
    /**
     * 
     * @return string
     */
    public function __toString() {
        return $this->toHtml();
    }

   /**
    * Renders the output
    * If the tag 
    */
   public function toHTML() {
       /* Fill style definition */
       $style = $this->getAttribute("style");
       
       /* If the style doesn't end with a semicolon, append one */
       if(is_string($style)) {
           $style = trim($style);
           
           if (substr($style, strlen($style) - 1) != ";") {
               $style .= ";";
           }
       }
       
       foreach($this->_aStyleDefinitions as $sEntry) {
           $style .= $sEntry;
           
           if (substr($style, strlen($style) - 1) != ";") {
               $style .= ";";
           }			
       }
       
       foreach($this->_aEventDefinitions as $sEventName => $sEntry) {
           $aFullCode = array();
           
           foreach ($sEntry as $sName => $sCode) {
               $aFullCode[] = $sCode;
           }
           $this->setAttribute($sEventName, $this->getAttribute($sEventName).implode(" ", $aFullCode));
       }
       
       /* Apply all stored styles */
       foreach ($this->_styledefs as $key => $value) {
           $style .= "$key: $value;";
       }
       
       if ($style != "") {
           $this->setStyle($style);
       }
       
       if ($this->_content != "" || $this->_contentlessTag == false) {
           $attributes = $this->getAttributes(true);
           return $this->fillSkeleton($attributes).$this->_content.$this->fillCloseSkeleton();
       } else {
           /* This is a single style tag */
           $attributes = $this->getAttributes(true);
           return $this->fillSkeleton($attributes);
       }
   }

    /**
     * render(): Alias for toHtml
     *
     * @param none
     * @return string Rendered HTML
     */
    public function render() {
        return $this->toHtml();
    }
}
?>