<?php
/**
 * File:
 * class.metatag.php
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
 * Description of cApiMetaTagCollection
 *
 * @author Ortwin Pinke <o.pinke@conlite.org>
 */
class cApiMetaTagCollection extends ItemCollection {
    
    public function __construct() {
        global $cfg;
        parent::__construct($cfg["tab"]["meta_tag"], "idmetatag");
        $this->_setItemClass("cApiMetaTag");
    }
}

/**
 * Description of cApiMetaTag
 *
 * @author Ortwin Pinke <o.pinke@conlite.org>
 */
class cApiMetaTag extends Item {
    
    public function __construct() {
        global $cfg;
        parent::__construct($cfg["tab"]["meta_tag"], "idmetatag");
    }
}
?>