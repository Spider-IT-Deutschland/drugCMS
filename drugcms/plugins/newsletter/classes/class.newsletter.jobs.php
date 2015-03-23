<?php
/**
 * Project:
 * Contenido Content Management System
 *
 * Description:
 * Newsletter job class
 *
 * Requirements:
 * @con_php_req 5.0
 *
 *
 * @package    Contenido Backend classes
 * @version    1.1
 * @author     Björn Behrens
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 *
 * {@internal
 *   created  2004-08-01
 *   modified 2008-06-30, Dominik Ziegler, add security fix
 *   modified 2011-03-14, Murat Purc, adapted to new GenericDB, partly ported to PHP 5, formatting
 *
 *   $Id$:
 * }}
 *
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

$plugin_name = 'newsletter';

/**
 * Collection management class
 */
class cNewsletterJobCollection extends ItemCollection
{
    /**
     * Constructor Function
     * @param none
     */
    public function __construct()
    {
        global $cfg;
        parent::__construct($cfg["tab"]["news_jobs"], "idnewsjob");
        $this->_setItemClass("cNewsletterJob");
    }

    /** @deprecated  [2011-03-15] Old constructor function for downwards compatibility */
    public function cNewsletterJobCollection()
    {
        cWarning(__FILE__, __LINE__, "Deprecated method call, use __construct()");
        $this->__construct();
    }

