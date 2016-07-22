<?php
/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * Defines the general contenido functions
 *
 * @package    Contenido Backend includes
 * @version    $Rev$
 * @author     Jan Lengowski
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 * 
 *   $Id$:
 */

if(!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

/**
 * Extracts the available content-types from the database
 *
 * Creates an array $a_content[type][number] = content string
 * f.e. $a_content['CMS_HTML'][1] = content string
 * Same for array $a_description
 *
 * @param   int  $idartlang  Language specific ID of the arcticle
 * @return  void
 *
 * @author Jan Lengowski <Jan.Lengowski@4fb.de>
 * @copyright four for business AG
 */
function getAvailableContentTypes($idartlang)
{
    global $db, $cfg, $a_content, $a_description;

    $sql = "SELECT
                *
            FROM
                ".$cfg["tab"]["content"]." AS a,
                ".$cfg["tab"]["art_lang"]." AS b,
                ".$cfg["tab"]["type"]." AS c
            WHERE
                a.idtype    = c.idtype AND
                a.idartlang = b.idartlang AND
                b.idartlang = '".Contenido_Security::toInteger($idartlang)."'";

    $db->query($sql);

    while ($db->next_record())
    {
        $a_content[$db->f("type")][$db->f("typeid")] = urldecode($db->f("value"));
        $a_description[$db->f("type")][$db->f("typeid")] = i18n($db->f("description"));
    }
}

/**
 * Checks if an article is assigned to multiple categories
 *
 * @param   int  $idart  Article-Id
 * @return  bool  Article assigned to multiple categories
 */
function isArtInMultipleUse($idart)
{
    global $cfg, $client;

    $db = new DB_Contenido;
    $sql = "SELECT idart FROM ".$cfg["tab"]["cat_art"]." WHERE idart = '".Contenido_Security::toInteger($idart)."'";
    $db->query($sql);

    return ($db->affected_rows() > 1);
}

/**
 * Checks if a value is alphanumeric
 *
 * @param   mixed  $test     Value to test
 * @param   bool   $umlauts  [Use german Umlaute] Optional
 * @return  bool   Value is alphanumeric
 */
function is_alphanumeric($test, $umlauts = true)
{

    if ($umlauts == true)
    {
        $match = "/^[a-z0-9??????? ]+$/i";
    } else
    {
        $match = "/^[a-z0-9 ]+$/i";
    }

    return (preg_match($match, $test));
}

/**
 * Returns multi-language month name (canonical) by its numeric value
 *
 * @param   int  $month
 * @return  string
 */
function getCanonicalMonth($month)
{
    switch ($month)
    {
        case 1 :
            return (i18n("January"));
            break;
        case 2 :
            return (i18n("February"));
            break;
        case 3 :
            return (i18n("March"));
            break;
        case 4 :
            return (i18n("April"));
            break;
        case 5 :
            return (i18n("May"));
            break;
        case 6 :
            return (i18n("June"));
            break;
        case 7 :
            return (i18n("July"));
            break;
        case 8 :
            return (i18n("August"));
            break;
        case 9 :
            return (i18n("September"));
            break;
        case 10 :
            return (i18n("October"));
            break;
        case 11 :
            return (i18n("November"));
            break;
        case 12 :
            return (i18n("December"));
            break;
    }
}

/**
 * Get multi-language day
 * 
 * @param   int     $iDay  The day number of date(w)
 * @return  string  Dayname of current language
 */
function getCanonicalDay($iDay)
{
    switch ($iDay)
    {
        case 1 :
            return (i18n("Monday"));
            break;
        case 2 :
            return (i18n("Tuesday"));
            break;
        case 3 :
            return (i18n("Wednesday"));
            break;
        case 4 :
            return (i18n("Thursday"));
            break;
        case 5 :
            return (i18n("Friday"));
            break;
        case 6 :
            return (i18n("Saterday"));
            break;
        case 0 :
            return (i18n("Sunday"));
            break;
        default: break;    
    }
}


/**
 * Returns the id of passed area
 *
 * @param   mixed  $area  Area name
 * @return  int
 */
function getIDForArea($area)
{
    global $client, $lang, $cfg, $sess;

    $db = new DB_Contenido;

    if (!is_numeric($area))
    {
        $sql = "SELECT
                            idarea
                        FROM
                            ".$cfg["tab"]["area"]."
                        WHERE
                            name = '".Contenido_Security::escapeDB($area, $db)."'";

        $db->query($sql);
        if ($db->next_record())
        {
            $area = $db->f(0);
        }
    }

    return $area;
}

/**
 * Returns the parent id of passed area
 *
 * @param   mixed  $area
 * @return  int
 */
function getParentAreaId($area)
{
    global $client, $lang, $cfg, $sess;

    $db = new DB_Contenido;

    if (is_numeric($area))
    {
        $sql = "SELECT
                            b.name
                        FROM
                            ".$cfg["tab"]["area"]." AS a,
                            ".$cfg["tab"]["area"]." AS b
                        WHERE
                            a.idarea = '".Contenido_Security::toInteger($area)."' AND
                            b.name = a.parent_id";
    } else
    {
        $sql = "SELECT
                            b.name
                        FROM
                            ".$cfg["tab"]["area"]." AS a,
                            ".$cfg["tab"]["area"]." AS b
                        WHERE
                            a.name = '".Contenido_Security::escapeDB($area, $db)."' AND
                            b.name = a.parent_id";

    }
    $db->query($sql);

    if ($db->next_record())
    {
        return $db->f(0);
    } else
    {
        return $area;

    }
}

/**
 * Write JavaScript to mark
 *
 * @param int $menuitem Which menuitem to mark
 * @param bool $return Return or echo script
 *
 * @author Jan Lengowski <Jan.Lengowski@4fb.de>
 * @copyright four for business AG <www.4fb.de>
 */
function markSubMenuItem($menuitem, $return = false)
{
    $str = '<script type="text/javascript">
    
            try {
            /* Check if we are in a dual-frame or a quad-frame */
            if ( parent.parent.frames[0].name == "header")
            {
                if ( parent.frames["right_top"].document.getElementById("c_'.$menuitem.'") ) {
                    menuItem = parent.frames["right_top"].document.getElementById("c_'.$menuitem.'").getElementsByTagName(\'a\')[0];
                    parent.frames["right_top"].sub.clicked(menuItem);
                }     
            } else {
            /* Check if submenuItem is existing
               and mark it */
            
                
                if ( parent.parent.frames["right"].frames["right_top"].document.getElementById("c_'.$menuitem.'") ) {
                    menuItem = parent.parent.frames["right"].frames["right_top"].document.getElementById("c_'.$menuitem.'").getElementsByTagName(\'a\')[0];
                    
                    parent.parent.frames["right"].frames["right_top"].sub.clicked(menuItem);
                } 
                
            }
            } catch (e)
            {}
        </script>';

    if ($return)
    {
        return $str;

    } else
    {
        echo $str;
    }
}

/**
 * Redirect to main area
 *
 * @param bool $send Redirect Yes/No
 *
 * @author Jan Lengowski <Jan.Lengowski@4fb.de>
 * @copyright four for business AG <www.4fb.de>
 */
function backToMainArea($send)
{
    if ($send)
    {
        /* Global vars */
        global $area, $cfg, $db, $sess, $idart, $idcat, $idartlang, $idcatart, $frame;

        /* Get main area */
        $sql = "SELECT
                            a.name
                        FROM
                            ".$cfg["tab"]["area"]." AS a,
                            ".$cfg["tab"]["area"]." AS b
                        WHERE
                            b.name      = '".Contenido_Security::escapeDB($area, $db)."' AND
                            b.parent_id = a.name";

        $db->query($sql);
        $db->next_record();

        $parent = $db->f("name");

        /* Create url string */
        $url_str = 'main.php?'.'area='.$parent.'&'.'idcat='.$idcat.'&'.'idart='.$idart.'&'.'idartlang='.$idartlang.'&'.'idcatart='.$idcatart.'&'.'force=1&'.'frame='.$frame;

        $url = $sess->url($url_str);

        /* Redirect */
        header("location: $url");
    }
}

function showLocation($area)
{
    global $db;
    global $cfgPath, $lngArea;
    global $cfg;
    global $belang;

    //Create new xml Class and load the file

    $xml = new XML_doc;
    if ($xml->load($cfg['path']['xml'].$cfg['lang'][$belang]) == false)
    {
        if ($xml->load($cfg['path']['xml'].'lang_en_US.xml') == false)
        {
            die("Unable to load any XML language file");
        }
    }

    $sql = "SELECT location
                  FROM ".$cfg["tab"]["area"]." as A, ".$cfg["tab"]["nav_sub"]." as B
                  Where A.name='".Contenido_Security::escapeDB($area, $db)."' AND A.idarea=B.idarea AND A.online='1'";

    $db->query($sql);
    if ($db->next_record())
    {

        echo "<b>".$xml->valueOf($db->f("location"))."</b>";

    } else
    {

        $sql = "SELECT parent_id
                            FROM ".$cfg["tab"]["area"]."
                            WHERE name='".Contenido_Security::escapeDB($area, $db)."' AND online='1'";
        $db->query($sql);
        $db->next_record();
        $parent = $db->f("parent_id");

        $sql = "SELECT location
                            FROM ".$cfg["tab"]["area"]." as A, ".$cfg["tab"]["nav_sub"]." as B
                            Where A.name='".Contenido_Security::escapeDB($parent, $db)."' AND A.idarea = B.idarea AND A.online='1'";

        $db->query($sql);
        $db->next_record();
        echo "<b>".$xml->valueOf($db->f("location")).$lngArea[$area]."</b>";

    }
}

function showTable($tablename)
{
    global $db;

    $sql = "SELECT * FROM $tablename";
    $db->query($sql);
    while ($db->next_record())
    {
        while (list ($key, $value) = each($db->Record))
        {
            print (is_string($key) ? "<b>$key</b>: $value | " : "");
        }
        print ("<br>");
    }
}

/**
 * Get languages for given client
 * 
 * @deprecated since ConLite version 2.0.0, use method in class cApiLanguageCollection instead
 * 
 * @param int $client
 * @return array Array of language ids
 */
function getLanguagesByClient($client) {    
    $oClLangs = new cApiLanguageCollection();
    return $oClLangs->getClientLanguages($client);
}

/**
 * Returns all languages (language ids and names) of an client
 *
 * @param   int  $client
 * @return  array  List of languages where the key is the language id and value the language name
 */
function getLanguageNamesByClient($client)
{
    global $db;
    global $cfg;

    $sql = "SELECT
                        a.idlang AS idlang,
                        b.name AS name
                    FROM
                      ".$cfg["tab"]["clients_lang"]." AS a,
                      ".$cfg["tab"]["lang"]." AS b
                    WHERE
                        idclient='".Contenido_Security::toInteger($client)."' AND
                        a.idlang = b.idlang
                    ORDER BY
                        idlang ASC";

    $db->query($sql);
    while ($db->next_record())
    {
        $list[$db->f("idlang")] = $db->f("name");
    }

    return $list;
}

function set_magic_quotes_gpc(&$code) {
    global $cfg;

    if (!$cfg['simulate_magic_quotes']) { 
        if (get_magic_quotes_gpc() == 0)
        {
            $code = addslashes($code);
        } 
    }
}


/**
 * Returns a list with all clients and languages.
 *
 * @return  array  Indexed array where the value is an assoziative array as follows:
 *                 - $arr[0]['idlang']
 *                 - $arr[0]['langname']
 *                 - $arr[0]['idclient']
 *                 - $arr[0]['clientname']
 */
function getAllClientsAndLanguages()
{
    global $db, $cfg;

    $sql = "SELECT
                a.idlang as idlang,
                a.name as langname,
                b.name as clientname,
                b.idclient as idclient
             FROM
                " .$cfg["tab"]["lang"]." as a,
                " .$cfg["tab"]["clients_lang"]." as c,
                " .$cfg["tab"]["clients"]." as b
             WHERE
                a.idlang = c.idlang AND
                c.idclient = b.idclient";
    $db->query($sql);

    $aRs = array();
    while ($db->next_record()) {
        $aRs[] = array(
            'idlang'     => $db->f('idlang'),
            'langname'   => $db->f('langname'),
            'idclient'   => $db->f('idclient'),
            'clientname' => $db->f('clientname'),
        );
    }
    return $aRs;
}

function fakeheader($time)
{
    global $con_time0;
    if (!isset ($con_time0))
    {
        $con_time0 = $time;
    }

    if ($time >= $con_time0 +1000)
    {
        $con_time0 = $time;
        header('X-pmaPing: Pong');
    } // end if
}

function recursive_copy($from_path, $to_path)
{
    mkdir($to_path, 0777);
    $old_path = getcwd();
    $this_path = getcwd();

    if (is_dir($from_path))
    {
        chdir($from_path);
        $myhandle = opendir('.');

        while (($myfile = readdir($myhandle)) !== false)
        {
            if (($myfile != ".") && ($myfile != ".."))
            {
                if (is_dir($myfile))
                {
                    recursive_copy($from_path.$myfile."/", $to_path.$myfile."/");
                    chdir($from_path);
                } else if (file_exists($myfile))
                {
                    copy($from_path.$myfile, $to_path.$myfile);
                }
            }
        }
        closedir($myhandle);
    }

    chdir($old_path);
    return;
}

function getmicrotime()
{
    list ($usec, $sec) = explode(" ", microtime());
    return ((float) $usec + (float) $sec);
}

/* Small hack to clean up unused sessions.
   As we are probably soon rewriting the
   session management, this hack is OK. */
function cleanupSessions()
{
    global $cfg;

    $db = new DB_Contenido;
    $db2 = new DB_Contenido;

    $col = new InUseCollection;

    /* Expire old backend sessions */
    $temp = new Contenido_Challenge_Crypt_Auth;
    $maxdate = date("YmdHis", (time() - ($temp->lifetime * 60)));
    $sql = 'SELECT changed, sid
            FROM ' . $cfg['tab']['phplib_active_sessions'] . '
            WHERE (name="contenido")';
    $db->query($sql);
    while ($db->next_record()) {
        if ($db->f("changed") < $maxdate) {
            $sql = "DELETE FROM ".$cfg["tab"]["phplib_active_sessions"]." WHERE sid = '".Contenido_Security::escapeDB($db->f("sid"), $db2)."'";
            $db2->query($sql);
            $col->removeSessionMarks($db->f("sid"));
        }
    }

    /* Expire old frontend sessions */
    $temp = new Contenido_Frontend_Challenge_Crypt_Auth;
    $maxdate = date("YmdHis", (time() - ($temp->lifetime * 60)));
    $sql = 'SELECT changed, sid
            FROM ' . $cfg['tab']['phplib_active_sessions'] . '
            WHERE (name<>"contenido")';
    $db->query($sql);
    while ($db->next_record()) {
        if ($db->f("changed") < $maxdate) {
            $sql = "DELETE FROM ".$cfg["tab"]["phplib_active_sessions"]." WHERE sid = '".Contenido_Security::escapeDB($db->f("sid"), $db2)."'";
            $db2->query($sql);
            $col->removeSessionMarks($db->f("sid"));
        }
    }

    /* Expire invalid InUse-Entries */
    $col->select();

    while ($c = $col->next())
    {
        $sql = "SELECT sid FROM ".$cfg["tab"]["phplib_active_sessions"]." WHERE sid = '".Contenido_Security::escapeDB($c->get("session"), $db2)."'";
        $db2->query($sql);
        if (!$db2->next_record())
        {
            $col->delete($c->get("idinuse"));
        }
    }
}

function isGroup($uid)
{
    $users = new User;

    if ($users->loadUserByUserID($uid) == false)
    {
        return true;
    } else
    {
        return false;
    }
}

function getGroupOrUserName($uid)
{
    $users = new User;

    if ($users->loadUserByUserID($uid) === false)
    {
        $groups = new Group;
        /* Yes, it's a group. Let's try to load the group members! */
        if ($groups->loadGroupByGroupID($uid) === false)
        {
            return false;
        } else
        {
            return substr($groups->getField("groupname"), 4);
        }
    } else
    {
        return $users->getField("realname");
    }
}

/**
 * getPhpModuleInfo - parses phpinfo() output
 *
 * parses phpinfo() output
 * (1) get informations for a specific module (parameter $modulname)
 * (2) get informations for all modules (no parameter for $modulname needed)
 *
 * if a specified extension doesn't exists or isn't activated an array will be returned:
 * Array
 *     (
 *          [error] => extension is not available
 *     )
 *
 *
 * to get specified information on one module use (1):
 * getPhpModuleInfo($moduleName = 'gd');
 *
 * to get all informations use (2):
 * getPhpModuleInfo($moduleName);
 *
 *
 * EXAMPLE OUTPUT (1):
 * Array
 * (
 *    [GD Support] => Array
 *        (
 *            [0] => enabled
 *         )
 * ...
 * )
 *
 *
 * EXAMPLE OUTPUT (2):
 * Array
 * (
 *     [yp] => Array
 *         (
 *              [YP Support] => Array
 *                  (
 *                      [0] => enabled
 *                   )
 *
 *         )
 * ...
 * }
 *
 * foreach ($moduleSettings as $setting => $value)
 * $setting contains the modul settings
 * $value contains the settings as an array ($value[0] => Local Value && $value[1] => Master Value)
 *
 * @param $modulName string specify modul name or if not get all settings
 *
 * @return array see above for example
 * @author Marco Jahn
 */
function getPhpModuleInfo($moduleName)
{
    $moduleSettings = array();
    ob_start();
    phpinfo(INFO_MODULES); // get information vor modules
    $string = ob_get_contents();
    ob_end_clean();

    $pieces = explode("<h2", $string); // get several modules

    foreach ($pieces as $val)
    {
        // perform a regular expression match on every module header
        preg_match("/<a name=\"module_([^<>]*)\">/", $val, $sub_key);

        // perform a regular expression match on tabs with 2 columns
        preg_match_all("/<tr[^>]*>
                <td[^>]*>(.*)<\/td>
                <td[^>]*>(.*)<\/td>/Ux", $val, $sub);

        // perform a regular expression match on tabs with 3 columns
        preg_match_all("/<tr[^>]*>
                <td[^>]*>(.*)<\/td>
                <td[^>]*>(.*)<\/td>
                <td[^>]*>(.*)<\/td>/Ux", $val, $sub_ext);

        if (isset ($moduleName)) // if $moduleName is specified
        {
            if (extension_loaded($moduleName)) //check if specified extension exists or is loaded
            {
                if ($sub_key[1] == $moduleName)
                { //create array only for specified $moduleName
                    foreach ($sub[0] as $key => $val)
                    {
                        $moduleSettings[strip_tags($sub[1][$key])] = array (strip_tags($sub[2][$key]));
                    }
                }
            } else //specified extension is not loaded or doesn't exists
                {
                $moduleSettings['error'] = 'extension is not available';
            }
        } else // $moduleName isn't specified => get everything
            {
            foreach ($sub[0] as $key => $val)
            {
                $moduleSettings[$sub_key[1]][strip_tags($sub[1][$key])] = array (strip_tags($sub[2][$key]));
            }

            foreach ($sub_ext[0] as $key => $val)
            {
                $moduleSettings[$sub_key[1]][strip_tags($sub_ext[1][$key])] = array (strip_tags($sub_ext[2][$key]), strip_tags($sub_ext[3][$key]));
            }
        }
    }
    return $moduleSettings;
}

/**
 * getBrowserInfo()
 * 
 * Get's extended browser info.
 * 
 * @return array Array containing UserAgent, BrowserName, BrowserVersion, and Plattform.
 */
function getBrowserInfo() {
    static $aBrowser;
    
    if (!is_array($aBrowser)) {
        $u_agent    = $_SERVER['HTTP_USER_AGENT'];
        $bname      = 'Unknown';
        $platform   = 'Unknown';
        $version    = '';
        
        //First get the platform?
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        } elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }
        
        // Next get the name of the useragent (yes, separately, and for good reason)
        if (preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif(preg_match('/Firefox/i',$u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif(preg_match('/Chrome/i',$u_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif(preg_match('/Safari/i',$u_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif(preg_match('/Opera/i',$u_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif(preg_match('/Netscape/i',$u_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        }
        
        // finally get the correct version number
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }
        
        // see how many we have
        $i = count($matches['browser']);
        if ($i != 1) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent,"Version") < strripos($u_agent,$ub)) {
                $version= $matches['version'][0];
            } else {
                $version= $matches['version'][1];
            }
        } else {
            $version= $matches['version'][0];
        }
        
        // check if we have a number
        if (($version == null) || ($version == '')) {
            $version = '?';
        }
        
        $aBrowser = array( 'userAgent' => $u_agent, 'name' => $bname, 'version' => $version, 'platform' => $platform);
    }
    
    return $aBrowser;
}

function isValidMail($sEMail, $bStrict = false)
{
    # 2014-07-19 Spider IT :: Function now uses the new form validation functions.
    #                         Strict now means that the email address must exist.
    cInclude('classes', 'class.form.validation.php'); # Needed for some plugins
    $ret = FormValidation::isValidEmailAddress($sEMail);
    if (($ret) && ($bStrict)) {
        $ret = FormValidation::isExistingEmailAddress($sEMail);
    }
    return $ret;
}

function htmldecode($string)
{
    global $encoding, $lang;
    
    if (isset($encoding[$lang])) {
        $ret = html_entity_decode($string, ENT_COMPAT, $encoding[$lang]);
    } else {
        $trans_tbl = get_html_translation_table(HTML_ENTITIES);
        $trans_tbl = array_flip($trans_tbl);
        $ret = strtr($string, $trans_tbl);
    }
    return $ret;
}

function rereadClients()
{
    global $cfgClient;
    global $errsite_idcat;
    global $errsite_idart;
    global $db;
    global $cfg;

    if (!is_object($db))
    {
        $db = new DB_Contenido;
    }

    $sql = "SELECT
                    idclient,
                    frontendpath,
                    htmlpath,
                    errsite_cat,
                    errsite_art
                FROM
                ".$cfg["tab"]["clients"];

    $db->query($sql);

    while ($db->next_record())
    {

        $cfgClient["set"] = "set";
        $cfgClient[$db->f("idclient")]["path"]["frontend"] = $db->f("frontendpath");
        $cfgClient[$db->f("idclient")]["path"]["htmlpath"] = $db->f("htmlpath");
        $errsite_idcat[$db->f("idclient")] = $db->f("errsite_cat");
        $errsite_idart[$db->f("idclient")] = $db->f("errsite_art");

        $cfgClient[$db->f("idclient")]["images"] = $db->f("htmlpath")."images/";
        $cfgClient[$db->f("idclient")]["upload"] = "upload/";

        $cfgClient[$db->f("idclient")]["htmlpath"]["frontend"] = $cfgClient[$db->f("idclient")]["path"]["htmlpath"];
        $cfgClient[$db->f("idclient")]["upl"]["path"] = $cfgClient[$db->f("idclient")]["path"]["frontend"]."upload/";
        $cfgClient[$db->f("idclient")]["upl"]["htmlpath"] = $cfgClient[$db->f("idclient")]["htmlpath"]["frontend"]."upload/";
        $cfgClient[$db->f("idclient")]["upl"]["frontendpath"] = "upload/";
        $cfgClient[$db->f("idclient")]["css"]["path"] = $cfgClient[$db->f("idclient")]["path"]["frontend"]."css/";
        $cfgClient[$db->f("idclient")]["js"]["path"] = $cfgClient[$db->f("idclient")]["path"]["frontend"]."js/";
        $cfgClient[$db->f("idclient")]["tpl"]["path"] = $cfgClient[$db->f("idclient")]["path"]["frontend"]."templates/";

    }
}

/**
 * Sets a system property entry
 * 
 * @modified Timo Trautmann 22.02.2008 Support for editing name and type
 *
 * @param string $type The type of the item
 * @param string $name The name of the item
 * @param string $value The value of the item
 * @param int $idsystemprop The sysprop id, use optional. If set it allows to modify type name and value
 */
function setSystemProperty($type, $name, $value, $idsystemprop = 0)
{    
    global $cfg;
    if ($type == "" || $name == "")
    {
        return false;
    }
    
    $idsystemprop = Contenido_Security::toInteger($idsystemprop);
    
    $db_systemprop = new DB_Contenido;
    
    if ($idsystemprop == 0) {
        $sql = "SELECT idsystemprop FROM ".$cfg["tab"]["system_prop"]." WHERE type='".Contenido_Security::escapeDB($type, $db_systemprop)."' AND name='".Contenido_Security::escapeDB($name, $db_systemprop)."'";
    } else {
        $sql = "SELECT idsystemprop FROM ".$cfg["tab"]["system_prop"]." WHERE idsystemprop='$idsystemprop'";
    }
    
    $db_systemprop->query($sql);

    if ($db_systemprop->num_rows() > 0)
    {
        if ($idsystemprop == 0) {
            $sql = "UPDATE ".$cfg["tab"]["system_prop"]." SET value='".Contenido_Security::filter($value, $db_systemprop)."' WHERE type='".Contenido_Security::escapeDB($type, $db_systemprop)."'
                    AND name='".Contenido_Security::escapeDB($name, $db_systemprop)."'";
        } else {
            $sql = "UPDATE ".$cfg["tab"]["system_prop"]." SET value='".Contenido_Security::filter($value, $db_systemprop)."', type='".Contenido_Security::escapeDB($type, $db_systemprop)."',
                    name='".Contenido_Security::escapeDB($name, $db_systemprop)."' WHERE idsystemprop='$idsystemprop'";
        }
    } else
    {
        $idsystemprop = $db_systemprop->nextid($cfg["tab"]["system_prop"]);
        $sql = "INSERT INTO ".$cfg["tab"]["system_prop"]." (idsystemprop, value, type, name) VALUES ('$idsystemprop', '".Contenido_Security::filter($value, $db_systemprop)."',
                '".Contenido_Security::escapeDB($type, $db_systemprop)."', '".Contenido_Security::escapeDB($name, $db_systemprop)."')";
    }

    $db_systemprop->query($sql);
    $db_systemprop->disconnect();
}

/**
 * Remove a system property entry
 * 
 * @param string $type The type of the item
 * @param string $name The name of the item
 */
function deleteSystemProperty($type, $name)
{
    global $cfg;

    $db_systemprop = new DB_Contenido;
    $type = Contenido_Security::escapeDB($type, $db_systemprop);
    $name = Contenido_Security::escapeDB($name, $db_systemprop);
    $sql = "DELETE FROM ".$cfg["tab"]["system_prop"]." WHERE type='".$type."' AND name='".$name."'";
    $db_systemprop->query($sql);
    $db_systemprop->disconnect();
}

/**
 * Retrieves all available system properties.
 * Array format:
 *
 * $array[$type][$name] = $value;
 *
 * @modified Timo Trautmann 22.02.2008 Support for editing name and type editing by primaray key idsystemprop
 * if bGetPropId is set:
 * $array[$type][$name][value] = $value;
 * $array[$type][$name][idsystemprop] = $idsystemprop;
 *
 * @param boolean bGetPropId - if true special mode is activated which generates for each property a third array, which also contains idsystemprop value
 * @return array 
 */
function getSystemProperties($bGetPropId = 0)
{
    global $cfg;

    $db_systemprop = new DB_Contenido;

    $sql = "SELECT idsystemprop, type, name, value FROM ".$cfg["tab"]["system_prop"]." ORDER BY type ASC, name ASC, value ASC";
    $db_systemprop->query($sql);
    $results = array();
    
    if ($bGetPropId) {
        while ($db_systemprop->next_record())
        {
            $results[$db_systemprop->f("type")][$db_systemprop->f("name")]['value'] = urldecode($db_systemprop->f("value"));
            $results[$db_systemprop->f("type")][$db_systemprop->f("name")]['idsystemprop'] = urldecode($db_systemprop->f("idsystemprop"));
        }
    } else {
        while ($db_systemprop->next_record())
        {
            $results[$db_systemprop->f("type")][$db_systemprop->f("name")] = urldecode($db_systemprop->f("value"));
        }
    }

    return ($results);
}

/**
 * Gets a system property entry
 * 
 * @param string $type The type of the item
 * @param string $name The name of the item
 * @return mixed boolean false if nothing was found, or 
 */
function getSystemProperty($type, $name)
{
    global $cfg;

    $db_systemprop = new DB_Contenido;

    $sql = "SELECT value FROM ".$cfg["tab"]["system_prop"]." WHERE type='".Contenido_Security::escapeDB($type, $db_systemprop)."' AND name='".Contenido_Security::escapeDB($name, $db_systemprop)."'";
    $db_systemprop->query($sql);

    if ($db_systemprop->next_record())
    {
        return urldecode($db_systemprop->f("value"));
    } else
    {
        return false;
    }
}

/** 
 * Gets system property entries 
 * 
 * @param string $type The type of the item 
 * @return array Value 
 */ 
function getSystemPropertiesByType($sType) 
{
    global $cfg;
    
    $aResult = array();
    
    $db_systemprop = new DB_Contenido;
    
    $sSQL = "SELECT name, value FROM ".$cfg["tab"]["system_prop"]." WHERE type='".Contenido_Security::escapeDB($sType, $db_systemprop)."' ORDER BY name";
    $db_systemprop->query($sSQL);
    
    while ($db_systemprop->next_record())
    {
        $aResult[$db_systemprop->f("name")] = urldecode($db_systemprop->f("value")); 
    }

    return $aResult; 
} 

/**
 * Gets a client property entry
 * 
 * @param int $client The id of the client
 * @param string $type The type of the item
 * @param string $name The name of the item
 * @return string The value of the item
 */
function getClientProperty($client, $type, $name) {    
    $oClient = new cApiClient($client);
    $value = $oClient->getProperty($type, $name);
    unset($oClient);
    return $value;
}

/**
 * Sets a client property entry
 * 
 * @param string $type The type of the item
 * @param string $name The name of the item
 * @param string $value The value of the item
 */
function setClientProperty($type, $name, $value) {    
    global $client, $cfg, $auth;
    
    if ((strlen($type)) && (strlen($name))) {
        $db_clientprop = new DB_Contenido();
        $type = Contenido_Security::escapeDB($type, $db_clientprop);
        $name = Contenido_Security::escapeDB($name, $db_clientprop);
        $value = Contenido_Security::escapeDB($value, $db_clientprop);
        $sql = 'SELECT value
                FROM ' . $cfg['tab']['properties'] . '
                WHERE ((idclient=' . $client . ')
                   AND (itemtype="clientsetting")
                   AND (type="' . $type . '")
                   AND (name="' . $name . '"))';
        $db_clientprop->query($sql);
        if ($db_clientprop->next_record()) {
            $sql = 'UPDATE ' . $cfg['tab']['properties'] . '
                    SET value = "' . $value . '",
                        modified = "' . date('Y-m-d H:i:n') . '",
                        modifiedby = "' . $auth->auth['uid'] . '"
                    WHERE ((idclient=' . $client . ')
                       AND (itemtype="clientsetting")
                       AND (type="' . $type . '")
                       AND (name="' . $name . '"))';
        } else {
            $sql = 'INSERT INTO ' . $cfg['tab']['properties'] . ' (idclient, itemtype, itemid, type, name, value, author, created, modified, modifiedby)
                    VALUES (' . $client . ', "clientsetting", 1, "' . $type . '", "' . $name . '", "' . $value . '", "' . $auth->auth['uid'] . '", "' . date('Y-m-d H:i:n') . '", "' . date('Y-m-d H:i:n') . '", "' . $auth->auth['uid'] . '")';
        }
        $db_clientprop->query($sql);
        $db_clientprop->disconnect();
        return true;
    } else {
        return false;
    }
}

/**
 * Remove a client property entry
 * 
 * @param string $type The type of the item
 * @param string $name The name of the item
 */
function deleteClientProperty($type, $name) {
    global $client, $cfg;

    $db_clientprop = new DB_Contenido();
    $type = Contenido_Security::escapeDB($type, $db_clientprop);
    $name = Contenido_Security::escapeDB($name, $db_clientprop);
    $sql = 'DELETE FROM ' . $cfg['tab']['properties'] . '
            WHERE ((idclient=' . $client . ')
               AND (itemtype="clientsetting")
               AND (itemid=1)
               AND (type="' . $type . '")
               AND (name="' . $name . '"))';
    $db_clientprop->query($sql);
    $db_clientprop->disconnect();
}

/**
 * Returns the current effective setting for a property.
 *
 * The order is:
 * System => Client => Language => Group => User
 * (where User is the most important)
 *
 * System properties can be overridden by the client, client properties
 * by the language, language properties by the group, and group properties
 * by the user.
 * 
 * @param string $type The type of the item
 * @param string $name The name of the item
 * @param string $default Optional default value
 * @param string $shortDescription Optional short description for backend overview
 * @param string $longDescription Optional long description for backend overview
 * @param array $possibleValues Optional array of possible values for backend overview (leave empty if anything goes)
 * @return mixed boolean false if nothing was found and no default value was specified
 */
function getEffectiveSetting($type, $name, $default = '', $shortDescription = '', $longDescription = '', $possibleValues = array()) {
    global $auth, $client, $lang, $cfg;
    
    if ((strlen($default)) || (strlen($shortDescription)) || (strlen($longDescription)) || (count($possibleValues))) {
        # Enter or update the setting in the database for system and client setting selection
        $db = new DB();
        $sql = 'SELECT `setting_id`
                FROM `' . $cfg['tab']['effective_settings'] . '`
                WHERE ((`type`="' . Contenido_Security::escapeDB($type) . '")
                   AND (`name`="' . Contenido_Security::escapeDB($name) . '"))';
        $db->query($sql);
        if ($db->next_record()) {
            $aUpdates = array();
            if (strlen($default)) {
                $aUpdates[] = '`default_value`="' . Contenido_Security::escapeDB($default) . '"';
            }
            if (strlen($shortDescription)) {
                $aUpdates[] = '`short_description`="' . Contenido_Security::escapeDB($shortDescription) . '"';
            }
            if (strlen($longDescription)) {
                $aUpdates[] = '`long_description`="' . Contenido_Security::escapeDB($longDescription) . '"';
            }
            if (strlen($possibleValues)) {
                $aUpdates[] = '`possible_values`="' . json_encode($possibleValues) . '"';
            }
            $sql = 'UPDATE `' . $cfg['tab']['effective_settings'] . '`
                    SET ' . implode(', ', $aUpdates) . '
                    WHERE (`setting_id`=' . $db->f('setting_id') . ')';
            $db->query($sql);
        }
        else {
            $sql = 'INSERT INTO `' . $cfg['tab']['effective_settings'] . '` (`setting_id`, `type`, `name`, `default_value`, `short_description`, `long_description`, `possible_values`)
                    VALUES (' . $db->nextid($cfg['tab']['effective_settings']) . ', "' . Contenido_Security::escapeDB($type) . '", "' . Contenido_Security::escapeDB($name) . '", "' . Contenido_Security::escapeDB($default) . '", "' . Contenido_Security::escapeDB($shortDescription) . '", "' . Contenido_Security::escapeDB($longDescription) . '", "' . json_encode($possibleValues) . '")';
            $db->query($sql);
        }
        unset($sql);
        unset($db);
    }
    
    # Get the value for the given setting
    if ($auth->auth["uid"] != "nobody") {
        $user = new User;
        $user->loadUserByUserID($auth->auth["uid"]);
        $value = $user->getUserProperty($type, $name, true); # true also respects the group properties
        unset($user);
    }
    else {
        $value = false;    
    }
    
    if ($value === false) {
        $oClient = new cApiClientLanguage(false, $client, $lang);
        $value = $oClient->getProperty($type, $name);
        unset ($oClient);
    }
    
    if ($value === false) {
        $value = getClientProperty($client, $type, $name);
    }
    
    if ($value === false) {
        $value = getSystemProperty($type, $name);
    }
    
    if ($value === false) {
        return $default;
    }
    else {
        return $value;
    }
}

/** 
 * Returns the current effective settings for a type of properties. 
 * 
 * The order is: 
 * System => Client => Group => User 
 * 
 * System properties can be overridden by the group, and group 
 * properties can be overridden by the user. 
 * 
 * @param string $type The type of the item 
 * @return array Value 
 */ 
function getEffectiveSettingsByType($sType) 
{
    global $auth, $client, $cfg, $lang;
    
    $aResult = getSystemPropertiesByType($sType);
    
    $oClient = new cApiClient($client);
    $aResult = array_merge($aResult, $oClient->getPropertiesByType($sType));
    unset ($oClient);
    
    $oClient = new cApiClientLanguage(false, $client, $lang);
    $aResult = array_merge($aResult, $oClient->getPropertiesByType($sType));
    
    unset ($oClient);
    
    if ($auth->auth["uid"] != "nobody")
    {
        $oUser = new User;
        $oUser->loadUserByUserID($auth->auth["uid"]);
        
        $aResult = array_merge($aResult, $oUser->getUserPropertiesByType($sType, true));
    }
    
    return $aResult; 
}

/**
 * Sets a generic property entry
 * 
 * @param int $idclient The id of the client (may be 0)
 * @param string $itemtype The type of the item
 * @param string $itemid The value of the item
 * @param string $type The type of the item
 * @param string $name The name of the item
 * @param string $value The value of the item
 */
function setProperty($idclient, $itemtype, $itemid, $type, $name, $value, $idproperty = 0) {
    global $cfg;
    
    $db = new DB_Contenido();
    if ($idproperty) {
        $sql = 'UPDATE `' . $cfg['tab']['properties'] . '`
                SET `idclient` = ' . intval($idclient) . ',
                    `itemtype` = "' . urlencode(Contenido_Security::escapeDB($itemtype, $db)) . '",
                    `itemid` = "' . urlencode(Contenido_Security::escapeDB($itemid, $db)) . '",
                    `type` = "' . urlencode(Contenido_Security::escapeDB($type, $db)) . '",
                    `name` = "' . urlencode(Contenido_Security::escapeDB($name, $db)) . '",
                    `value` = "' . urlencode(Contenido_Security::escapeDB($value, $db)) . '",
                    `modified` = "' . date('Y-m-d H:i:s') . '",
                    `modifiedby` = "' . $auth->auth['uid'] . '"
                WHERE (`idproperty`=' . intval($idproperty) . ')';
    }
    else {
        $sql = 'INSERT INTO `' . $cfg['tab']['properties'] . '` (`idproperty`, `idclient`, `itemtype`, `itemid`, `type`, `name`, `value`, `author`, `created`, `modified`, `modifiedby`)
                VALUES (' . $db->nextid($cfg['tab']['properties']) . ', ' . intval($idclient) . ', "' . urlencode(Contenido_Security::escapeDB($itemtype, $db)) . '", "' . urlencode(Contenido_Security::escapeDB($itemid, $db)) . '", "' . urlencode(Contenido_Security::escapeDB($type, $db)) . '", "' . urlencode(Contenido_Security::escapeDB($name, $db)) . '", "' . urlencode(Contenido_Security::escapeDB($value, $db)) . '", "' . $auth->auth['uid'] . '", "' . date('Y-m-d H:i:s') . '", "' . date('Y-m-d H:i:s') . '", "' . $auth->auth['uid'] . '")';
    }
    return ($db->query($sql));
}

/**
 * Gets a generic property entry's value
 * 
 * @param int $idclient The id of the client (may be 0)
 * @param string $itemtype The type of the item
 * @param string $itemid The value of the item
 * @param string $type The type of the item
 * @param string $name The name of the item
 * @param bool $bGetPropertyId Also get the idproperty of the entrie
 */
function getProperty($idclient, $itemtype, $itemid, $type, $name, $bGetPropertyId = false) {
    global $cfg;
    
    $db = new DB_Contenido();
    $sql = 'SELECT `idproperty`, `value`
            FROM `' . $cfg['tab']['properties'] . '`
            WHERE ((`idclient`=' . intval($idclient) . ')
               AND (`itemtype`="' . urlencode(Contenido_Security::escapeDB($itemtype, $db)) . '")
               AND (`itemid`="' . urlencode(Contenido_Security::escapeDB($itemid, $db)) . '")
               AND (`type`="' . urlencode(Contenido_Security::escapeDB($type, $db)) . '")
               AND (`name`="' . urlencode(Contenido_Security::escapeDB($name, $db)) . '"))';
    if ($db->query($sql)) {
        if ($db->next_record()) {
            if ($bGetPropertyId) {
                return array('value' => urldecode($db->f('value')), 'idproperty' => $db->f('idproperty'));
            }
            else {
                return urldecode($db->f('value'));
            }
        }
    }
}

/**
 * Gets generic properties by itemtype
 * 
 * @param string $itemtype The type of the item
 * @param int $idclient The id of the client (may be 0, or -1 for all)
 * @param bool $bGetPropertyId Also get the idproperties of the entries
 */
function getPropertiesByItemtype($itemtype, $sortby = '', $idclient = -1, $bGetPropertyId = false) {
    global $cfg;
    
    $aProperties = array();
    $db = new DB_Contenido();
    $sql = 'SELECT `idproperty`, `itemid`, `type`, `name`, `value`
            FROM `' . $cfg['tab']['properties'] . '`
            WHERE (' . ((intval($idclient) == -1) ? '' : '(`idclient`=' . intval($idclient) . ')
               AND ') . '(`itemtype`="' . urlencode(Contenido_Security::escapeDB($itemtype, $db)) . '"))' . ((strlen($sortby)) ? '
            ORDER BY `' . $sortby . '`' : '');
    if ($db->query($sql)) {
        while ($db->next_record()) {
            if ($bGetPropertyId) {
                $aProperties[] = array('itemid' => urldecode($db->f('itemid')), 'type' => urldecode($db->f('type')), 'name' => urldecode($db->f('name')), 'value' => urldecode($db->f('value')), 'idproperty' => $db->f('idproperty'));
            }
            else {
                $aProperties[] = array('itemid' => urldecode($db->f('itemid')), 'type' => urldecode($db->f('type')), 'name' => urldecode($db->f('name')), 'value' => urldecode($db->f('value')));
            }
        }
    }
    return $aProperties;
}

/**
 * retrieve list of article specifications for current client and language
 *
 * @return array list of article specifications
 */
function getArtspec()
{
    global $db, $cfg, $lang, $client;
    $sql = "SELECT artspec, idartspec, online, artspecdefault FROM ".$cfg['tab']['art_spec']."
            WHERE client='".Contenido_Security::toInteger($client)."' AND lang='".Contenido_Security::toInteger($lang)."' ORDER BY artspec ASC";
    $db->query($sql);

    $artspec = array ();

    while ($db->next_record())
    {
        $artspec[$db->f("idartspec")]['artspec'] = $db->f("artspec");
        $artspec[$db->f("idartspec")]['online'] = $db->f("online");
        $artspec[$db->f("idartspec")]['default'] = $db->f("artspecdefault");
    }
    return $artspec;
}

/**
 * add new article specification
 *
 * @param string article specification text
 *
 * @return void
 */
function addArtspec($artspectext, $online)
{
    global $db, $cfg, $lang, $client;

    if (isset ($_POST['idartspec']))
    { //update
        $sql = "UPDATE ".$cfg['tab']['art_spec']." SET 
                        artspec='".Contenido_Security::escapeDB(urldecode($artspectext), $db)."',
                        online='".Contenido_Security::toInteger($online)."'
                    WHERE idartspec=".Contenido_Security::toInteger($_POST['idartspec'])."";
        $db->query($sql);
    } else
    {
        $sql = "INSERT INTO ".$cfg['tab']['art_spec']."
                    (idartspec, client, lang, artspec, online, artspecdefault)
                    VALUES
                    (".Contenido_Security::toInteger($db->nextid($cfg['tab']['art_spec'])).", '".Contenido_Security::toInteger($client)."', '".Contenido_Security::toInteger($lang)."',
                    '".Contenido_Security::escapeDB(urldecode($artspectext), $db)."', 0, 0)";
        $db->query($sql);
    }
}

/**
 * delete specified article specification
 *
 * @param integer article specification id
 *
 * @return void
 */
function deleteArtspec($idartspec)
{
    global $db, $cfg;
    $sql = "DELETE FROM ".$cfg['tab']['art_spec']." WHERE idartspec = '".Contenido_Security::toInteger($idartspec)."'";
    $db->query($sql);

    $sql = "UPDATE ".$cfg["tab"]["art_lang"]." set artspec = '0' WHERE artspec = '".Contenido_Security::toInteger($idartspec)."'";
    $db->query($sql);
}

/**
 * set article specifications online
 *
 * flag to switch if an article specification should be shown the frontend or not
 *
 * @param integer article specification id
 * @param integer 0/1 switch the status between on an offline
 *
 * @return void
 */
function setArtspecOnline($idartspec, $online)
{
    global $db, $cfg;
    $sql = "UPDATE ".$cfg['tab']['art_spec']." SET online=".Contenido_Security::toInteger($online)." WHERE idartspec=".Contenido_Security::toInteger($idartspec)."";
    $db->query($sql);
}

/**
 * set a default article specification
 *
 * while creating a new article this defined article specification will be default setting
 *
 * @param integer article specification id
 *
 * @return void
 */
function setArtspecDefault($idartspec)
{
    global $db, $cfg, $lang, $client;
    $sql = "UPDATE ".$cfg['tab']['art_spec']." SET artspecdefault=0 WHERE client='".Contenido_Security::toInteger($client)."' AND lang='".Contenido_Security::toInteger($lang)."'";
    $db->query($sql);

    $sql = "UPDATE ".$cfg['tab']['art_spec']." SET artspecdefault=1 WHERE idartspec='".Contenido_Security::toInteger($idartspec)."'";
    $db->query($sql);
}

/**
 * Build an Article select Box
 *
 * @param string $sName Name of the SelectBox
 * @param int $iIdCat ID of the category to select an article in
 * @param string $sValue Value of the SelectBox
 * @return string HTML
 */
function buildArticleSelect($sName, $iIdCat, $sValue) {
    global $cfg, $client, $lang, $idcat;
    $db = new DB_Contenido;

    $html = '';
    $html .= '<select id="'.$sName.'" name="'.$sName.'">';
    $html .= '  <option value="">'.i18n("Please choose").'</option>';
    
    $sql = "SELECT b.title, b.idart FROM
               ".$cfg["tab"]["art"]." AS a, ".$cfg["tab"]["art_lang"]." AS b, ".$cfg["tab"]["cat_art"]." AS c
               WHERE c.idcat = '".Contenido_Security::toInteger($iIdCat)."'
               AND b.idlang = '".Contenido_Security::toInteger($lang)."' AND b.idart = a.idart and b.idart = c.idart
               ORDER BY b.title";

    $db->query($sql);
    
    while ($db->next_record())
    {
        if ($sValue != $db->f('idart'))
        {
            $html .= '<option value="'.$db->f('idart').'" style="background-color:#EFEFEF">'.$db->f('title').'</option>';
        } else{
            $html .= '<option value="'.$db->f('idart').'" style="background-color:#EFEFEF" selected="selected">'.$db->f('title').'</option>';
        }
    }
    
    $html .= '</select>';

    return $html;
}

/**
 * Build a Category / Article select Box
 *
 * @param string $sName Name of the SelectBox
 * @param string $sValue Value of the SelectBox
 * @param Integer $iLevel Value of highest level that should be shown
 * @param string $sStyle Optional style informations for select
 * @return string HTML
 */
function buildCategorySelect($sName, $sValue, $iLevel = 0, $sStyle = "")
{
    global $cfg, $client, $lang, $idcat;

    $db = new DB_Contenido;
    $db2 = new DB_Contenido;

    $html = '';
    $html .= '<select id="'.$sName.'" style="'.$sStyle.'" name="'.$sName.'">';
    $html .= '  <option value="">'.i18n("Please choose").'</option>';

    if ($iLevel > 0)
    {
        $addString = "AND c.level<$iLevel";
    }

    $sql = "SELECT a.idcat AS idcat, b.name AS name, c.level FROM
               ".$cfg["tab"]["cat"]." AS a, ".$cfg["tab"]["cat_lang"]." AS b,
               ".$cfg["tab"]["cat_tree"]." AS c WHERE a.idclient = '".Contenido_Security::toInteger($client)."'
               AND b.idlang = '".Contenido_Security::toInteger($lang)."' AND b.idcat = a.idcat AND c.idcat = a.idcat ".Contenido_Security::escapeDB($addString, $db)."
               ORDER BY c.idtree";

    $db->query($sql);

    $categories = array ();

    while ($db->next_record())
    {
        $categories[$db->f("idcat")]["name"] = $db->f("name");

        $sql2 = "SELECT level FROM ".$cfg["tab"]["cat_tree"]." WHERE idcat = '".Contenido_Security::toInteger($db->f("idcat"))."'";
        $db2->query($sql2);

        if ($db2->next_record())
        {
            $categories[$db->f("idcat")]["level"] = $db2->f("level");
        }

        $sql2 = "SELECT a.title AS title, b.idcatart AS idcatart FROM
                        ".$cfg["tab"]["art_lang"]." AS a,  ".$cfg["tab"]["cat_art"]." AS b
                        WHERE b.idcat = '".$db->f("idcat")."' AND a.idart = b.idart AND
                        a.idlang = '".Contenido_Security::toInteger($lang)."'";

        $db2->query($sql2);

        while ($db2->next_record())
        {
            $categories[$db->f("idcat")]["articles"][$db2->f("idcatart")] = $db2->f("title");
        }

    }

    foreach ($categories as $tmpidcat => $props)
    {
        $spaces = "&nbsp;&nbsp;";

        for ($i = 0; $i < $props["level"]; $i ++)
        {
            $spaces .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        }

        $tmp_val = $tmpidcat;

        if ($sValue != $tmp_val)
        {
            $html .= '<option value="'.$tmp_val.'" style="background-color:#EFEFEF">'.$spaces.">".$props["name"].'</option>';

        } else
        {

            $html .= '<option value="'.$tmp_val.'" style="background-color:#EFEFEF" selected="selected">'.$spaces.">".$props["name"].'</option>';
        }
    }

    $html .= '</select>';

    return $html;
}

/**
 * getFileExtension
 * Returns the file extension of a given file
 *
 * @param string $filename Name of the file
 * @return extension on success, false if no extension could be extracted.
 */
function getFileExtension($filename)
{
    $dotposition = strrpos($filename, ".");

    if ($dotposition !== false)
    {
        return (strtolower(substr($filename, $dotposition +1)));
    } else
    {
        return false;
    }
}

function human_readable_size($number)
{
    $base = 1024;
    $suffixes = array (" B", " KB", " MB", " GB", " TB", " PB", " EB");

    $usesuf = 0;
    $n = (float) $number; //Appears to be necessary to avoid rounding
    while ($n >= $base)
    {
        $n /= (float) $base;
        $usesuf ++;
    }

    $places = 2 - floor(log10($n));
    $places = max($places, 0);
    $retval = number_format($n, $places, ".", "").$suffixes[$usesuf];
    return $retval;
}

/**
 * Trims an array
 *
 * @param array Array to trim
 * @return array Trimmed array
 */
function trim_array($array)
{
    if (!is_array($array))
    {
        return $array;
    }

    foreach ($array as $key => $value)
    {
        $array[$key] = trim($value);
    }

    return $array;
}

/**
 * array_csort()
 *
 * Sorts a cascaded array by columns
 *
 * @param array The array to sort
 * $paramarray Fieldname, sort type, sort direction (repeated)
 *   (see array_multisort, php.net/manual/en/function.array-multisort.php)
 * @return array The sorted multi-dimensional array
 * Example:
 *   $x = array_csort($x, 'Name', SORT_STRING, SORT_ASC, 'Firstname', SORT_STRING, SORT_ASC);
 */
function array_csort()
{
    # originally coded by Ichier2003
    # optimized by Ren Mansveld
    $args = func_get_args();
    $marray = array_shift($args);
    if (count($marray)) {
        $msortline = 'return(array_multisort(';
        $i = 0;
        foreach ($args as $arg) {
            $i ++;
            if (is_string($arg)) {
                foreach ($marray as $row) {
                    $sortarr[$i][] = $row[$arg];
                }
            } else {
                $sortarr[$i] = $arg;
            }
            $msortline .= '$sortarr['.$i.'],';
        }
        $msortline .= '$marray));';
        eval($msortline);
    }
    return $marray;
}

/**
 * explodeAssociative()
 *
 * Converts a string to an associative array.
 *
 * @param string $delimiter1 Delimiter for building the array
 * @param string $delimiter2 Delimiter for separating key and value
 * @param string $string String to convert
 * @return array The created array
 */
function explodeAssociative($delimiter1, $delimiter2, $string) {
    $tmp = explode($delimiter1, $string);
    $ret = array();
    for ($i = 0, $n = count($tmp); $i < $n; $i ++) {
        $t = explode($delimiter2, $tmp[$i]);
        $ret[$t[0]] = $t[1];
    }
    return $ret;
}

/**
 * explodeCascading()
 *
 * Converts a string to a multi-dimensional array.
 *
 * @param array string $delimiters Array with delimiters to be used in their sequence
 * @param string $string String to convert
 * @return array The created array
 */
function explodeCascading($delimiters = array(), $string = '') {
    $tmp = explode($delimiters[0], $string);
    array_shift($delimiters);
    for ($i = 0, $n = count($tmp); $i < $n; $i ++) {
        $tmp[$i] = explodeCascading($delimiters, $tmp[$i]);
    }
    return $tmp;
}

/**
 * explodeLines()
 *
 * Converts a text to an array while splitting on line breaks.
 *
 * @param string $string String to convert
 * @return array The created array
 */
function explodeLines($string) {
    return explode("\n", str_replace(array("\r\n", "\r"), "\n", $string));
}

/**
 * str_ireplace_once - Replaces a string only once
 *
 * Caution: This function only takes strings as parameters,
 *          not arrays!
 * @param $find string String to find
 * @param $replace string String to replace
 * @param $subject string String to process
 *
 * @return string Processed string        
 */
function str_ireplace_once($find, $replace, $subject)
{
    $start = strpos(strtolower($subject), strtolower($find));

    if ($start === false)
    {
        return $subject;
    }

    $end = $start +strlen($find);

    $first = substr($subject, 0, $start);
    $last = substr($subject, $end, strlen($subject) - $end);

    $result = $first.$replace.$last;

    return ($result);
}

/**
 * str_ireplace_once_reverse - Replaces a string only once, in reverse direction
 *
 * Caution: This function only takes strings as parameters,
 *          not arrays!
 * @param $find string String to find
 * @param $replace string String to replace
 * @param $subject string String to process
 *
 * @return string Processed string        
 */
function str_ireplace_once_reverse($find, $replace, $subject)
{
    $start = str_rpos(strtolower($subject), strtolower($find));

    if ($start === false)
    {
        return $subject;
    }

    $end = $start +strlen($find);

    $first = substr($subject, 0, $start);
    $last = substr($subject, $end, strlen($subject) - $end);

    $result = $first.$replace.$last;

    return ($result);
}

/**
 * str_rpos - Finds a string position in reverse direction
 *
 * NOTE: The original strrpos-Function of PHP4 only finds
 *         a single character as needle.
 *
 * @param $haystack string  String to search in
 * @param $needle   string  String to search for
 * @param $start    integer Offset
 *
 * @return string Processed string        
 */
function str_rpos($haystack, $needle, $start = 0)
{
    $tempPos = strpos($haystack, $needle, $start);

    if ($tempPos === false)
    {
        if ($start == 0)
        {
            //Needle not in string at all
            return false;
        } else
        {
            //No more occurances found
            return $start -strlen($needle);
        }
    } else
    {
        //Find the next occurance
        return str_rpos($haystack, $needle, $tempPos +strlen($needle));
    }
}

/**
 * isImageMagickAvailable - checks if ImageMagick is available
 *
 * @return boolean true if ImageMagick is available        
 */
function isImageMagickAvailable()
{
    global $_imagemagickAvailable;

    if (is_bool($_imagemagickAvailable))
    {
        if ($_imagemagickAvailable === true)
        {
            return true;
        } else
        {
            return false;
        }
    }

    $output = array ();
    $retval = 0;
    
    @exec("convert", $output, $retval);

    if (!is_array($output) || count($output) == 0)
    {
        return false;
    }
    
    if (strpos($output[0], "ImageMagick") !== false)
    {
        $_imagemagickAvailable = true;
        return true;
    } else
    {
        $_imagemagickAvailable = false;
        return false;
    }
}

/**
 * isRunningFromWeb - checks if the script is being runned from the web
 *
 * @return boolean true if the script is running from the web        
 */
function isRunningFromWeb()
{
    if ($_SERVER["PHP_SELF"] == "" || php_sapi_name() == "cgi" || php_sapi_name() == "cli")
    {
        return false;
    }

    return true;
}

/**
 * getClientName: Returns the client name for a given ID
 *
 * @return string client name        
 */
function getClientName($idclient)
{
    global $cfg;

    $db = new DB_Contenido;

    $sql = "SELECT name FROM ".$cfg["tab"]["clients"]." WHERE idclient='".Contenido_Security::toInteger($idclient)."'";

    $db->query($sql);

    if ($db->next_record())
    {
        return $db->f("name");
    } else
    {
        return false;
    }
}

/**
 * getFilesInDirectory()
 *
 * Lists files in the given directory.
 *
 * @param string $path Full path to scan
 * @param $filter Filter (string/array) for selecting files (optional)
 * @param $sort Sort order (optional)
 *
 * Examples for $filter: '*.txt' or array('*.jp*g', '*.gif', '*.png').
 * Values for $sort: 'asc', 'desc', SORT_ASC, or SORT_DESC.
 */
function getFilesInDirectory($path, $filter = '*', $sort = '') {
    define('FNM_CASEFOLD', 16);
    $aFiles = array();
    if (is_dir($path)) {
        if (!is_array($filter)) {
            $filter = array($filter);
        }
        if ($oDir = opendir($path)) {
            while (($sFile = readdir($oDir)) !== false) {
                if (is_dir($path . $sFile)) {
                    continue;
                } else {
                    for ($i = 0, $n = count($filter); $i < $n; $i ++) {
                        if (fnmatch($filter[$i], $sFile, FNM_CASEFOLD)) {
                            $aFiles[] = $sFile;
                            break;
                        }
                    }
                }
            }
            closedir($oDir);
            if (strlen($sort)) {
                sort($aFiles, SORT_STRING);
                if (($sort == 'desc') || ($sort == SORT_DESC)) {
                    $aFiles = array_reverse($aFiles);
                }
            }
        }
    }
    return $aFiles;
}

/**
 * getSubdirs()
 *
 * Lists all directories in the given directory
 *
 * @param string $dir Directory to scan
 * @param int $levels Number of directory levels to list (tree)
 * @param array $__dirs Internal parameter, do not use!
 * @return array Array of subdirs
 */
function getSubdirs($dir, $levels = 1, $__dirs = array()) {
    $a = array();
    $dir = $dir . ((substr($dir, -1) == '/') ? '' : '/');
    $p = opendir($dir);
    while (($s = readdir($p)) !== false) {
        if (($s == '.') || ($s == '..')) {
            continue;
        }
        if (is_dir($dir . $s)) {
            $a[] = $dir . $s . '/';
        }
    }
    closedir($p);
    sort($a, SORT_STRING);
    for ($i = 0; $i < count($a); $i ++) {
        $__dirs[] = $a[$i];
        if ($levels > 1) {
            $__dirs = getSubdirs($a[$i], ($levels - 1), $__dirs);
        }
    }
    return $__dirs;
}

function scanDirectory($sDirectory, $bRecursive = false)
{
    if (substr($sDirectory, strlen($sDirectory) - 1, 1) == "/")
    {
        $sDirectory = substr($sDirectory, 0, strlen($sDirectory) - 1);
    }

    if (!is_dir($sDirectory))
    {
        return false;
    }

    $aFiles = array ();

    $hDirHandle = opendir($sDirectory);

    while ($sFile = readdir($hDirHandle))
    {
        $sFullpathFile = $sDirectory."/".$sFile;

        if (is_file($sFullpathFile) && is_readable($sFullpathFile))
        {
            $aFiles[] = $sFullpathFile;
        }

        if (is_dir($sFullpathFile) && $sFile != "." && $sFile != ".." && $bRecursive == true)
        {
            $aSubFiles = scanDirectory($sFullpathFile, $bRecursive);

            if (is_array($aSubFiles))
            {
                $aFiles = array_merge($aFiles, $aSubFiles);
            }
        }
    }

    return $aFiles;
}

/**
 * scanPlugins: Scans a given plugin directory and places the
 *                 found plugins into the array $cfg['plugins']
 *
 *
 * Example:
 * scanPlugins("frontendusers");
 *
 * Result:
 * $cfg['plugins']['frontendusers'] => array with all found plugins
 *
 * Note: Plugins are only "found" if the following directory structure
 *       if found:
 *
 * entity/
 *        plugin1/plugin1.php
 *        plugin2/plugin2.php
 *
 * The plugin's directory and file name have to be the
 * same, otherwise the function won't find them!
 *
 * @param $entity Name of the directory to scan
 * @return string client name        
 */
function scanPlugins($entity)
{
    global $cfg;

    $pluginorder = getSystemProperty("plugin", $entity."-pluginorder");
    $lastscantime = getSystemProperty("plugin", $entity."-lastscantime");

    $plugins = array ();

    /* Fetch and trim the plugin order */
    if ($pluginorder != "")
    {
        $plugins = explode(",", $pluginorder);

        foreach ($plugins as $key => $plugin)
        {
            $plugins[$key] = trim($plugin);
        }
    }

    $basedir = $cfg["path"]["contenido"].$cfg["path"]["plugins"]."$entity/";

    /* Don't scan all the time, but each 60 seconds */
    if ($lastscantime +60 < time())
    {
        setSystemProperty("plugin", $entity."-lastscantime", time());

        $dh = opendir($basedir);

        while (($file = readdir($dh)) !== false)
        {

            if (is_dir($basedir.$file) && $file != "includes" && $file != "." && $file != "..")
            {
                if (!in_array($file, $plugins))
                {
                    if (file_exists($basedir.$file."/".$file.".php"))
                    {
                        $plugins[] = $file;
                    }
                }
            }
        }

        foreach ($plugins as $key => $value)
        {
            if (!is_dir($basedir.$value) || !file_exists($basedir.$value."/".$value.".php"))
            {
                unset ($plugins[$key]);
            }
        }

        $pluginorder = implode(",", $plugins);
        setSystemProperty("plugin", $entity."-pluginorder", $pluginorder);
    }

    foreach ($plugins as $key => $value)
    {
        if (!is_dir($basedir.$value) || !file_exists($basedir.$value."/".$value.".php"))
        {
            unset ($plugins[$key]);
        } else
        {
            i18nRegisterDomain($entity . "_" . $value, $basedir.$value."/locale/");
        }
    }

    $cfg['plugins'][$entity] = $plugins;
}

/**
 * includePlugins: Includes plugins for a given entity
 *
 * Example:
 * includePlugins("frontendusers");
 *
 * @param $entity Name of the directory to scan
 */
function includePlugins($entity)
{
    global $cfg;

    if (is_array($cfg['plugins'][$entity]))
    {
        foreach ($cfg['plugins'][$entity] as $plugin)
        {
            plugin_include($entity, $plugin."/".$plugin.".php");
        }
    }
}

/**
 * callPluginStore: Calls the plugin's store methods
 *
 * Example:
 * callPluginStore("frontendusers");
 *
 * @param $entity Name of the directory to scan
 */
function callPluginStore($entity)
{
    global $cfg;

    /* Check out if there are any plugins */
    if (is_array($cfg['plugins'][$entity]))
    {
        foreach ($cfg['plugins'][$entity] as $plugin)
        {
            if (function_exists($entity."_".$plugin."_wantedVariables") && function_exists($entity."_".$plugin."_store"))
            {
                $wantVariables = call_user_func($entity."_".$plugin."_wantedVariables");

                if (is_array($wantVariables))
                {
                    $varArray = array ();

                    foreach ($wantVariables as $value)
                    {
                        $varArray[$value] = stripslashes($GLOBALS[$value]);
                    }
                }
                $store = call_user_func($entity."_".$plugin."_store", $varArray);
            }
        }
    }
}

function displayPlugin($entity, & $form)
{
    /* TODO: Function can't work, as $feuser is not defined (see $display = 
     * call_user_func($entity."_".$plugin."_display", $feuser);) and plugins need
     * - if data has to be shown - global objects ...
     */
    $pluginOrder = trim_array(explode(",", getSystemProperty("plugin", $entity."-pluginorder")));

    /* Check out if there are any plugins */
    if (is_array($pluginOrder))
    {
        foreach ($pluginOrder as $plugin)
        {
            if (function_exists($entity."_".$plugin."_getTitle") && function_exists($entity."_".$plugin."_display"))
            {
                $plugTitle = call_user_func($entity."_".$plugin."_getTitle");
                $display = call_user_func($entity."_".$plugin."_display", $feuser);

                if (is_array($plugTitle) && is_array($display))
                {
                    foreach ($plugTitle as $key => $value)
                    {
                        $form->add($value, $display[$key]);
                    }
                } else
                {
                    if (is_array($plugTitle) || is_array($display))
                    {
                        $form->add("WARNING", "The plugin $plugin delivered an array for the displayed titles, but did not return an array for the contents.");
                    } else
                    {
                        $form->add($plugTitle, $display);
                    }
                }
            }
        }
    }
}

/**
 * createRandomName: Creates a random name (example: Passwords)
 *
 * Example:
 * echo createRandomName(8);
 *
 * @param $nameLength Length of the generated string
 * @return string random name        
 */
function createRandomName($nameLength)
{
    $NameChars = 'abcdefghijklmnopqrstuvwxyz';
    $Vouel = 'aeiou';
    $Name = "";

    for ($index = 1; $index <= $nameLength; $index ++)
    {
        if ($index % 3 == 0)
        {
            $randomNumber = rand(1, strlen($Vouel));
            $Name .= substr($Vouel, $randomNumber -1, 1);
        } else
        {
            $randomNumber = rand(1, strlen($NameChars));
            $Name .= substr($NameChars, $randomNumber -1, 1);
        }
    }

    return $Name;
}

/**
 * sendPostRequest: Sents a HTTP POST request
 *
 * Example:
 * sendPostRequest("hostname", "serverpath/test.php", $data);
 *
 * @param $host     Hostname or domain
 * @param $pathhost Path on the host or domain
 * @param $data        Data to send
 * @param $referer    Referer (optional)
 * @param $port        Port (default: 80)
 */
function sendPostRequest($host, $path, $data, $referer = "", $port = 80)
{
    $fp = fsockopen($host, $port);

    fputs($fp, "POST $path HTTP/1.1\n");
    fputs($fp, "Host: $host\n");
    fputs($fp, "Referer: $referer\n");
    fputs($fp, "Content-type: application/x-www-form-urlencoded\n");
    fputs($fp, "Content-length: ".strlen($data)."\n");
    fputs($fp, "Connection: close\n\n");
    fputs($fp, "$data\n");

    while (!feof($fp))
    {
        $res .= fgets($fp, 128);
    }

    fclose($fp);

    return $res;
}

function is_dbfs($file)
{
    if (substr($file, 0, 5) == "dbfs:")
    {
        return true;
    }
}

function setHelpContext($area)
{
    global $cfg;

    if ($cfg['help'] == true)
    {
        $hc = "parent.parent.parent.frames[0].document.getElementById('help').setAttribute('data', '$area');";
    } else
    {
        $hc = "";
    }

    return $hc;
}

function define_if($constant, $value)
{
    if (!defined($constant))
    {
        define($constant, $value);
    }
}

/**
 * Specify platform specific newline character; PHP_EOL has been introduced in PHP 5.0.2
 * Note, that Mac seems to use \r, sorry guys 
 */
if (!defined('PHP_EOL')) 
{
    if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
    {
        define ('PHP_EOL', "\r\n"); // Windows
    } else {
        define ('PHP_EOL', "\n");   // *nix
    }
}

function locale_arsort($locale, $array)
{
    $oldlocale = setlocale(LC_COLLATE, 0);
    setlocale(LC_COLLATE, $locale);

    uasort($array, "strcoll");

    setlocale(LC_COLLATE, $oldlocale);

    return ($array);
}

/* TODO: Ask timo to document this. */
/* Note: If subarrays exists, this function currently returns the key of the array
   given by $array, and not from the subarrays (todo: add flag to allow this) */
function array_search_recursive($search, $array, $partial = false, $strict = false)
{
    foreach ($array as $key => $value)
    {
        if (is_array($value))
        {
            $val = array_search_recursive($search, $value, $partial, $strict);
            if ($val !== false)
            {
                return ($key);
            }
        } else
        {
            if ($partial == false)
            {
                if ($strict == true)
                {
                    if ($value === $search)
                    {
                        return $key;
                    }
                } else
                {
                    if ($value == $search)
                    {
                        return $key;
                    }
                }
            } else
            {
                if (strpos($value, $search) !== FALSE)
                {
                    return $key;
                }
            }

        }
    }

    return false;
}

/**
 * cDie: Contenido die-alternative
 * 
 * @author  unknown
 *
 * @param string $file       File name   (use __FILE__)
 * @param string $line    Line number (use __LINE__)
 * @param string $message Message to display
 * @return void
 */
function cDie($file, $line, $message)
{
    cError($file, $line, $message);
    die("$file $line: $message");
}

/**
 * cWarning: Contenido warning
 *
 * @param $file       File name   (use __FILE__)
 * @param $line    Line number (use __LINE__)
 * @param $message Message to display
 */
function cWarning($file, $line, $message)
{
    trigger_error("$file $line: $message", E_USER_WARNING);
}

/**
 * cError: Contenido error
 *
 * @param $file       File name   (use __FILE__)
 * @param $line    Line number (use __LINE__)
 * @param $message Message to display
 */
function cError($file, $line, $message)
{
    trigger_error("$file $line: $message", E_USER_ERROR);
}

/**
 * cDeprecated: Trigger deprecated stuff
 * 
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @param string $file File name (use __FILE__)
 * @param string $line Line number (use __LINE__)
 * @param string $message Message to display
 */
function cDeprecated($file, $line, $message) {
    if(version_compare(PHP_VERSION, '5.3.0') >= 0) {
        trigger_error($file." ".$line.": ".$message, E_USER_DEPRECATED);
    } else {
        trigger_error("$file $line: $message", E_USER_WARNING);
    }
}

/**
 * getNamedFrame: Returns the name of the numeric frame given
 *
 * @param $frame   Frame number
 * @return string  Canonical name of the frame
 */
function getNamedFrame($frame)
{
    switch ($frame)
    {
        case 1 :
            return ("left_top");
            break;
        case 2 :
            return ("left_bottom");
            break;
        case 3 :
            return ("right_top");
            break;
        case 4 :
            return ("right_bottom");
            break;
        default :
            return ("");
            break;
    }
}

/**
 * startTiming: Starts the timing for a specific function
 *
 * @param function string Name of the function 
 * @param parameters array All parameters for the function to measure
 *
 * @return int uuid for this measure process
 */
function startTiming($function, $parameters = array ())
{
    global $_timings, $cfg;

    if ($cfg["debug"]["functiontiming"] == false)
    {
        return;
    }

    /* Create (almost) unique ID */
    $uuid = md5(uniqid(rand(), true));

    if (!is_array($parameters))
    {
        cWarning(__FILE__, __LINE__, "Warning: startTiming's parameters parameter expects an array");
        $parameters = array ();
    }

    $_timings[$uuid]["parameters"] = $parameters;
    $_timings[$uuid]["function"] = $function;

    $_timings[$uuid]["start"] = getmicrotime();

    return $uuid;
}

/**
 * endAndLogTiming: Ends the timing process and logs it to the timings file
 *
 * @param uuid int UUID which has been used for timing 
 */
function endAndLogTiming($uuid)
{
    global $_timings, $cfg;

    if ($cfg["debug"]["functiontiming"] == false)
    {
        return;
    }

    $_timings[$uuid]["end"] = getmicrotime();

    $timeSpent = $_timings[$uuid]["end"] - $_timings[$uuid]["start"];

    $myparams = array ();

    /* Build nice representation of the function */
    foreach ($_timings[$uuid]["parameters"] as $parameter)
    {

        switch (gettype($parameter))
        {
            case "string" :
                $myparams[] = '"'.$parameter.'"';
                break;

            case "boolean" :
                if ($parameter == true)
                {
                    $myparams[] = "true";
                } else
                {
                    $myparams[] = "false";
                }
                break;

            default :
                if ($parameter == "")
                {
                    $myparams[] = '"'.$parameter.'"';
                } else
                {
                    $myparams[] = $parameter;
                }
        }
    }

    $parameterString = implode(", ", $myparams);

    trigger_error("calling function ".$_timings[$uuid]["function"]."(".$parameterString.") took ".$timeSpent." seconds", E_USER_NOTICE);
}

// @TODO: it's better to create a instance of DB_Contenido class, the class constructor connects also to the database. 
function checkMySQLConnectivity() {
    global $contenido_host, $contenido_database, $contenido_user, $contenido_password, $cfg;
    
    if ($cfg["database_extension"] == "mysqli") {
        if (($iPos = strpos($contenido_host, ":")) !== false) {
            list($sHost, $sPort) = explode(":", $contenido_host);
            
            $res = @ mysqli_connect($sHost, $contenido_user, $contenido_password, "", $sPort);
        } else {
            $res = @ mysqli_connect($contenido_host, $contenido_user, $contenido_password);    
        }
    } else {
        $res = @ mysql_connect($contenido_host, $contenido_user, $contenido_password);
    }
    
    $selectDb = false;
    if ( $res ) {
        if ($cfg["database_extension"] == "mysqli") {
            $selectDb = @mysqli_select_db($res, $contenido_database);
        } else {
            $selectDb = @mysql_select_db($contenido_database);
        }
    }

    if ( !$res || !$selectDb ) {
        $errortitle = i18n("MySQL Database not reachable for installation %s");
        $errortitle = sprintf($errortitle, $cfg["path"]["contenido_fullhtml"]);

        $errormessage = i18n("The MySQL Database for the installation %s is not reachable. Please check if this is a temporary problem or if it is a real fault.");
        $errormessage = sprintf($errormessage, $cfg["path"]["contenido_fullhtml"]);

        notifyOnError($errortitle, $errormessage);

        if ($cfg["contenido"]["errorpage"] != "") {
            header("Location: ".$cfg["contenido"]["errorpage"]);
        } else {
            die("Could not connect to the database server with this configuration!");
        }
        
        exit;
    } else {
        if ($cfg["database_extension"] == "mysqli") {        
            mysqli_close($res);
        } else {
            mysql_close($res);
        }
    }
}

function notifyOnError($errortitle, $errormessage)
{
    global $cfg, $encoding, $lang;

    if (file_exists($cfg["path"]["contenido"]."logs/notify.txt"))
    {
        $notifytimestamp = file_get_contents($cfg["path"]["contenido"]."logs/notify.txt");
    } else
    {
        $notifytimestamp = 0;
    }

    if ((time() - $notifytimestamp) > $cfg["contenido"]["notifyinterval"] * 60)
    {
        if ($cfg['contenido']['notifyonerror'] != "") {
            $sMailhost = getSystemProperty('system', 'mail_host');
            if ($sMailhost == '') {
                $sMailhost = 'localhost';
            } 
        
            $oMail = new PHPMailer();
            $oMail->CharSet = ((isset($encoding[$lang])) ? $encoding[$lang] : 'UTF-8');
            $oMail->Host = $sMailhost;
            $oMail->IsHTML(0);
            $oMail->WordWrap = 1000;
            $oMail->IsMail();

            $oMail->AddAddress($cfg["contenido"]["notifyonerror"], "");
            $oMail->Subject = $errortitle;
            $oMail->Body = $errormessage;
        
            /* Notify configured email */
            $oMail->Send();
        }
        /* Write last notify log file */
        file_put_contents($cfg["path"]["contenido"]."logs/notify.txt", time());
    }
}

function cIDNAEncode($sourceEncoding, $string)
{
    if (extension_loaded("iconv"))
    {
        cInclude('pear', 'Net/IDNA.php');

        $idn = Net_IDNA :: getInstance();

        $string = iconv("UTF-8", $sourceEncoding, $string);
        $string = $idn->encode($string);

        return ($string);
    }

    if (extension_loaded("recode"))
    {
        cInclude('pear', 'Net/IDNA.php');

        $idn = Net_IDNA :: getInstance();

        $string = $idn->decode($string);
        $string = recode_string("UTF-8..".$sourceEncoding, $string);
        return $string;
    }

    return $string;
}

function cIDNADecode($targetEncoding, $string)
{
    if (extension_loaded("iconv"))
    {
        cInclude('pear', 'Net/IDNA.php');

        $idn = Net_IDNA :: getInstance();

        $string = $idn->decode($string);
        $string = iconv($targetEncoding, "UTF-8", $string);

        return ($string);
    }

    if (extension_loaded("recode"))
    {
        cInclude('pear', 'Net/IDNA.php');

        $idn = Net_IDNA :: getInstance();

        $string = recode_string($targetEncoding."..UTF-8", $string);
        $string = $idn->decode($string);

        return $string;
    }

    return $string;
}

function cInitializeArrayKey (&$aArray, $sKey, $mDefault = "")
{
    if (!is_array($aArray))
    {
        if (isset($aArray))
        {
            return false;    
        }
        
        $aArray = array();
    }
    
    if (!array_key_exists($sKey, $aArray))
    {
        $aArray[$sKey] = $mDefault;    
    }
}

/**
 * Function checks current language
 * and client settings by HTTP-Params
 * and DB settings. Based on this
 * informations it will send an HTTP
 * header for right encoding.
 *
 * @param DB_Contenido $db
 * @param array $cfg global cfg-array
 * @param int $lang global language id
 * 
 * @since 4.6.18
 * 
 * @version 1.0.0
 * @author Holger Librenz
 */
function sendEncodingHeader ($db, $cfg, $lang) {
    if (array_key_exists("use_encoding", $_GET))
    {
        $use_encoding = trim(strip_tags($_GET["use_encoding"]));  
    }
    
    if (array_key_exists("use_encoding", $_POST))
    {
        $use_encoding = trim(strip_tags($_POST["use_encoding"])); 
    }
    
    if (!isset($use_encoding))
    {
        $use_encoding = true;
    }
    
    if (is_string($use_encoding))
    {
        if ($use_encoding == "false")
        {
            $use_encoding = false;  
        } else {
            $use_encoding = true;   
        }
    }
    
    if ($use_encoding != false)
    {
        $sql = "SELECT idlang, encoding FROM ".$cfg["tab"]["lang"];
        $db->query($sql);
        
        $aLanguageEncodings = array();
        
        while ($db->next_record())
        {
            $aLanguageEncodings[$db->f("idlang")] = $db->f("encoding");
        }
        
        if (array_key_exists($lang, $aLanguageEncodings))
        {
            if (!in_array($aLanguageEncodings[$lang], $cfg['AvailableCharsets']))
            {
                header("Content-Type: text/html; charset=UTF-8");
            } else {
                header("Content-Type: text/html; charset={$aLanguageEncodings[$lang]}");        
            }
        } else {
            header("Content-Type: text/html; charset=UTF-8");          
        }
    
    }
}

/**
 * IP_match
 *
 * @param string $network
 * @param string $mask
 * @param string $ip
 * @return boolean
 */
function IP_match($network, $mask, $ip)
{
    bcscale(3);
    $ip_long = ip2long($ip);
    $mask_long = ip2long($network);

    #
    # Convert mask to divider
    #
    if (preg_match('/^[0-9]+$/', $mask))
    {
        /// 212.50.13.0/27 style mask (Cisco style)
        $divider = bcpow(2, (32 - $mask));
    }
    else
    {
        /// 212.50.13.0/255.255.255.0 style mask
        $xmask = ip2long($mask);
        if ($xmask < 0)
            $xmask = bcadd(bcpow(2, 32), $xmask);
        $divider = bcsub(bcpow(2, 32), $xmask);
    }
    #
    # Test is IP within specified mask
    #
    if (floor(bcdiv($ip_long, $divider)) == floor(bcdiv($mask_long, $divider)))
    {
        # match - this IP is within specified mask
        return true;
    }
    else
    {
        # fail - this IP is NOT within specified mask
        return false;
    }
}

/**
 * function sendHtmlMail()
 *
 * Sends an html email with both html and plain text body,
 * allows to send multiple attachments, uses system and/or
 * client settings for sender information, sending method and
 * server/account information, and handles multiple primary,
 * CC and BCC recipients and a separate reply to address.
 *
 * @param string $html The HTML body of the email
 * @param string $subject The subject of the email
 * @param array $recipients Array holding either 'name' and 'email' fields,
 *                          or an array of these (multiple recipients)
 * @param array $attachments Filename (full path) or array of filenames (optional)
 * @param string $sname Sender name (optional if specified in system/client settings)
 * @param string $smail Sender email address (optional if specified in system/client settings)
 * @param string $mailer Mailer to use, one of 'mail', 'sendmail', 'qmail', 'smtp' (optional, defaults to 'mail')
 * @param string $sserver Server address for smtp transport (optional)
 * @param string $slogin Login name for smtp transport (optional)
 * @param string $spass Password for smtp transport (optional)
 * @param int $iport Port number for smtp transport (optional)
 * @param array $cc_recipients Array holding either 'name' and 'email' fields,
 *                             or an array of these (multiple recipients) (optional)
 * @param array $bcc_recipients Array holding either 'name' and 'email' fields,
 *                              or an array of these (multiple recipients) (optional)
 * @param string $reply_to Email address to send answers to (optional)
 * @param int $ilang Language number to use (optional, needed for newsletter only)
 */
function sendHtmlMail($html, $subject, $recipients, $attachments = '', $sname = '', $smail = '', $mailer = '', $sserver = '', $slogin = '', $spass = '', $iport = 0, $cc_recipients = '', $bcc_recipients = '', $reply_to = '', $ilang = 0) {
    global $encoding, $lang, $cfg;
    
    # Check and complete paramaters
    if ($ilang == 0) {
        $ilang = $lang;
    }
    if (!is_array($attachments)) {
        $attachments = array($attachments);
    }
    $sname = html_entity_decode(((strlen($sname)) ? $sname : getEffectiveSetting('email', 'sender-name')), ENT_QUOTES, $encoding[$ilang]);
    $smail = html_entity_decode(((strlen($smail)) ? $smail : getEffectiveSetting('email', 'sender-email')), ENT_QUOTES, $encoding[$ilang]);
    if (strlen(trim($sname)) == 0) {
        $sname = $smail;
    }
    $mailer = strtolower(((strlen($mailer)) ? $mailer : getEffectiveSetting('email', 'mailer', 'mail')));
    if (strlen($mailer) == 0) {
        $mailer = 'mail';
    }
    if (strtolower($mailer) == 'smtp') {
        $sserver = ((strlen($sserver)) ? $sserver : getEffectiveSetting('email', 'smtp-server'));
        $slogin = ((strlen($slogin)) ? $slogin : getEffectiveSetting('email', 'smtp-login'));
        $spass = ((strlen($spass)) ? $spass : getEffectiveSetting('email', 'smtp-password'));
        if (substr($spass, 0, 3) == '&#x') {
            $spass = html_entity_decode($spass, ENT_QUOTES, $encoding[$ilang]);
        }
        $iport = intval((($iport) ? $iport : getEffectiveSetting('email', 'smtp-port', 25)));
        if ($iport == 0) {
            $iport = 25;
        }
    }
    
    # Prfen, ob gengend Angaben vorliegen
    $bOK = true;
    if (strlen($html) == 0) {
        echo '<pre>No message specified</pre>';
        $bOK = false;
    }
    if (strlen($subject) == 0) {
        echo '<pre>No subject specified</pre>';
        $bOK = false;
    }
    if ((!is_array($recipients)) || ((strlen($recipients['email']) == 0) && (strlen($recipients[0]['email']) == 0))) {
        echo '<pre>No recipient(s) specified</pre>';
        $bOK = false;
    }
    if (strlen($smail) == 0) {
        echo '<pre>No sender email address specified</pre>';
        $bOK = false;
    }
    if (($mailer == 'smtp') && ((strlen($sserver) == 0) || (strlen($slogin) == 0) || (strlen($spass) == 0))) {
        echo '<pre>SMTP set as transport protocol, but no login data specified</pre>';
        $bOK = false;
    }
    if (!$bOK) {
        return false;
    }
    
    # Mail aufbereiten und versenden
    $oMail = new PHPMailer();
    $oLang = new Language();
    $oLang->loadByPrimaryKey($ilang);
    $oMail->setLanguage($oLang->getProperty('language', 'code'), $cfg['path']['contenido'] . $cfg['path']['external'] . 'PHPMailer/language/');
    $oMail->CharSet = $encoding[$ilang];
    $oMail->IsHTML(true);
    $oMail->Mailer = $mailer;
    if ($mailer == 'smtp') {
        $oMail->SMTPAuth = true;
        $oMail->Host     = $sserver;
        $oMail->Port     = $iport;
        $oMail->Username = $slogin;
        $oMail->Password = $spass;
    }
    $oMail->Subject = html_entity_decode($subject, ENT_QUOTES, $encoding[$ilang]);
    $oMail->From = $smail;
    $oMail->FromName = $sname;
    if (is_array($recipients[0])) {
        for ($i = 0, $n = count($recipients); $i < $n; $i ++) {
            if (strlen($recipients[$i]['email'])) {
                $oMail->AddAddress($recipients[$i]['email'], ((strlen($recipients[$i]['name'])) ? html_entity_decode($recipients[$i]['name'], ENT_QUOTES, $encoding[$ilang]) : $recipients[$i]['email']));
            }
        }
    } else {
        $oMail->AddAddress($recipients['email'], ((strlen($recipients['name'])) ? html_entity_decode($recipients['name'], ENT_QUOTES, $encoding[$ilang]) : $recipients['email']));
    }
    if (is_array($cc_recipients[0])) {
        for ($i = 0, $n = count($cc_recipients); $i < $n; $i ++) {
            if (strlen($cc_recipients[$i]['email'])) {
                $oMail->AddCC($cc_recipients[$i]['email'], ((strlen($cc_recipients[$i]['name'])) ? html_entity_decode($cc_recipients[$i]['name'], ENT_QUOTES, $encoding[$ilang]) : $cc_recipients[$i]['email']));
            }
        }
    } elseif (is_array($cc_recipients)) {
        $oMail->AddCC($cc_recipients['email'], ((strlen($cc_recipients['name'])) ? html_entity_decode($cc_recipients['name'], ENT_QUOTES, $encoding[$ilang]) : $cc_recipients['email']));
    }
    if (is_array($bcc_recipients[0])) {
        for ($i = 0, $n = count($bcc_recipients); $i < $n; $i ++) {
            if (strlen($bcc_recipients[$i]['email'])) {
                $oMail->AddBCC($bcc_recipients[$i]['email'], ((strlen($bcc_recipients[$i]['name'])) ? html_entity_decode($bcc_recipients[$i]['name'], ENT_QUOTES, $encoding[$ilang]) : $bcc_recipients[$i]['email']));
            }
        }
    } elseif (is_array($bcc_recipients)) {
        $oMail->AddBCC($bcc_recipients['email'], ((strlen($bcc_recipients['name'])) ? html_entity_decode($bcc_recipients['name'], ENT_QUOTES, $encoding[$ilang]) : $bcc_recipients['email']));
    }
    if (strlen($reply_to)) {
        $oMail->AddReplyTo($reply_to);
    }
    $oMail->Body = $html;
    # Nur-Text-Bereich -->
    $sMsg = substr($html, strpos($html, '<body'));
    $sMsg = str_replace(array("\n", '</p>', '<br />', '<br>', '</li>'), array('', "</p>\n\n", "\n", "\n", "\n"), $sMsg);
    $sMsg = trim(strip_tags($sMsg));
    $sMsg = explode("\n", $sMsg);
    for ($i = 0, $n = count($sMsg); $i < $n; $i ++) {
        $sMsg[$i] = trim($sMsg[$i]);
    }
    $sMsg = implode("\n", $sMsg);
    $sMsg = html_entity_decode($sMsg, ENT_QUOTES, $encoding[$ilang]);
    $sMsg = capiStrReplaceDiacritics($sMsg);
    # <-- Nur-Text-Bereich
    $oMail->AltBody = $sMsg;
    for ($i = 0, $n = count($attachments); $i < $n; $i ++) {
        if (is_file($attachments[$i])) {
            $oMail->AddAttachment($attachments[$i]);
        }
    }
    $oMail->WordWrap = 76;
    if ($oMail->Send()) {
        return true;
    } else {
        echo '<pre>' . $oMail->ErrorInfo . '</pre>';
    }
}

/**
 * debug()
 *
 * Shows debugging information on the website
 *
 * @param any $value The value to output
 * @param string $type The type of the value (optional)
 * Possible types:
 * '' - Text / Array / Object
 * 'sql' - SQL query
 */
function debug($value, $type = '') {
    global $debug;
    
    if (($debug) || (in_array(getEffectiveSetting('system', 'debug', 'false'), array('true', '1')))) {
        $oDbg = DebuggerFactory::getDebugger('visible_adv');
        switch (strtolower($type)) {
            case 'sql':
                $oDbg->add(str_replace(' ', '&nbsp;', htmlentities(str_replace(array(str_repeat(chr(32), 4), chr(9)), '', $value))), 'SQL');
                break;
            default:
                if (is_array($value)) {
                    $oDbg->add($value, 'Array');
                } elseif (is_object($value)) {
                    $oDbg->add($value, 'Object');
                } else {
                    $oDbg->add($value, 'String');
                }
                break;
        }
    }
}

/**
 * getImageDescription()
 *
 * Reads the image description from the database.
 *
 * @param int $idupl ID of the image record in the database
 * @return string The image description
 */
function getImageDescription($idupl) {
    global $cfg;
    
    $db = new DB_Contenido();
    $sDesc = '';
    $sql = 'SELECT description
            FROM ' . $cfg['tab']['upl_meta'] . '
            WHERE (idupl=' . $idupl . ')';
    $db->query($sql);
    if ($db->next_record()) {
        $sDesc = urldecode(str_replace(array('%0D%0A', '%0D', '%0A'), '<br />', $db->f('description')));
    }
    if (strlen(trim($sDesc)) == 0) {
        $sql = 'SELECT description
                FROM ' . $cfg['tab']['upl'] . '
                WHERE (idupl=' . $idupl . ')';
        $db->query($sql);
        if ($db->next_record()) {
            $sDesc = urldecode(str_replace(array('%0D%0A', '%0D', '%0A'), '<br />', $db->f('description')));
        }
    }
    $db->disconnect();
    return $sDesc;
}

/**
 * getInternalNotice()
 *
 * Reads the internal notice from the database
 *
 * @param int $idupl - ID of the database entry
 * @return string The internal notice
 */
function getInternalNotice($idupl) {
    global $cfg;
    
    $db = new DB_Contenido();
    $sNotice = '';
    $sql = 'SELECT internal_notice
            FROM ' . $cfg['tab']['upl_meta'] . '
            WHERE (idupl=' . $idupl . ')';
    $db->query($sql);
    if ($db->next_record()) {
        $sNotice = urldecode(str_replace(array('%0D%0A', '%0D', '%0A'), '<br />', $db->f('internal_notice')));
    }
    $db->disconnect();
    return $sNotice;
}

/**
 * getMedianame()
 *
 * Reads the media name from the database
 *
 * @param int $idupl - ID of the database entry
 * @return string The media name
 */
function getMedianame($idupl) {
    global $cfg;
    
    $db = new DB_Contenido();
    $sMedianame = '';
    $sql = 'SELECT medianame
            FROM ' . $cfg['tab']['upl_meta'] . '
            WHERE (idupl=' . $idupl . ')';
    $db->query($sql);
    if ($db->next_record()) {
        $sMedianame = urldecode(str_replace(array('%0D%0A', '%0D', '%0A'), '<br />', $db->f('medianame')));
    }
    $db->disconnect();
    return $sMedianame;
}

/**
 * getCopyright()
 *
 * Reads the copyright from the database
 *
 * @param int $idupl - ID of the database entry
 * @return string The copyright
 */
function getCopyright($idupl) {
    global $cfg;
    
    $db = new DB_Contenido();
    $sCopyright = '';
    $sql = 'SELECT copyright
            FROM ' . $cfg['tab']['upl_meta'] . '
            WHERE (idupl=' . $idupl . ')';
    $db->query($sql);
    if ($db->next_record()) {
        $sCopyright = urldecode(str_replace(array('%0D%0A', '%0D', '%0A'), '<br />', $db->f('copyright')));
    }
    $db->disconnect();
    return $sCopyright;
}

/**
 * getKeywords()
 *
 * Reads the keywords from the database
 *
 * @param int $idupl - ID of the database entry
 * @return string The keywords
 */
function getKeywords($idupl) {
    global $cfg;
    
    $db = new DB_Contenido();
    $sKeywords = '';
    $sql = 'SELECT keywords
            FROM ' . $cfg['tab']['upl_meta'] . '
            WHERE (idupl=' . $idupl . ')';
    $db->query($sql);
    if ($db->next_record()) {
        $sKeywords = urldecode(str_replace(array('%0D%0A', '%0D', '%0A'), '<br />', $db->f('keywords')));
    }
    $db->disconnect();
    return $sKeywords;
}

/**
 * getRemoteContent()
 *
 * Gets content from another server/domain
 *
 * @param string $url The address to get the content from (http/https)
 * @param int $errno Error number received
 * @param string $errmsg Error message received
 * @param string $login Login to the remote server/domain (optional)
 * @param string $password Password to the remote server/domain (optional)
 * @return string The remote content
 */
function getRemoteContent($url, &$errno, &$errmsg, $login = '', $password = '') {
    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle compressed
        CURLOPT_USERAGENT      => "spider", // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 10,       // timeout on connect
        CURLOPT_TIMEOUT        => 10,       // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
    );
    if ((strlen($login) > 0) && (strlen($password) > 0)) {
        $options[CURLOPT_USERPWD] = $login . ':' . $password;
    }
    
    $ch      = curl_init($url);
    curl_setopt_array($ch, $options);
    $content = curl_exec($ch);
    $errno   = curl_errno($ch);
    $errmsg  = curl_error($ch);
    $header  = curl_getinfo($ch);
    curl_close($ch);
    
    if (($errno == 0) && ($header['http_code'] == 200)) {
        return $content;
    } else {
        return false;
    }
}

/**
 * getRemoteContentToFile()
 *
 * Gets content from another server/domain and saves it locally
 *
 * @param string $url The address to get the content from (http/https)
 * @param string $file Full path to the file to create/overwrite
 * @param int $errno Error number received
 * @param string $errmsg Error message received
 * @param string $login Login to the remote server/domain (optional)
 * @param string $password Password to the remote server/domain (optional)
 * @return bool Success
 */
function getRemoteContentToFile($url, $file, &$errno, &$errmsg, $login = '', $password = '') {
    $ret = getRemoteContent($url, $errno, $errmsg, $login, $password);
    if ($ret !== false) {
        # Content in Datei speichern
        if ($fp = fopen($file, 'w')) {
            fputs($fp, $ret);
            fclose($fp);
            return true;
        } else {
            $errno = -1;
            $errmsg = 'Can\'t write to file ' . $file;
            return false;
        }
    } else {
        return false;
    }
}

/**
 * makeCmsType()
 * 
 * Generates a CMS_ type field
 * 
 * @param string $type Type of the field to generate
 * @param int $id Nummer of the field to generate
 * @return string Generated code for the CMS_ type field
 * 
 * Examples:
 *   echo makeCmsType('CMS_HTML', 1);
 *   echo makeCmsType('html', 1);
 */
function makeCmsType($type, $id) {
    global $db, $client, $lang, $cfg, $cfgClient, $area_tree, $sess, $perm, $area_rights, $item_rights, $_SESSION, $remakeCatTable, $remakeStrTable, $auth, $tpl, $edit, $a_content, $idartlang, $idcat, $idart;
    
    $type = strtoupper($type);
    if (substr($type, 0, 4) != 'CMS_') {
        $type = 'CMS_' . $type;
    }
    $tmp = '';
    $val = $id;
    # Den Code fuer das Feld laden
    $sql = 'SELECT *
            FROM ' . $cfg["tab"]["type"] . '
            WHERE (type="' . $type . '")';
    $db->query($sql);
    if ($db->next_record()) {
        $code = $db->f('code');
        if (!$edit) {
            $new_code = '$article = new Article($idart, $client, $lang); $tmp = urldecode($article->getContent($type, $id));';
            $code = str_replace('$tmp = urldecode($a_content["' . $type . '"][$val]);', $new_code, $code);
            $code = str_replace('$tmp = urldecode($a_content[\'' . $type . '\'][$val]);', $new_code, $code);
            $new_code = '$article = new Article($idart, $client, $lang); $tmp = $article->getContent($type, $id);';
            $code = str_replace('$tmp = $a_content["' . $type . '"][$val];', $new_code, $code);
            $code = str_replace('$tmp = $a_content[\'' . $type . '\'][$val];', $new_code, $code);
        }
        eval($code);
        $tmp = str_replace('\\\\\\', '', $tmp);
        $tmp = str_replace("\'", "'", $tmp);
        $tmp = str_replace('border="0" /', 'border="0" style="margin-right: 2px;" /', $tmp);
    }
    return $tmp;
}

/**
 * teaserText()
 *
 * Teasers a given text
 *
 * @param string $text The text to teaser
 * @param int $maxlength Max length of the teasered text
 * @return string The teasered text with &hellip; (...) if shortened
 *
 * Text gets shortened without html tags.
 */
function teaserText($text, $maxlength) {
    $sText1 = strip_tags(str_replace(array("\r\n", "\r", "\n"), ' ', $text));
    $sText2 = capiStrTrimAfterWord($sText1, intval($maxlength));
    if (strlen($sText2) < strlen($sText1)) {
        $sText2 .= '&hellip;';
    }
    return $sText2;
}

?>