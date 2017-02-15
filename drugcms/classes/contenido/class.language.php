<?php
/**
 * Project:
 * Contenido Content Management System
 *
 * Description:
 * Language management class
 * 
 * @package    Contenido Backend classes
 * @version    1.5
 * @author     Bjoern Behrens
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 *
 *   $Id$:
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}


class cApiLanguageCollection extends ItemCollection {
    
    /**
     * Constructor
     */
    public function __construct() {
        global $cfg;
        parent::__construct($cfg["tab"]["lang"], "idlang");
        $this->_setItemClass("cApiLanguage");
        $this->_setJoinPartner("cApiClientLanguageCollection");
    }

    /** @deprecated  [2011-03-15] Old constructor function for downwards compatibility */
    public function cApiLanguageCollection() {
        cWarning(__FILE__, __LINE__, "Deprecated method call, use __construct()");
        $this->__construct();
    }
    
    public function nextAccessible() {
        global $perm, $client, $cfg, $lang;

        $item = parent::next();

        $db = new DB();
        $lang   = Contenido_Security::toInteger($lang);
        $client = Contenido_Security::toInteger($client);

        $sql = "SELECT idclient FROM ".$cfg["tab"]["clients_lang"]." WHERE idlang = '".$lang."'";
        $db->query($sql);

        if ($db->next_record()) {
            if ($client != $db->f("idclient")) {
                $item = $this->nextAccessible();
            }
        }

        if ($item) {
            if ($perm->have_perm_client("lang[".$item->get("idlang")."]") ||
                $perm->have_perm_client("admin[".$client."]") ||
                $perm->have_perm_client()) {
                // Do nothing for now
            } else {
                $item = $this->nextAccessible();
            }

            return $item;
        } else {
            return false;
        }
    }    
    
    /**
     * Returns an array with language ids or an array of values if $bWithValues is true
     * 
     * @param int $iClient
     * @param boolean $bWithValues
     * @return array
     */
    public function getClientLanguages($iClient, $bWithValues=false) {
        $aList = array();
        $oClientLangCol = new cApiClientLanguageCollection();
        $oClientLangCol->setWhere("idclient", $iClient);
        $oClientLangCol->query();
        
        while($oItem = $oClientLangCol->next()) {
            $mTmpValues = '';
            if($bWithValues) {
                $oLanguage = new cApiLanguage($oItem->get("idlang"));
                $mTmpValues = array(
                  "idlang" => $oItem->get("idlang"),
                    "name" => $oLanguage->get("name"),
                    "active" => ($oLanguage->get("active"))?true:false,
                    "encoding" => $oLanguage->get("encoding")
                );
                unset($oLanguage);
            } else {
                $mTmpValues = $oItem->get("idlang");
            }
            $aList[$oItem->get("idlang")] = $mTmpValues;
        }
        unset($oClientLangCol, $oItem);
        return $aList;
    }
}


class cApiLanguage extends Item {
    
    /**
     * Constructor Function
     * @param  mixed  $mId  Specifies the ID of item to load
     */
    public function __construct($mId = false) {
        global $cfg;
        parent::__construct($cfg["tab"]["lang"], "idlang");
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }

    /** @deprecated  [2011-03-15] Old constructor function for downwards compatibility */
    public function cApiLanguage($mId = false) {
        cWarning(__FILE__, __LINE__, "Deprecated method call, use __construct()");
        $this->__construct($mId);
    }
}
?>