    /**
     * Creates a newsletter job
     * @param $name        string    Specifies the name of the newsletter, the same name may be used more than once
     * @param $idnews    integer Newsletter id
     */
    public function create($iIDNews, $iIDCatArt, $sName = "")
    {
        global $client, $lang, $cfg, $cfgClient, $auth;

        $oNewsletter = new Newsletter;
        if ($oNewsletter->loadByPrimaryKey($iIDNews)) {
            $iIDNews   = Contenido_Security::toInteger($iIDNews);
            $iIDCatArt = Contenido_Security::toInteger($iIDCatArt);
            $lang      = Contenido_Security::toInteger($lang);
            $client    = Contenido_Security::toInteger($client);
            $sName     = Contenido_Security::escapeDB($sName, null);

            $oItem = parent::create();

            $oItem->set("idnews", $iIDNews);
            $oItem->set("idclient", $client);
            $oItem->set("idlang", $lang);

            if ($sName == "") {
                $oItem->set("name", $oNewsletter->get("name"));
            } else {
                $oItem->set("name", $sName);
            }
            $oItem->set("type", $oNewsletter->get("type"));
            $oItem->set("use_cronjob", $oNewsletter->get("use_cronjob"));

            $oLang = new cApiLanguage($lang);
            $oItem->set("encoding", $oLang->get("encoding"));
            unset ($oLang);

            $oItem->set("idart", $oNewsletter->get("idart"));
            $oItem->set("subject", $oNewsletter->get("subject"));

            // Precompile messages
            #$sPath = $cfgClient[$client]["path"]["htmlpath"]."front_content.php?changelang=".$lang."&idcatart=".$iIDCatArt."&";
            $sPath = Contenido_Url::getInstance()->build(array('idcatart' => $iIDCatArt, 'client' => $client, 'lang' => $lang), true);
            $sPath .= ((strpos($sPath, '?') === false) ? '?' : '&');

            $sMessageText = $oNewsletter->get("message");

            // Preventing double lines in mail, you may wish to disable this function on windows servers
            if (!getSystemProperty("newsletter", "disable-rn-replacement")) {
                $sMessageText = str_replace("\r\n", "\n", $sMessageText);
            }

            $oNewsletter->_replaceTag($sMessageText, false, "unsubscribe", $sPath."unsubscribe={KEY}");
            $oNewsletter->_replaceTag($sMessageText, false, "change", $sPath."change={KEY}");
            $oNewsletter->_replaceTag($sMessageText, false, "stop", $sPath."stop={KEY}");
            $oNewsletter->_replaceTag($sMessageText, false, "goon", $sPath."goon={KEY}");

            $oItem->set("message_text", $sMessageText);

            if ($oNewsletter->get("type") == "text") {
                // Text newsletter, no html message
                $sMessageHTML = "";
            } else {
                // HTML newsletter, get article content
                $sMessageHTML = $oNewsletter->getHTMLMessage();

                if ($sMessageHTML) {
                    $oNewsletter->_replaceTag($sMessageHTML, true, "name", "MAIL_NAME");
                    $oNewsletter->_replaceTag($sMessageHTML, true, "number", "MAIL_NUMBER");
                    $oNewsletter->_replaceTag($sMessageHTML, true, "date", "MAIL_DATE");
                    $oNewsletter->_replaceTag($sMessageHTML, true, "time", "MAIL_TIME");

                    $oNewsletter->_replaceTag($sMessageHTML, true, "unsubscribe", $sPath."unsubscribe={KEY}");
                    $oNewsletter->_replaceTag($sMessageHTML, true, "change", $sPath."change={KEY}");
                    $oNewsletter->_replaceTag($sMessageHTML, true, "stop", $sPath."stop={KEY}");
                    $oNewsletter->_replaceTag($sMessageHTML, true, "goon", $sPath."goon={KEY}");

                    // Replace plugin tags by simple MAIL_ tags
                    if (getSystemProperty("newsletter", "newsletter-recipients-plugin") == "true") {
                        if (is_array($cfg['plugins']['recipients'])) {
                            foreach ($cfg['plugins']['recipients'] as $sPlugin) {
                                plugin_include("recipients", $sPlugin."/".$sPlugin.".php");
                                if (function_exists("recipients_".$sPlugin."_wantedVariables")) {
                                    $aPluginVars = array();
                                    $aPluginVars = call_user_func("recipients_".$sPlugin."_wantedVariables");

                                    foreach ($aPluginVars as $sPluginVar) {
                                        $oNewsletter->_replaceTag($sMessageHTML, true, $sPluginVar, "MAIL_".strtoupper($sPluginVar));
                                    }
                                }
                            }
                        }
                    }
                } else {
                    // There was a problem getting html message (maybe article deleted)
                    // Cancel job generation
                    return false;
                }
            }

            $oItem->set("message_html", $sMessageHTML);

            $oItem->set("newsfrom", $oNewsletter->get("newsfrom"));
            if ($oNewsletter->get("newsfromname") == "") {
                $oItem->set("newsfromname", $oNewsletter->get("newsfrom"));
            } else {
                $oItem->set("newsfromname", $oNewsletter->get("newsfromname"));
            }
            $oItem->set("newsdate", date("Y-m-d H:i:s"), false); //$oNewsletter->get("newsdate"));
            $oItem->set("dispatch", $oNewsletter->get("dispatch"));
            $oItem->set("dispatch_count", $oNewsletter->get("dispatch_count"));
            $oItem->set("dispatch_delay", $oNewsletter->get("dispatch_delay"));

            // Store "send to" info in serialized array (just info)
            $aSendInfo   = array();
            $aSendInfo[] = $oNewsletter->get("send_to");

            switch ($oNewsletter->get("send_to")) {
                case "selection":
                    $oGroups = new RecipientGroupCollection;
                    $oGroups->setWhere("idnewsgroup", unserialize($oNewsletter->get("send_ids")), "IN");
                    $oGroups->setOrder("groupname");
                    $oGroups->query();
                    #$oGroups->select("idnewsgroup IN ('" . implode("','", unserialize($oNewsletter->get("send_ids"))) . "')", "", "groupname");

                    while ($oGroup = $oGroups->next()) {
                        $aSendInfo[] = $oGroup->get("groupname");
                    }

                    unset ($oGroup);
                    unset ($oGroups);
                    break;
                case "single":
                    if (is_numeric($oNewsletter->get("send_ids"))) {
                        $oRcp = new Recipient($oNewsletter->get("send_ids"));

                        if ($oRcp->get("name") == "") {
                            $aSendInfo[] = $oRcp->get("email");
                        } else {
                            $aSendInfo[] = $oRcp->get("name");
                        }
                        $aSendInfo[] = $oRcp->get("email");

                        unset($oRcp);
                    }
                    break;
                default:
            }
            $oItem->set("send_to", serialize($aSendInfo), false);

            $oItem->set("created", date("Y-m-d H:i:s"), false);
            $oItem->set("author", $auth->auth["uid"]);
            $oItem->set("authorname", $auth->auth["uname"]);
            unset ($oNewsletter); // Not needed anymore

            // Adds log items for all recipients and returns recipient count
            $oLogs = new cNewsletterLogCollection();
            $iRecipientCount = $oLogs->initializeJob($oItem->get($oItem->primaryKey), $iIDNews);
            unset ($oLogs);

            $oItem->set("rcpcount", $iRecipientCount);
            $oItem->set("sendcount", 0);
            $oItem->set("status", 1); // Waiting for sending; note, that status will be set to 9, if $iRecipientCount = 0 in store() method

            $oItem->store();

            return $oItem;
        } else {
            return false;
        }
    }

