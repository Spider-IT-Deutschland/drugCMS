<?php
/**
 * class UpdateNotifier
 * 
 * This class handles all notification stuff for updates and rss dashboard entries
 * 
 * @package Core
 * @subpackage Classes
 * @version $Rev$
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @license http://www.gnu.de/documents/gpl-2.0.de.html GPL
 * @link http://conlite.org drugCMS Portal
 * @todo recode and optimize methods, remove all contenido related stuff
 * 
 * $Id$
 */

/**
 * @package    Contenido Backend classes
 * @version    1.0.2
 * @author     Dominik Ziegler
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release 4.8.7
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}


class Contenido_UpdateNotifier
{
    /**
     * Minor release for the simplexml xpath() method
     * @access protected
     * @var string
     */
    protected $sMinorRelease = "";

    /**
     * Host for vendor XML
     * @access protected
     * @var string
     */
    protected $sVendorHost = "updater.drugcms.org";

    /**
     * Path to files
     * @access protected
     * @var string
     */
    protected $sVendorHostPath = "/";

    /**
     * Vendor XML file
     * @access protected
     * @var string
     */
    protected $sVendorXMLFile = "vendor.xml";

    /**
     * German Vendor RSS file
     * @access protected
     * @var string
     */
    protected $sVendorRssDeFile = "rss_de.xml";

    /**
     * English Vendor RSS file
     * @access protected
     * @var string
     */
    protected $sVendorRssEnFile = "rss_en.xml";

    /**
     * Language specific RSS file
     * @access protected
     * @var string
     */
    protected $sRSSFile = "";

    /**
     * Timestamp cache file
     * @access protected
     * @var string
     */
    protected $sTimestampCacheFile = "update_time.txt";

    /**
     * Content of the XML file
     * @access protected
     * @var string
     */
    protected $sXMLContent = "";

        /**
     * Content of the language specific RSS file
     * @access protected
     * @var string
     */
    protected $sRSSContent = "";

    /**
     * Current available vendor version
     * @access protected
     * @var string
     */
    protected $sVendorVersion = "";

    /**
     * Download URL
     * @access protected
     * @var string
     */
    protected $sVendorURL = "http://updater.drugcms.org/redir";

    /**
     * Current backend language
     * @access protected
     * @var string
     */
    protected $sBackendLanguage = "";

    /**
     * Contains the cache path.
     * @access protected
     * @var string
     */
    protected $sCacheDirectory = "";

    /**
     * SimpleXML object
     * @access protected
     * @var object
     */
    protected $oXML = null;

    /**
     * Properties object
     * @access protected
     * @var object
     */
    protected $oProperties = null;

    /**
     * Session object
     * @access protected
     * @var object
     */
    protected $oSession = null;

    /**
     * Timeout for the fsockopen connection
     * @access protected
     * @var integer
     */
    protected $iConnectTimeout = 3;

    /**
     * Cache duration in minutes
     * @access protected
     * @var integer
     */
    protected $iCacheDuration = 60;

    /**
     * Check for system setting
     * @access protected
     * @var boolean
     */
    protected $bEnableCheck = false;

    /**
     * Check for system setting Rss
     * @access protected
     * @var boolean
     */
    protected $bEnableCheckRss = false;

    /**
     * If true drugcms displays a special error message due to missing write permissions.
     * @access protected
     * @var boolean
     */
    protected $bNoWritePermissions = false;

    /**
     * Display update notification based on user rights (sysadmin only)
     * @access protected
     * @var boolean
     */
    protected $bEnableView = false;

    /**
     * Update necessity
     * @access protected
     * @var boolean
     */
    protected $bUpdateNecessity = false;

    /**
     * Property configuration array
     * @access protected
     * @var array
     */
    protected $aPropConf = array("itemType" => "update", "itemID" => 1, "type" => "file_check", "name" => "xml");

    /**
     * System property configuration array for update notification
     * @access protected
     * @var array
     */
    protected $aSysPropConf = array("type" => "update", "name" => "check");

    /**
     * System property configuration array for rss notification
     * @access protected
     * @var array
     */
    protected $aSysPropConfRss = array("type" => "update", "name" => "news_feed");

    /**
     * System property configuration array for update period
     * @access protected
     * @var array
     */
    protected $aSysPropConfPeriod = array("type" => "update", "name" => "check_period");

    /**
     * Contenido configuration array
     * @access protected
     * @var array
     */
    protected $aCfg = array();
    
    private $_bUseCurl = false;
    
    private $enc = '';
    
    public $sErrorOutput;

    /**
     * Constructor of Contenido_UpdateNotifier
     * @access public
     * @param  string $sConVersion
     * @return void
     */
    public function __construct($aCfg, $oUser, $oPerm, $oSession, $sBackendLanguage)
    {
        $this->oProperties      = new PropertyCollection;
        $this->oSession         = $oSession;
        $this->aCfg             = $aCfg;
        $this->sBackendLanguage = $sBackendLanguage;
        $this->_bUseCurl        = (extension_loaded("curl"))?true:false;

        if ($oPerm->isSysadmin($oUser) != 1) {
            $this->bEnableView = false;
        } else {
            $this->bEnableView = true;

            $sAction = (isset($_GET['do']))?$_GET['do']:'';
            if($sAction != "") {
                $this->updateSystemProperty($sAction);
            }

            $sPropUpdate = getSystemProperty($this->aSysPropConf['type'], $this->aSysPropConf['name']);
            $sPropRSS    = getSystemProperty($this->aSysPropConfRss['type'], $this->aSysPropConfRss['name']);
            $sPeriod     = getSystemProperty($this->aSysPropConfPeriod['type'], $this->aSysPropConfPeriod['name']);
            $iPeriod     = Contenido_Security::toInteger($sPeriod);

            if ($sPropUpdate == "true" || $sPropRSS == "true") {

                if($sPropUpdate == "true") {
                    $this->bEnableCheck = true;
                }

                if ($sPropRSS == "true") {
                   $this->bEnableCheckRss = true;
                }

                // default cache duration of 60 minutes
                if ($iPeriod >= 60) {
                    $this->iCacheDuration = $iPeriod;
                } else {
                    $this->iCacheDuration = 60;
                }

                $this->setCachePath();
                if ( $this->sCacheDirectory != "" ) {
                    $this->setRSSFile();
                    $this->detectMinorRelease();
                    $this->checkUpdateNecessity();
                    $this->readVendorContent();
                }
            }
        }
    }

    /**
     * Sets the actual RSS file for the reader
     * @access protected
     * @return void
     */
    protected function setRSSFile()
    {
        if ($this->sBackendLanguage == "de_DE") {
            $this->sRSSFile = $this->sVendorRssDeFile;
        } else {
            $this->sRSSFile = $this->sVendorRssEnFile;
        }
    }

    /**
     * Updates the system property for activation/deactivation requests
     * @access protected
     * @param $sAction string
     * @return void
     */
    protected function updateSystemProperty($sAction)
    {
        if ($sAction == "activate") {
            setSystemProperty($this->aSysPropConf['type'], $this->aSysPropConf['name'], "true");
        } else if ($sAction == "deactivate") {
            setSystemProperty($this->aSysPropConf['type'], $this->aSysPropConf['name'], "false");
        } else if ($sAction == "activate_rss"){
            setSystemProperty($this->aSysPropConfRss['type'], $this->aSysPropConfRss['name'], "true");
        } else if ($sAction == "deactivate_rss"){
            setSystemProperty($this->aSysPropConfRss['type'], $this->aSysPropConfRss['name'], "false");
        }
    }

    /**
     * Sets the cache path
     * @access protected
     * @return void
     */
    protected function setCachePath() {
        $sCachePath = $this->aCfg['path']['contenido'].$this->aCfg['path']['cache'];
        if (!is_dir($sCachePath)) {
            mkdir($sCachePath, 0777);
        }

        if (!is_writable($sCachePath)) {
            // setting special flag for error message
            $this->bNoWritePermissions     = true;
        } else {
            $this->sCacheDirectory = $sCachePath;
        }
    }

    /**
     * Checks if the xml files must be loaded from the vendor host or local cache
     * @access protected
     * @return void
     */
    protected function checkUpdateNecessity()
    {
        $bUpdateNecessity = false;

        $aCheckFiles = array($this->sVendorXMLFile, $this->sVendorRssDeFile, $this->sVendorRssEnFile, $this->sTimestampCacheFile);
        foreach ($aCheckFiles as $sFilename) {
            if (!file_exists($this->sCacheDirectory.$sFilename)) {
                $bUpdateNecessity = true;
                break;
            }
        }

        if ($bUpdateNecessity == false) {
            $iLastUpdate = file_get_contents($this->sCacheDirectory.$this->sTimestampCacheFile);

            $iCheckTimestamp = $iLastUpdate + ($this->iCacheDuration * 60);
            $iCurrentTime = time();

            if ($iCheckTimestamp > $iCurrentTime) {
                $bUpdateNecessity = false;
            } else {
                $bUpdateNecessity = true;
            }
        }

        $this->bUpdateNecessity = $bUpdateNecessity;
    }

    /**
     * Detects and converts the minor release of the system version
     * @access protected
     * @return void
     */
    protected function detectMinorRelease()
    {
        $sVersion             = $this->aCfg['version'];
        $aExplode             = explode(".", $sVersion);        
        $sMinorRelease        = "drugcms".$aExplode[0].$aExplode[1];
        $this->sMinorRelease  = $sMinorRelease;
    }

    /**
     * Reads the xml files from vendor host or cache and checks for file manipulations
     * @access protected
     * @return void
     */
    protected function readVendorContent()
    {
        $this->sXMLContent = "";
        if ($this->bUpdateNecessity == true) {
            $aXmlContent = $this->getVendorHostFiles();
            if (isset($aXmlContent[$this->sVendorXMLFile]) && isset($aXmlContent[$this->sVendorRssDeFile]) && isset($aXmlContent[$this->sVendorRssEnFile])) {
                $this->handleVendorUpdate($aXmlContent);
            }
        } else {
            $sXMLContent                          = file_get_contents($this->sCacheDirectory.$this->sVendorXMLFile);
            $aRSSContent[$this->sVendorRssDeFile] = file_get_contents($this->sCacheDirectory.$this->sVendorRssDeFile);
            $aRSSContent[$this->sVendorRssEnFile] = file_get_contents($this->sCacheDirectory.$this->sVendorRssEnFile);

            $sXMLHash = md5($sXMLContent.$aRSSContent[$this->sVendorRssDeFile].$aRSSContent[$this->sVendorRssEnFile]);
            $sPropertyHash = $this->getHashProperty();
            if ($sXMLHash == $sPropertyHash) {
                $this->sXMLContent = $sXMLContent;
                $this->sRSSContent = $aRSSContent[$this->sRSSFile];
            } else {
                $aXmlContent = $this->getVendorHostFiles();
                if (isset($aXmlContent[$this->sVendorXMLFile]) && isset($aXmlContent[$this->sVendorRssDeFile]) && isset($aXmlContent[$this->sVendorRssEnFile])) {
                    $this->handleVendorUpdate($aXmlContent);
                }
            }
        }

        if ($this->sXMLContent != "") {
            $this->oXML = simplexml_load_string($this->sXMLContent);
            if (!is_object($this->oXML)) {
                $sErrorMessage = i18n('Unable to check for new updates!')." ".i18n('Could not handle server response!');
                $this->sErrorOutput = $this->renderOutput($sErrorMessage);
            } else {
                $oVersion = $this->oXML->xpath("/drugcmsteam/drugcms/releases/".$this->sMinorRelease);
                if (!isset($oVersion[0])) {
                    $sErrorMessage = i18n('Unable to check for new updates!')." ".i18n('Could not determine vendor version!');
                    $this->sErrorOutput = $this->renderOutput($sErrorMessage);
                } else {
                    $this->sVendorVersion = $oVersion[0];
                }
            }
        }
    }

    /**
     * Handles the update of files coming per vendor host
     * @access protected
     * @return void
     */
    protected function handleVendorUpdate($aXMLContent)
    {
        $bValidXMLFile   = true;
        $bValidDeRSSFile = true;
        $bValidEnRSSFile = true;

        $sCheckXML = stristr($aXMLContent[$this->sVendorXMLFile], "<drugcmsteam>");
        if ($sCheckXML == false) {
            $bValidXMLFile = false;
        }

        $sCheckDeRSS = stristr($aXMLContent[$this->sVendorRssDeFile], "<channel>");
        if ($sCheckDeRSS == false) {
            $bValidDeRSSFile = false;
        }

        $sCheckEnRSS = stristr($aXMLContent[$this->sVendorRssEnFile], "<channel>");
        if ($sCheckEnRSS == false) {
            $bValidEnRSSFile = false;
        }

        // To prevent simplexml and rss reader parser errors by loading an error page from the vendor host
        // the content will be replaced with the cached file (if existing) or a string
        if ($bValidXMLFile != true) {
            if (file_exists($this->sCacheDirectory.$this->sVendorXMLFile)) {
                $sXMLReplace = file_get_contents($this->sCacheDirectory.$this->sVendorXMLFile);
            } else {
                $sXMLReplace = "<error>The vendor host file at ".$this->sVendorHost." is not availiable!</error>";
            }
            $aXMLContent[$this->sVendorXMLFile] = $sXMLReplace;
        }

        if ($bValidDeRSSFile != true) {
            if (file_exists($this->sCacheDirectory.$this->sVendorRssDeFile)) {
                $sDeRSSReplace = file_get_contents($this->sCacheDirectory.$this->sVendorRssDeFile);
            } else {
                $sDeRSSReplace = "<rss></rss>";
            }
            $aXMLContent[$this->sVendorRssDeFile] = $sDeRSSReplace;
        }

        if ($bValidEnRSSFile != true) {
            if (file_exists($this->sCacheDirectory.$this->sVendorRssEnFile)) {
                $sEnRSSReplace = file_get_contents($this->sCacheDirectory.$this->sVendorRssEnFile);
            } else {
                $sEnRSSReplace = "<rss></rss>";
            }
            $aXMLContent[$this->sVendorRssEnFile] = $sEnRSSReplace;
        }

        $this->sXMLContent = $aXMLContent[$this->sVendorXMLFile];
        $this->sRSSContent = $aXMLContent[$this->sRSSFile];
        $this->updateCacheFiles($aXMLContent);
        $this->updateHashProperty($aXMLContent);
    }
    
    /**
     * get vendor file from host server
     * 
     * @author Ortwin Pinke <o.pinke@conlite.org>
     * @since 2.0.0
     * @todo add old fsockopen functionality if curl is not available
     * @param string $sHost
     * @param string $sFile
     * @param boolean $bCheckCon
     * @return string|boolean response or false
     */
    protected function _getFile($sHost, $sFile, $bCheckCon = false) {
        $response = false;
        
        if ($this->_bUseCurl) {
            if ($bCheckCon) {
                $ch = $this->_checkCon2Host($sHost);
            } else {
                $ch = curl_init("http://".$sHost);
            }
            if (is_resource($ch)) {
                curl_setopt($ch, CURLOPT_URL, "http://".$sHost.$sFile);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: close'));
                curl_setopt($ch, CURLOPT_TIMEOUT, 2); 
                $response = curl_exec($ch); 
                curl_close($ch);
            }
        } else {
            // URL zerlegen
            $parsedurl = @parse_url("http://".$sHost.$sFile);
            // Host ermitteln, ungültigen Aufruf abfangen
            if (empty($parsedurl['host'])) {
                return null;
            }
            $host = $parsedurl['host'];
            // Pfadangabe ermitteln
            if (empty($parsedurl['path'])) {
                $documentpath = '/';
            } else {
                $documentpath = $parsedurl['path'];
            }
            // Parameter ermitteln
            if (!empty($parsedurl['query'])) {
                $documentpath .= '?' . $parsedurl['query'];
            }
            // Port ermitteln
            if (!empty($parsedurl['port'])) {
                $port = $parsedurl['port'];
            } else {
                $port = 80;
            }
            // Socket öffnen
            $fp = @fsockopen($host, $port, $errno, $errstr, 30);
            if (!$fp) {
                return null;
            }
            // Request senden
            fputs ($fp, "GET {$documentpath} HTTP/1.0\r\nHost: {$host}\r\n\r\n");
            // Header auslesen
            do {
                $line = chop(fgets($fp));
            } while ((!empty($line)) && (!feof($fp)));
            // Daten auslesen
            $result = array();
            while (!feof($fp)) {
                $result[] = fgets($fp);
            }
            // Socket schliessen
            fclose($fp);
            // Ergebnis-Array zurückgeben
            $response = implode('', $result);
        }
        return $response;
    }
    
    /**
     * Check http-connection to a host using curl
     * 
     * @author Ortwin Pinke <o.pinke@conlite.org>
     * @since 2.0.0
     * @param string $sHost 
     * @return obj|boolean curl object or false
     */
    protected function _checkCon2Host($sHost) {
        $ch = curl_init("http://".$sHost);        
        if(!is_resource($ch)) {
            $sErrorMessage = i18n('Unable to check for updates!')." "
                    .  sprintf(i18n('Connection to %s failed!'), $sHost);
            $this->sErrorOutput = $this->renderOutput($sErrorMessage);
            return false;
        }
        return $ch;
    }

    /**
     * Connects with vendor host and gets the xml files
     * @access protected
     * @return array
     */
    protected function getVendorHostFiles()
    {
        $aXMLContent = array();
        
        $aXMLContent[$this->sVendorXMLFile]     = $this->_getFile($this->sVendorHost, $this->sVendorHostPath.$this->sVendorXMLFile);
        $aXMLContent[$this->sVendorRssEnFile]   = $this->_getFile($this->sVendorHost, $this->sVendorHostPath.$this->sVendorRssEnFile);
        $aXMLContent[$this->sVendorRssDeFile]   = $this->_getFile($this->sVendorHost, $this->sVendorHostPath.$this->sVendorRssDeFile);
        
        return $aXMLContent;
    }

    /**
     * Updates the files in cache
     * @access protected
     * @param $aRSSContent array
     * @return void
     */
    protected function updateCacheFiles($aRSSContent)
    {
        $aWriteCache = array();
        $aWriteCache[$this->sVendorXMLFile]      = $this->sXMLContent;
        $aWriteCache[$this->sVendorRssDeFile]    = $aRSSContent[$this->sVendorRssDeFile];
        $aWriteCache[$this->sVendorRssEnFile]    = $aRSSContent[$this->sVendorRssEnFile];
        $aWriteCache[$this->sTimestampCacheFile] = time();

        if (is_writable($this->sCacheDirectory)) {
            foreach ($aWriteCache as $sFile=>$sContent) {
                $sCacheFile = $this->sCacheDirectory.$sFile;
                $oFile = fopen($sCacheFile, "w+");
                ftruncate($oFile, 0);
                fwrite($oFile, $sContent);
                fclose($oFile);
            }
        }
    }

    /**
     * Gets the xml file hash from the property table
     * @access protected
     * @return string
     */
    protected function getHashProperty()
    {
        $sProperty = $this->oProperties->getValue($this->aPropConf['itemType'], $this->aPropConf['itemID'], $this->aPropConf['type'], $this->aPropConf['name']);
        return $sProperty;
    }

    /**
     * Updates the xml file hash in the property table
     * @access protected
     * @param $aRSSContent array
     * @return void
     */
    protected function updateHashProperty($aXMLContent)
    {
        $sXML    = $aXMLContent[$this->sVendorXMLFile];
        $sDeRSS  = $aXMLContent[$this->sVendorRssDeFile];
        $sEnRSS  = $aXMLContent[$this->sVendorRssEnFile];

        $sPropValue = md5($sXML.$sDeRSS.$sEnRSS);
        $this->oProperties->setValue($this->aPropConf['itemType'], $this->aPropConf['itemID'], $this->aPropConf['type'], $this->aPropConf['name'], $sPropValue);
    }

    /**
     * Checks the patch level of system and vendor version
     * @access protected
     * @return string
     */
    protected function checkPatchLevel() {
        $sActVer = str_replace(" ", ".", strtolower($this->aCfg['version']));
        $sUpdVer = str_replace(" ", ".", strtolower($this->sVendorVersion));
        $sVersionCompare = version_compare($sActVer, $sUpdVer);
        return $sVersionCompare;
    }

    /**
     * Generates the download URL
     * @access protected
     * @return string
     */
    protected function getDownloadURL() {
        $sVendorURLVersion = str_replace(".", "_", $this->sVendorVersion);
        $sVendorURL = $this->sVendorURL."/drugCMS_".$sVendorURLVersion;
        return $sVendorURL;
    }

    /**
     * Generates the output for the backend
     * @access protected
     * @param $sMessage string
     * @return string
     */
    protected function renderOutput($sMessage)
    {
        $oTpl = new Template();
        $oTpl->set('s', 'UPDATE_MESSAGE', $sMessage);

        if ($this->bEnableCheck == true) {
            $oTpl->set('s', 'UPDATE_ACTIVATION', i18n('Disable update notification'));
            $oTpl->set('s', 'IMG_BUT_UPDATE', 'but_cancel.gif');
            $oTpl->set('s', 'LABEL_BUT_UPDATE', i18n('Disable notification'));
            $oTpl->set('s', 'URL_UPDATE', $this->oSession->url('main.php?frame=4&amp;area=mydrugcms&amp;do=deactivate'));
        } else {
            $oTpl->set('s', 'UPDATE_ACTIVATION', i18n('Enable update notification (recommended)'));
            $oTpl->set('s', 'IMG_BUT_UPDATE', 'but_ok.gif');
            $oTpl->set('s', 'LABEL_BUT_UPDATE', i18n('Enable notification'));
            $oTpl->set('s', 'URL_UPDATE', $this->oSession->url('main.php?frame=4&amp;area=mydrugcms&amp;do=activate'));
        }

        if ($this->bEnableCheckRss == true) {
            $oTpl->set('s', 'RSS_ACTIVATION', i18n('Disable RSS notification'));
            $oTpl->set('s', 'IMG_BUT_RSS', 'but_cancel.gif');
            $oTpl->set('s', 'LABEL_BUT_RSS', i18n('Disable notification'));
            $oTpl->set('s', 'URL_RSS', $this->oSession->url('main.php?frame=4&amp;area=mydrugcms&amp;do=deactivate_rss'));

            $oTpl = $this->renderRss($oTpl);
        } else {
            $oTpl->set('s', 'RSS_ACTIVATION', i18n('Enable RSS notification (recommended)'));
            $oTpl->set('s', 'IMG_BUT_RSS', 'but_ok.gif');
            $oTpl->set('s', 'LABEL_BUT_RSS', i18n('Enable notification'));
            $oTpl->set('s', 'URL_RSS', $this->oSession->url('main.php?frame=4&amp;area=mydrugcms&amp;do=activate_rss'));
            $oTpl->set('s', 'NEWS_NOCONTENT', i18n('RSS notification is disabled'));
            $oTpl->set("s", "DISPLAY_DISABLED", 'block');
        }

        return $oTpl->generate('templates/standard/'.$this->aCfg['templates']['welcome_update'], 1);
    }

    /**
     * Generates the output for the rss informations
     * @access protected
     * @param $oTpl
     * @return drugcms template object
     */
    protected function renderRss($oTpl)
    {
        if (!is_object($oTpl)) {
            $oTpl = new Template();
        }

        if ($this->sRSSContent != '') {
            $sFeedContent = substr($this->sRSSContent, 0, 1024);
            $sFeedContent = trim($sFeedContent);

            $p1 = strpos($sFeedContent, 'encoding=');
            if ($p1 !== false) {
                $c = substr($sFeedContent, ($p1 + 9), 1); # " or ' after encoding=
                $p1 += 10;
                $p2 = strpos($sFeedContent, $c, $p1);
                $rssencoding = substr($sFeedContent, $p1, $p2 - $p1);
            }

            if ($rssencoding) {
                $oRss = new XML_RSS($this->sCacheDirectory.$this->sRSSFile, $rssencoding);
            } else {
                $oRss = new XML_RSS($this->sCacheDirectory.$this->sRSSFile);
            }

            $oRss->parse();

            $iCnt = 0;
            foreach ($oRss->getItems() as $aItem) {
                $sText = $aItem['description'];
                if (strlen($sText) > 150) {
                    $sText = capiStrTrimAfterWord($sText, 150).'&hellip;';
                }

                $oTpl->set("d", "NEWS_DATE", $aItem['pubdate']);
				$oTpl->set("d", "NEWS_TITLE", $this->prepareStringForOutput($aItem['title'], $rssencoding));
                $oTpl->set("d", "NEWS_TEXT", $this->prepareStringForOutput($sText, $rssencoding));
                $oTpl->set("d", "NEWS_URL", $aItem['link']);
                $oTpl->set("d", "LABEL_MORE", i18n('read more'));
                $oTpl->next();
                $iCnt++;

                if ($iCnt == 3) {
                    break;
                }
            }

            if ($iCnt == 0) {
                $oTpl->set("s", "NEWS_NOCONTENT", i18n("No RSS content available"));
                $oTpl->set("s", "DISPLAY_DISABLED", 'block');
            } else {
                $oTpl->set("s", "NEWS_NOCONTENT", "");
                $oTpl->set("s", "DISPLAY_DISABLED", 'none');
            }
        } else if ( $this->bNoWritePermissions == true ) {
            $oTpl->set("s", "NEWS_NOCONTENT", i18n('Your webserver does not have write permissions for the directory /drugcms/cache/!'));
        } else {
            $oTpl->set("s", "NEWS_NOCONTENT", i18n("No RSS content available"));
        }

        return $oTpl;
    }
    function prepareStringForOutput($sIn, $sCode = 'ISO-8859-1') {
        global $db, $cfg, $lang;
        
        if (strlen($this->enc) == 0) {
            if (intval($lang)) {
                $sql = 'SELECT encoding
                        FROM ' . $cfg['tab']['lang'] . '
                        WHERE (idlang=' . $lang . ')';
                $db->query($sql);
                $db->next_record();
                $this->enc = $db->f('encoding');
            } else {
                $this->enc = 'UTF-8';
            }
        }
        if ((strtoupper($sCode) == 'UTF-8') && (strtoupper($this->enc) != 'UTF-8')) {
            return utf8_decode($sIn);
        } elseif ((strtoupper($this->enc) == 'UTF-8') && (strtoupper($sCode) != 'UTF-8')) {
            return utf8_encode($sIn);
        } else {
            return $sIn;
        }
    }

    /**
     * Displays the rendered output
     * @access public
     * @return string
     */
    public function displayOutput()
    {
        if (!$this->bEnableView) {
            $sOutput = "";
        } elseif ($this->bNoWritePermissions == true ) {
            $sMessage = i18n('Your webserver does not have write permissions for the directory /drugcms/cache/!');
            $sOutput = $this->renderOutput($sMessage);
        } elseif (!$this->bEnableCheck) {
            $sMessage = i18n('Update notification is disabled! For actual update information, please activate.');
            $sOutput = $this->renderOutput($sMessage);
        } elseif (strlen($this->sErrorOutput)) {
            $sOutput = $this->sErrorOutput;
        } elseif (!strlen($this->sVendorVersion)) {
            $sMessage = i18n('You have an unknown or unsupported version of drugCMS!');
            $sOutput = $this->renderOutput($sMessage);
        } elseif ($this->sVendorVersion == "deprecated") {
            $sMessage = sprintf(i18n('Your version of drugCMS is deprecated and not longer supported for any updates. Please update to a higher version! <br /> <a href="%s" class="blue" target="_blank">Download now!</a>'), 'http://www.drugcms.org');
            $sOutput = $this->renderOutput($sMessage);
        } elseif (intval($this->checkPatchLevel()) == -1) {
            $sVendorDownloadURL = $this->getDownloadURL();
            $sMessage = sprintf(i18n('A new version of drugCMS is available! <br /> <a href=\"%s\" class=\"blue\" target=\"_blank\">Download %s now!</a>'), $sVendorDownloadURL, $this->sVendorVersion);
            $sOutput = $this->renderOutput($sMessage);
        } elseif (intval($this->checkPatchLevel()) == 1) {
            $sMessage = sprintf(i18n('It seems to be that your version string was manipulated. drugCMS %s does not exist!'), $this->aCfg['version']);
            $sOutput = $this->renderOutput($sMessage);
        } else {
            $sMessage = i18n('Your version of drugCMS is up to date!');
            $sOutput = $this->renderOutput($sMessage);
        }

        return $sOutput;
    }
}
?>