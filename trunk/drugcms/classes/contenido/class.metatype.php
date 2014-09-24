<?php
/**
 * File:
 * class.metatype.php
 * 
 * @package Core
 * @subpackage cApi
 * @version $Rev$
 * @since 2.0.0
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright (c) 2012-2013, ConLite Team <www.conlite.org>
 * @license http://www.gnu.de/documents/gpl.en.html GPL v3 (english version)
 * @license http://www.gnu.de/documents/gpl.de.html GPL v3 (deutsche Version)
 * @link http://www.conlite.org ConLite.org
 * 
 * $Id$
 */

// security check
defined('CON_FRAMEWORK') or die('Illegal call');

/**
 * Description of cApiMetaTypeCollection
 *
 * @author Ortwin Pinke <o.pinke@conlite.org>
 */
class cApiMetaTypeCollection extends ItemCollection {
    
    public function __construct() {
        global $cfg;
        parent::__construct($cfg["tab"]["meta_type"], "idmetatype");
        $this->_setItemClass("cApiMetaType");
    }
    
    public function getAvailableMetaTypes() {
        $this->setOrder('idmetatype');
        $this->query();
        $aMetaTypes = array();
        while($oItem = $this->next()) {
            $aNewEntry = array();
            $aNewEntry["name"] = $oItem->get("metatype");
            $aNewEntry["fieldtype"] = $oItem->get("fieldtype");
            $aNewEntry["maxlength"] = $oItem->get("maxlength");
            $aNewEntry["fieldname"] = $oItem->get("fieldname");
            $aMetaTypes[$oItem->get("idmetatype")] = $aNewEntry;
        }
        
        return $aMetaTypes;
    }
}

/**
 * Description of cApiMetaType
 *
 * @author Ortwin Pinke <o.pinke@conlite.org>
 */
class cApiMetaType extends Item {
    
    public function __construct() {
        global $cfg;
        parent::__construct($cfg["tab"]["meta_type"], "idmetatype");
    }
}
?>