    /**
     * Overridden delete method to remove job details (logs) from newsletter logs table
     * before deleting newsletter job
     *
     * @param $iItemID int specifies the frontend user group
     */
    public function delete($iItemID)
    {
        $oLogs = new cNewsletterLogCollection();
        $oLogs->delete($iItemID);

        parent::delete($iItemID);
    }
}


/**
 * Single NewsletterJob Item
 */
class cNewsletterJob extends Item
{
    /**
     * Constructor Function
     * @param  mixed  $mId  Specifies the ID of item to load
     */
    public function __construct($mId = false)
    {
        global $cfg;
        parent::__construct($cfg["tab"]["news_jobs"], "idnewsjob");
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }

    /** @deprecated  [2011-03-15] Old constructor function for downwards compatibility */
    public function cNewsletterJob($mId = false)
    {
        cWarning(__FILE__, __LINE__, "Deprecated method call, use __construct()");
        $this->__construct($mId);
    }

    public function runJob()
    {
        global $cfg, $cfgClient, $client, $recipient;

        $iCount = 0;
        if ($this->get("status") == 2) {
            // Job is currently running, check start time and restart if
            // started 5 minutes ago
            $dStart = strtotime($this->get("started"));
            $dNow   = time();

            if (($dNow - $dStart) > (5 * 60)) {
                $this->set("status", 1);
                $this->set("started", "0000-00-00 00:00:00", false);

                $oLogs = new cNewsletterLogCollection();
                $oLogs->setWhere("idnewsjob", $this->get($this->primaryKey));
                $oLogs->setWhere("status", "sending");
                $oLogs->query();

                while ($oLog = $oLogs->next()) {
                    $oLog->set("status", "error (sending)");
                    $oLog->store();
                }
            }
        }

        if ($this->get("status") == 1) {
            // Job waiting for sending
            $this->set("status", 2);
            $this->set("started",  date("Y-m-d H:i:s"), false);
            $this->store();

            // Initialization
            $aMessages = array();

            $oLanguage = new cApiLanguage($this->get("idlang"));
            $sFormatDate = $oLanguage->getProperty("dateformat", "date");
            $sFormatTime = $oLanguage->getProperty("dateformat", "time");
            unset ($oLanguage);

            if ($sFormatDate == "") {
                $sFormatDate = 'Y-m-d';
            }
            if ($sFormatTime == "") {
                $sFormatTime = 'h:i a';
            }

            // Get newsletter data
            $sFrom         = $this->get("newsfrom");
            $sFromName     = $this->get("newsfromname");
            $sSubject      = $this->get("subject");
            $sMessageText  = $this->get("message_text");
            $sMessageHTML  = $this->get("message_html");
            $dNewsDate     = strtotime($this->get("newsdate"));
            $sEncoding     = $this->get("encoding");
            $bIsHTML       = false;
            if ($this->get("type") == "html" && $sMessageHTML != "") {
                $bIsHTML = true;
            }

            $bDispatch = false;
            if ($this->get("dispatch") == 1) {
                $bDispatch = true;
            }

            // Single replacements
            // Replace message tags (text message)
            $sMessageText = str_replace("MAIL_DATE", date($sFormatDate, $dNewsDate), $sMessageText);
            $sMessageText = str_replace("MAIL_TIME", date($sFormatTime, $dNewsDate), $sMessageText);
            $sMessageText = str_replace("MAIL_NUMBER", $this->get("rcpcount"), $sMessageText);

            // Replace message tags (html message)
            if ($bIsHTML) {
                $sMessageHTML = str_replace("MAIL_DATE", date($sFormatDate, $dNewsDate), $sMessageHTML);
                $sMessageHTML = str_replace("MAIL_TIME", date($sFormatTime, $dNewsDate), $sMessageHTML);
                $sMessageHTML = str_replace("MAIL_NUMBER", $this->get("rcpcount"), $sMessageHTML);
                
                # Link to online article -->
                if (!is_object($db)) {
                    $db = new DB_Contenido;
                }
                $sql = 'SELECT idart
                        FROM ' . $cfg['tab']['news'] . '
                        WHERE (idnews=' . $this->get('idnews') . ')';
                $db->query($sql);
                $db->next_record();
                $news_idart = $db->f('idart');
                $link = Contenido_Url::getInstance()->build(array('idart' => $news_idart, 'client' => $this->get('idclient'), 'lang' => $this->get("idlang"), 'nl' => $this->get('idnewsjob'), 'rcp' => '{RCP}'), true);
                $p1 = strpos($sMessageHTML, '<body');
                if ($p1 !== false) {
                    $p1 = (strpos($sMessageHTML, '>', $p1) + 1);
                } else {
                    $p1 = 0;
                }
                $sOnlineText = getEffectiveSetting('newsletter-online-text', $this->get("idlang"), 'If the newsletter is not shown properly, please click here to view the online version.');
                $sMessageHTML = substr($sMessageHTML, 0, $p1) . '<div style="text-align: center; background-color: #FFF;"><a href="' . $link . '" style="font-weight: bold;">' . $sOnlineText . '</a></div>' . substr($sMessageHTML, $p1);
                # <-- Link to online article
                
                // Remove base tag
                $sMessageHTML = preg_replace('/<base href=(.*?)>/is', '', $sMessageHTML, 1);
                
                // Fix source path
                // TODO: Test any URL specification that may exist under the sun...
                $sMainURL = Contenido_Url::getInstance()->build(array('idcat' => getEffectiveSetting('navigation', 'idcat-home', 1), 'client' => $this->get('idclient'), 'lang' => $this->get("idlang")), true);
                $sSelfURL = Contenido_Url::getInstance()->build(array('idart' => $this->get("idart"), 'client' => $this->get('idclient'), 'lang' => $this->get("idlang")), true);
                $sMessageHTML = preg_replace("/(href|src)\=(\"|\')([^(http|#)])(\/)?/", "$1="."$2".$sMainURL."$3", $sMessageHTML);
                $sMessageHTML = preg_replace('/url\([\"\'](.*)[\"\']\)/', 'url(\''.$sMainURL.'$1\')', $sMessageHTML);
                $sMessageHTML = str_replace('/cms//', '/', $sMessageHTML);
                // Now replace anchor tags to the newsletter article itself just by the anchor
                $sMessageHTML = preg_replace("/(href|src)\=(\"|\')".str_replace('/', '\\/', $sSelfURL)."(.*)#(.*)(\"|\')/", "$1="."$2"."#"."$4"."$5", $sMessageHTML);
                // Now correct mailto tags
                $sMessageHTML = str_replace($sMainURL . 'mailto:', 'mailto:', $sMessageHTML);
                
                # Remove the <noscript> info from the newsletter message
                $sMessageHTML = str_replace(array('This website is powered by drugCMS, the Content Management System with addictive potential.', 'For more info and download visit <a href="http://www.drugcms.org">www.drugcms.org</a>.', 'drugCMS is made in Germany.'), '', $sMessageHTML);
            }

            // Enabling plugin interface
            $bPluginEnabled = false;
            if (getSystemProperty("newsletter", "newsletter-recipients-plugin") == "true") {
                $bPluginEnabled = true;
                $aPlugins       = array();

                if (is_array($cfg['plugins']['recipients'])) {
                    foreach ($cfg['plugins']['recipients'] as $sPlugin) {
                        plugin_include("recipients", $sPlugin."/".$sPlugin.".php");
                        if (function_exists("recipients_".$sPlugin."_wantedVariables")) {
                            $aPlugins[$sPlugin] = call_user_func("recipients_".$sPlugin."_wantedVariables");
                        }
                    }
                }
            }

            // Get recipients (from log table)
            if (!is_object($oLogs)) {
                $oLogs = new cNewsletterLogCollection;
            } else {
                $oLogs->resetQuery();
            }
            $oLogs->setWhere("idnewsjob", $this->get($this->primaryKey));
            $oLogs->setWhere("status", "pending");

            if ($bDispatch) {
                $oLogs->setLimit(0, $this->get("dispatch_count"));
            }

            $oLogs->query();
            while ($oLog = $oLogs->next()) {
                $iCount++;
                $oLog->set("status", "sending");
                $oLog->store();

                $sRcpMsgText = $sMessageText;
                $sRcpMsgHTML = $sMessageHTML;

                $sKey    = $oLog->get("rcphash");
                $sEMail = $oLog->get("rcpemail");
                $bSendHTML    = false;
                if ($oLog->get("rcpnewstype") == 1) {
                    $bSendHTML = true; // Recipient accepts html newsletter
                }

                if (strlen($sKey) == 30) { // Prevents sending without having a key
                    $sRcpMsgText = str_replace("{KEY}",     $sKey, $sRcpMsgText);
                    $sRcpMsgText = str_replace("MAIL_MAIL", $sEMail, $sRcpMsgText);
                    $sRcpMsgText = str_replace("MAIL_NAME", $oLog->get("rcpname"), $sRcpMsgText);

                    // Replace message tags (html message)
                    if ($bIsHTML && $bSendHTML) {
                        $sRcpMsgHTML = str_replace("{KEY}",     $sKey, $sRcpMsgHTML);
                        $sRcpMsgHTML = str_replace("MAIL_MAIL", $sEMail, $sRcpMsgHTML);
                        $sRcpMsgHTML = str_replace("MAIL_NAME", $oLog->get("rcpname"), $sRcpMsgHTML);
                        $sRcpMsgHTML = str_replace(urlencode('{RCP}'), $sKey, $sRcpMsgHTML);
                    }

                    if ($bPluginEnabled) {
                        // Don't change name of $recipient variable as it is used in plugins!
                        $recipient = new Recipient();
                        $recipient->loadByPrimaryKey($oLog->get("idnewsrcp"));

                        foreach ($aPlugins as $sPlugin => $aPluginVar) {
                            foreach ($aPluginVar as $sPluginVar) {
                                // Replace tags in text message
                                $sRcpMsgText = str_replace("MAIL_".strtoupper($sPluginVar), call_user_func("recipients_".$sPlugin."_getvalue", $sPluginVar), $sRcpMsgText);

                                // Replace tags in html message
                                if ($bIsHTML && $bSendHTML) {
                                    $sRcpMsgHTML = str_replace("MAIL_".strtoupper($sPluginVar), call_user_func("recipients_".$sPlugin."_getvalue", $sPluginVar), $sRcpMsgHTML);
                                }
                            }
                        }
                        unset($recipient);
                    }

                    $oMail = new PHPMailer();
                    $oMail->CharSet   = $sEncoding;
                    $oMail->IsHTML($bIsHTML && $bSendHTML);
                    $oMail->From      = $sFrom;
                    $oMail->FromName  = $sFromName;
                    $oMail->AddAddress($sEMail);
                    # Mailer Configuration -->
                    $sMailer			    = strtolower(getEffectiveSetting('newsletter', 'mailer'));
                    $sHost                  = getEffectiveSetting('newsletter', 'host');
                    $iPort                  = intval(getEffectiveSetting('newsletter', 'port'));
                    $sUsername              = getEffectiveSetting('newsletter', 'username');
                    $sPassword              = getEffectiveSetting('newsletter', 'password');
                    if (strlen($sMailer) == 0) {
                        $sMailer			= strtolower(getEffectiveSetting('email', 'mailer'));
                        $sHost              = getEffectiveSetting('email', 'host');
                        $iPort              = intval(getEffectiveSetting('email', 'port'));
                        $sUsername          = getEffectiveSetting('email', 'username');
                        $sPassword          = getEffectiveSetting('email', 'password');
                    }
                    if (strlen($sMailer) == 0) {
                        setSystemProperty('newsletter', 'mailer', 'mail');
                        $sMailer		    = 'mail';
                    }
                    if (strlen($sHost) == 0) {
                        setSystemProperty('newsletter', 'host', '');
                    }
                    if ($iPort == 0) {
                        setSystemProperty('newsletter', 'port', '25');
                        $iPort = 25;
                    }
                    if (strlen($sUsername) == 0) {
                        setSystemProperty('newsletter', 'username', '');
                    }
                    if (strlen($sPassword) == 0) {
                        setSystemProperty('newsletter', 'password', '');
                    }
                    $oMail->Mailer		    = $sMailer;
                    if ($sMailer == 'smtp') {
                        $oMail->SMTPAuth    = true;
                        $oMail->Host        = $sHost;
                        $oMail->Port        = $iPort;
                        $oMail->Username    = $sUsername;
                        $oMail->Password    = $sPassword;
                    }
                    # <-- Mailer Configuration
                    $oMail->Subject   = $sSubject;

                    if ($bIsHTML && $bSendHTML) {
                        $oMail->Body    = $sRcpMsgHTML;
                        $oMail->AltBody = $sRcpMsgText."\n\n";
                    } else {
                        $oMail->Body    = $sRcpMsgText."\n\n";
                    }

                    if ($oMail->Send()) {
                        $oLog->set("status", "successful");
                        $oLog->set("sent",     date("Y-m-d H:i:s"), false);
                    } else {
                        $oLog->set("status", "error (sending)");
                    }
                } else {
                    $oLog->set("status", "error (key)");
                }
                $oLog->store();
            }

            $this->set("sendcount", $this->get("sendcount") + $iCount);

            if ($iCount == 0 || !$bDispatch) {
                // No recipients remaining, job finished
                $this->set("status", 9);
                $this->set("finished", date("Y-m-d H:i:s"), false);
            } else if ($bDispatch) {
                // Check, if there are recipients remaining - stops job faster
                $oLogs->resetQuery();
                $oLogs->setWhere("idnewsjob", $this->get($this->primaryKey));
                $oLogs->setWhere("status", "pending");
                $oLogs->setLimit(0, $this->get("dispatch_count"));
                $oLogs->query();

                If ($oLogs->next()) {
                    // Remaining recipients found, set job back to pending
                    $this->set("status", 1);
                    $this->set("started",  "0000-00-00 00:00:00", false);
                } else {
                    // No remaining recipients, job finished
                    $this->set("status", 9);
                    $this->set("finished", date("Y-m-d H:i:s"), false);
                }
            } else {
                // Set job back to pending
                $this->set("status", 1);
                $this->set("started",  "0000-00-00 00:00:00", false);
            }
            $this->store();
        }

        return $iCount;
    }

    /**
     * Overriden store() method to set status to finished if rcpcount is 0
     */
    public function store()
    {
        if ($this->get("rcpcount") == 0) {
            // No recipients, job finished
            $this->set("status",     9);
            if ($this->get("started") == "0000-00-00 00:00:00") {
                $this->set("started", date("Y-m-d H:i:s"), false);
            }
            $this->set("finished",    date("Y-m-d H:i:s"), false);
        }

        parent::store();
    }
}

?>