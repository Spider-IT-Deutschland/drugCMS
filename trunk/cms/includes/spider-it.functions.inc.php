<?php
/**
 * Project: 
 * Spider IT Deutschland drugCMS, ConLite and Contenido Extensions
 * 
 * Description: 
 * File with useful helper functions
 * 
 * Requirements: 
 * @con_php_req 5
 *
 * @package     Frontend
 * @author      René Mansveld <R.Mansveld@Spider-IT.de>
 * @copyright   Spider IT Deutschland <www.Spider-IT.de>
 * @license     MIT <http://en.wikipedia.org/wiki/MIT_License> <http://de.wikipedia.org/wiki/MIT-Lizenz>
 *              (see below)
 * @link        http://www.Spider-IT.de
 * @link        http://www.drugcms.org
 * 
 * @file        spider-it.functions.inc.php
 * @version     1.6
 * @date        2014-03-11
 * 
 * {@internal 
 *  created     2012-09-14
 *  modified    2012-10-10
 *  modified    2012-10-14
 *  modified    2012-10-22
 *  modified    2012-10-24
 *  modified    2012-10-30
 *  modified    2012-11-21
 *  modified    2012-12-11
 *  modified    2012-12-12
 *  modified    2013-02-18
 *  modified    2014-03-11
 *
 *   $Id$:
 * }
 * 
 */

/**
 * Copyright (c) 2012-2014 Spider IT Deutschland
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without
 * limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the
 * Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions
 * of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED
 * TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 * 
 * 
 * Hiermit wird unentgeltlich, jeder Person, die eine Kopie der Software und der zugehörigen Dokumentationen (die
 * "Software") erhält, die Erlaubnis erteilt, sie uneingeschränkt zu benutzen, inklusive und ohne Ausnahme, dem
 * Recht, sie zu verwenden, kopieren, ändern, fusionieren, verlegen, verbreiten, unterlizenzieren und/oder zu
 * verkaufen, und Personen, die diese Software erhalten, diese Rechte zu geben, unter den folgenden Bedingungen:
 * 
 * Der obige Urheberrechtsvermerk und dieser Erlaubnisvermerk sind in allen Kopien oder Teilkopien der Software
 * beizulegen.
 * 
 * DIE SOFTWARE WIRD OHNE JEDE AUSDRÜCKLICHE ODER IMPLIZIERTE GARANTIE BEREITGESTELLT, EINSCHLIESSLICH DER GARANTIE
 * ZUR BENUTZUNG FÜR DEN VORGESEHENEN ODER EINEM BESTIMMTEN ZWECK SOWIE JEGLICHER RECHTSVERLETZUNG, JEDOCH NICHT
 * DARAUF BESCHRÄNKT. IN KEINEM FALL SIND DIE AUTOREN ODER COPYRIGHTINHABER FÜR JEGLICHEN SCHADEN ODER SONSTIGE
 * ANSPRÜCHE HAFTBAR ZU MACHEN, OB INFOLGE DER ERFÜLLUNG EINES VERTRAGES, EINES DELIKTES ODER ANDERS IM ZUSAMMENHANG
 * MIT DER SOFTWARE ODER SONSTIGER VERWENDUNG DER SOFTWARE ENTSTANDEN.
 */

/**
 * Functions in this file:
 *  debug()
 *  fwritecsv()
 *  sitArrayToString()
 *  sitCascadedArraySort()
 *  sitConvertCmykJpgToSrgbJpg()
 *  sitDeeperCategoriesArticlesArray()
 *  sitExplodeAssociative()
 *  sitExplodeCascading()
 *  sitExplodeLines()
 *  sitGetBrowserInfo()
 *  sitGetFilesInDirectory()
 *  sitGetImageDescription()
 *  sitGetInternalDescription()
 *  sitGetRemoteContentToFile()
 *  sitGetSubdirs()
 *  sitImgScale()
 *  sitMakeCmsType()
 *  sitMoveAllUploadFiles()
 *  sitSafeStringEscape()
 *  sitSendHtmlMail()
 *  sitSetClientProperty()
 *  sitTeaserText()
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

/**
 * debug()
 *
 * Zeigt Debugging-Informationen auf der Webseite
 *
 * Parameter:
 *   $value - Der anzuzeigende Wert
 *   $type - Der Typ des Wertes (optional)
 *       Möglichkeiten:
 *           '' - Text / Array / Object
 *           'sql' - SQL-Anweisungen (Aufruf sollte nach $db->query() erfolgen)
 *
 * Der Wert wird aufbereitet per echo auf der Webseite
 * ausgegeben, wobei die Funktion selbstständig zwischen
 * einzelnen Werte, Arrays und Objekte unterscheidet.
 * Der 2. Parameter $type dient besondere Fälle, wie z.B.
 * 'sql' für SQL-Anweisungen, welche dann individuell
 * aufbereitet werden.
 */
function debug($value, $type = '') {
    global $db, $debug;
    
    if ($debug) {
        echo '<div style="font-size: 14px;">';
        switch (strtolower($type)) {
            case 'sql':
                echo '<pre style="margin-top: 0px;">' . htmlentities(str_replace(array(str_repeat(chr(32), 4), chr(9)), '', $value)) . '</pre>Records: ' . intval(@$db->num_rows());
                break;
            default:
                if (is_array($value)) {
                    echo '<pre>' . htmlentities(sitArrayToString($value)) . '</pre>';
                } elseif (is_object($value)) {
                    echo 'Object:<pre style="margin-top: 0px;">'; var_dump($value); echo '</pre>';
                } else {
                    echo '<pre>' . htmlentities($value) . '</pre>';
                }
                break;
        }
        echo '</div>';
    }
}

/**
 * fwritecsv()
 * 
 * Ersatzfunktion für fputcsv ohne die Fehler dessen
 * 
 * Parameter:
 *   $handle - Dateihandler der zu beschreibenden Datei
 *   $fields - Feldwerte zum schreiben (Array)
 *   $delimiter - Trennzeichen zwischen den Feldwerten
 *   $enclosure - Zeichen zum Umschließen von Zeichenfolgen
 * 
 * Wie fputcsv() schreibt diese Funktion eine Zeile in eine
 * bereits geöffnete CSV-Datei, wobei sie allerdings die
 * Zeichenfolgen (Texte) in $enclosure (meist Anführungszeichen)
 * einschließt und im Text vorkommenden $enclosure verdoppelt um
 * Fehler beim Lesen zu verhindern. Zusätzlich werden Zahlen, wenn
 * $delimiter ein Punkt ist, auch in $enclosue gesetzt um die
 * Feldgrenzen klar zu definieren.
 * Der Rückgabewert ist die Anzahl der geschriebenen Zeichen, oder
 * false wenn ein Fehler aufgetreten ist (z. B. $fields kein Array).
 */
function fwritecsv($handle, $fields, $delimiter = ',', $enclosure = '"') {
    # Check if $fields is an array
    if (!is_array($fields)) {
        return false;
    }
    # Walk through the data array
    for ($i = 0, $n = count($fields); $i < $n; $i ++) {
        # Only 'correct' non-numeric values
        if (!is_numeric($fields[$i])) {
            # Duplicate in-value $enclusure's and put the value in $enclosure's
            $fields[$i] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $fields[$i]) . $enclosure;
        }
        # If $delimiter is a dot (.), also correct numeric values
        if (($delimiter == '.') && (is_numeric($fields[$i]))) {
            # Put the value in $enclosure's
            $fields[$i] = $enclosure . $fields[$i] . $enclosure;
        }
    }
    # Combine the data array with $delimiter and write it to the file
    $line = implode($delimiter, $fields) . "\n";
    fwrite($handle, $line);
    # Return the length of the written data
    return strlen($line);
}

/**
 * sitArrayToString
 *
 * Gibt ein Array ähnlich var_dump() aus
 *
 * Parameter:
 *   $array - Auszugebenses Array
 *   $depth - interner Parameter für die Formatierung
 *
 * Gibt das übergebene Array so aus, dass man den Text
 * in PHP zum Befüllen eines Arrays einfügen kann.
 * Dies ist nützlich, wenn man ein dynamisches Array, welches
 * man aus entfernter Quelle erhält, zu Entwicklungszwecke
 * zwischenspeichern möchte.
 */
function sitArrayToString($array, $depth = 0) {
    if (is_array($array)) {
        $string = "array(\n";
        $depth ++;
        foreach ($array as $key => $val) {
            $string .= str_repeat('    ', $depth) . quoteTypeWrap($key) . ' => ';
            if (is_array($val)) {            
                $string .= sitArrayToString($val, $depth) . ",\n";
            } else {
                $string .= quoteTypeWrap($val).",\n";
            }
        }
        $depth--;
        $string .= str_repeat('    ', $depth) . ")";
    }
    return $string;
}

/**
 * sitCascadedArraySort()
 *
 * Sortiert ein kaskadiertes Array nach Spalten
 *
 * Parameter:
 *   Zu sortierendes Array
 *   Liste aus Feldnamen, Sortierarten und Sortierrichtungen (siehe
 *       array_multisort, de.php.net/manual/de/function.array-multisort.php)
 *
 * Sortiert ein mehrdimentionales Array nach den angegebenen Spalten
 * nach den vorgegebenen Sortierarten und -richtungen (je Spalte anzugeben)
 * Beispiel:
 *   $x = sitCascadedArraySort($x, 'Name', SORT_STRING, SORT_ASC, 'Vorname', SORT_STRING, SORT_ASC);
 */
function sitCascadedArraySort() {
    $args = func_get_args();
    $marray = array_shift($args);
    if (count($marray)) {
        $msortline = 'return(array_multisort(';
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
 * sitConvertCmykJpgToSrgbJpg()
 *
 * Wandelt JPG-Bilder mit CMYK Farbprofil in sRGB Farbprofil um
 *
 * Parameter:
 *   $path - Kompletter Pfad zum Bild
 *
 * Da der Internet Explorer keine JPG-Bilder (.jpg / .jpeg) mit CMYK Farbprofil
 * darstellen kann, müssen diese Bilder in das sRGB Farbprofil (für das Web)
 * umgewandelt werden. Diese Funktion prüft das Bild und wandelt es ggf. um.
 */
function sitConvertCmykJpgToSrgbJpg($path) {
    if ((strtolower(substr($path, -4)) == '.jpg') || (strtolower(substr($path, -5)) == '.jpeg')) {
        exec('identify -verbose ' . $path . ' >' . $path . '.txt');
        $tmp = file($path . '.txt');
        unlink($path . '.txt');
        for ($i = 0, $n = count($tmp); $i < $n; $i ++) {
            $a = explode(':', $tmp[$i]);
            if (trim($a[0]) == 'Colorspace') {
                if (strpos($a[1], 'RGB') === false) {
                    # Bild ist in CMYK
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
 * sitDeeperCategoriesArticlesArray()
 *
 * Listet alle Artikel im angegebenen Baum/Zweig auf
 *
 * Parameter:
 *   $idcat - idcat der Kategorie der oberen Ebene
 *   $author - Login des zu suchenden Autors (optional)
 *   $ebene0 - Sollen Artikel der oberen Ebene ausgegeben werden? (true/false) (optional)
 *   $anzebenen - Wieviele Ebenen sollen ausgegeben werden? (0 = alle) (optional)
 *   $startarticle - Startartikel der Kategorien mit ausgeben? (true/false) (optional)
 *   $offline - Offline-Artikel mit ausgeben? (true/false) (optional)
 *   $aArts - interner Parameter, nicht verwenden!
 *
 * Wenn $author angegeben wird, werden nur Artikel dieses Autors
 * zurückgeliefert, andernfalls alle Artikel.
 * Rückgabewert ist ein Array mit den idarts der gefundenen Artikel.
 */
function sitDeeperCategoriesArticlesArray($idcat, $author = '', $ebene0 = false, $anzebenen = 0, $startarticle = false, $offline = false, $aArts = array()) {
    global $client, $lang;
    
    if ($ebene0) {
        $aList = new ArticleCollection(array('idcat' => intval($idcat), 'start' => $startarticle, 'offline' => $offline));
        while ($oArt = $aList->nextArticle()) {
            if (strlen($author)) {
                # Ist der Artikel von diesem Kunden?
                if ($oArt->getField('author') == $auth->auth['uname']) {
                    $aArts[] = $oArt->getField('idart');
                }
            } else {
                $aArts[] = $oArt->getField('idart');
            }
        }
    }
    if ($anzebenen > (($ebene0) ? 1 : 0)) {
        $aCats = conDeeperCategoriesArray(intval($idcat));
        for ($i = 0, $n = count($aCats); $i < $n; $i ++) {
            $aArts = sitDeeperCategoriesArticlesArray($aCats[$i], $author, true, (($ebene0) ? ($anzebenen - 1) : $anzebenen), $startarticle, $offline, $aArts);
        }
    }
    return $aArts;
}

/**
 * sitExplodeAssociative()
 *
 * Zerlegt eine Zeichenfolge in ein assoziatives Array.
 *
 * Parameter:
 *   $delimiter - Array mit Trennzeichen zum Zerlegen der Zeichenfolge
 *   $string - Zu zerlegende Zeichenfolge
 *
 * Das erste Trennzeichen bildet das Array, das zweite Trennzeichen
 * splittet auf Key und Value.
 */
function sitExplodeAssociative($delimiters = array(), $string = '') {
    $tmp = explode($delimiters[0], $string);
    $ret = array();
    for ($i = 0, $n = count($tmp); $i < $n; $i ++) {
        $t = explode($delimiters[1], $tmp[$i]);
        $ret[$t[0]] = $t[1];
    }
    return $ret;
}

/**
 * sitExplodeCascading()
 *
 * Zerlegt eine Zeichenfolge in ein kaskadiertes Array.
 *
 * Parameter:
 *   $delimiter - Array mit Trennzeichen zum Zerlegen der Zeichenfolge
 *   $string - Zu zerlegende Zeichenfolge
 *
 * Das erste Trennzeichen bildet das Hauptarray, jedes weitere
 * Trennzeichen darin ein Unterarray (mehrere Ebenen).
 */
function sitExplodeCascading($delimiters = array(), $string = '') {
    $tmp = explode($delimiters[0], $string);
    array_shift($delimiters);
    for ($i = 0, $n = count($tmp); $i < $n; $i ++) {
        $tmp[$i] = sitMultipleExplode($delimiters, $tmp[$i]);
    }
    return $tmp;
}

/**
 * sitExplodeLines()
 *
 * Zerlegt einen Text in einzelnen Zeilen
 *
 * Parameter:
 *   $string - Zu zerlegende Zeichenfolge
 *
 * Zerlegt den Text unabhängig der Zeilenumbruchart in ein Array
 * mit den einzelnen Zeilen.
 */
function sitExplodeLines($string) {
    return explode("\n", str_replace("\r\n", "\n", $string));
}

/**
 * sitGetBrowserInfo()
 * 
 * Liefert erweiterte Informationen zum Browser
 * 
 * Parameter:
 *   (keine)
 * 
 * Liefert ein Array mit: UserAgent, BrowserName, BrowserVersion, Plattform.
 */
function sitGetBrowserInfo() {
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

/**
 * sitGetFilesInDirectory()
 *
 * Liest Dateien in ein Verzeichnis
 *
 * Parameter:
 *   $path - Kompletter Pfad des zu lesenden Verzeichnisses
 *   $filter - Filter für gefundenen Dateien (optional)
 *   $sort - Sortierreihenfolge (optional)
 *
 * Liest die Dateien in ein Verzeichnis und filtert und sortiert diese bei Bedarf.
 * $filter kann ein Array mit mehrere Filter sein, z.B. array('*.jp*g', '*.gif', '*.png').
 * $sort kann 'asc', 'desc', SORT_ASC oder SORT_DESC sein, oder weggelassen werden.
 */
function sitGetFilesInDirectory($path, $filter = '*', $sort = '') {
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
 * sitGetImageDescription()
 *
 * Liest die Bildbeschreibung aus der Datenbank
 *
 * Parameter:
 *   $idupl - ID des Bildeintrags in der Datenbank
 *
 * Liest die zum Bild gehörenden Beschreibung entweder aus der Tabelle ..._upl_meta
 * oder (falls leer) aus der Tabelle ..._upl und liefert diese zurück.
 */
function sitGetImageDescription($idupl) {
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
    return $sDesc;
}

/**
 * sitGetInternalDescription()
 *
 * Liest die interne Notiz aus der Datenbank
 *
 * Parameter:
 *   $idupl - ID des Eintrags in der Datenbank
 *
 * Liest die zur datei gehörenden "interne Notiz" aus der Tabelle ..._upl_meta
 * und liefert diese zurück.
 */
function sitGetInternalDescription($idupl) {
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
    return $sNotice;
}

/**
 * sitGetRemoteContentToFile()
 *
 * Holt entfernten Inhalt ab und speichert diesen lokal
 *
 * Parameter:
 *   $url - Die Adresse von wo der Inhalt geholt werden soll
 *   $file - Die Datei in der gespeichert werden soll (inkl. Pfad)
 *   $errno - Die Fehlernummer (Rückgabe)
 *   $errmsg - Die Fehlerbeschreibung (Rückgabe)
 *
 * Die Daten (Webseite, Bild, Feed usw) werden per cURL geholt,
 * wobei Weiterleitungen gefolgt werden.
 * Diese Methode ist unabhängig von allow_url_fopen und verarbeitet
 * auch Anfragen per https (SSL).
 */
function sitGetRemoteContentToFile($url, $file, $errno, $errmsg) {
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
    
    $ch      = curl_init($url);
    curl_setopt_array($ch, $options);
    $content = curl_exec($ch);
    $errno   = curl_errno($ch);
    $errmsg  = curl_error($ch);
    $header  = curl_getinfo($ch);
    curl_close($ch);
    
    if (($errno == 0) && ($header['http_code'] == 200)) {
        # Content in Datei speichern
        if ($fp = fopen($file, 'w')) {
            fputs($fp, $content);
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
 * sitGetSubdirs()
 *
 * Listet Unterverzeichnisse eines Verzeichnisses
 *
 * Parameter:
 *   $dir - Übergeordnetes Verzeichnis
 *   $levels - Anzahl Ebenen an Unterverzeichnisse die mit aufgelistet werden sollen
 *   $__dirs - interner Parameter für Rekursion
 *
 * Listet die Unterverzeichnisse eines Verzeichnisses inkl. aller Unterverzeichnisse
 * bis zu der angegebenen Anzahl an Ebenen (die Tiefe).
 */
function sitGetSubdirs($dir, $levels = 1, $__dirs = array()) {
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
            $__dirs = sitGetSubdirs($a[$i], ($levels - 1), $__dirs);
        }
    }
    return $__dirs;
}

/**
 * sitImgScale()
 *
 * Skaliert oder zoomt ein Bild auch mit Transparenz
 *
 * Parameter:
 *   $img - Pfad und Dateiname der Originaldatei relativ zum Mandantenverzeichnis
 *   $maxX - Maximale Breite des neuen Bildes
 *   $maxY - Maximale Höhe des neuen Bildes
 *   $crop - Bild darf beschnitten werden (optional)
 *   $expand - Bild darf vergrößert werden
 *   $cacheTime - Ältere Version nutzen oder überschreiben
 *   $wantHQ - Bild soll in hoher Qualität sein
 *   $quality - Qualität bei JPG und GIF
 *   $keepType - Dateityp beibehalten
 *   $fixedSize - Zielbild wird auf angegebene Größe erstellt und transparent (GIF und PNG) gefüllt
 *   $fixedBG - Bei $fixedSize und JPG wird dies die Hintergrundfarbe des umgebenden Bereichs
 *   $cropLeft - Wenn $crop = true wird ab diese Linksposition ausgeschnitten (-1 = Bildmitte verwenden)
 *   $cropTop - Wenn $crop = true wird ab diese Topposition ausgeschnitten (-1 = Bildmitte verwenden)
 *
 * Erstellt im cache Verzeichnis eine skalierte Version des Originalbildes
 * wie auch die Con-Funktion capiImgScale(), aber behält Transparenz in GIF
 * und PNG Bilder bei. Der zusätzliche Parameter $fixedSize ermöglicht es,
 * das Zielbild mit fixe Abmessungen zu erstellen und das skalierte Bild
 * darin zu zentrieren, wobei der umgebenden Bereich bei GIF und PNG Bilder
 * transparent, bei JPG Bilder mit der in $fixedBG angegebenen Farbe gefüllt
 * wird. Ist $fixedBG nicht angegeben, wird weiß (#FFF) angenommen.
 */
function sitImgScale($img, $maxX = 0, $maxY = 0, $crop = false, $expand = false, $cacheTime = 10, $wantHQ = true, $quality = 75, $keepType = false, $fixedSize = false, $fixedBG = 'FFFFFF', $cropLeft = -1, $cropTop = -1) {
	global $cfgClient, $client, $lang;
    
    $cl = $cropLeft;
    $ct = $cropTop;
    
    if (($maxX <= 0) && ($maxY <= 0)) {
        return false;
    }
    
    # Cache
    $md5 = capiImgScaleGetMD5CacheFile($cfgClient[$client]['path']['frontend'] . $img, $maxX, $maxY, $crop, $expand);
    list($oWidth, $oHeight, $oType) = @getimagesize($cfgClient[$client]['path']['frontend'] . $img);
    if (($oType != IMAGETYPE_GIF) && ($oType != IMAGETYPE_JPEG) && ($oType != IMAGETYPE_PNG)) {
        return false;
    }
    if ($keeptype) {
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
        if ($cacheTime == 0) {
            # Datei läuft nicht ab, also direkt ausgeben
            return $webFile;
        } elseif ((filemtime($cacheFile) + (60 * $cacheTime)) < time()) {
            # Datei ist abgelaufen
            unlink($cacheFile);
        } else {
            # Datei ist noch nicht abgelaufen
            return $webFile;
        }
    }
    
    # Bild neu aufbauen
    $nLeft      = 0;
    $nTop       = 0;
    $nWidth     = 0;
    $nHeight    = 0;
    $faktor     = 1;
    if ($fixedSize) {
        $iWidth = $maxX;
        $iHeight = $maxY;
        # Abmessung und Position in neues Bild berechnen
        if (($oWidth > $maxX) || ($oHeight > $maxY) || ($expand)) {
            # Bild ist größer oder soll vergrößert werden
            if ($crop) {
                $faktor = max(($maxX / $oWidth), ($maxY / $oHeight));
            } else {
                $faktor = min($maxX / $oWidth, $maxY / $oHeight);
                $cropLeft = 0;
                $cropTop = 0;
            }
            if ($faktor == ($maxX / $oWidth)) {
                $nLeft = 0;
                $nWidth = $maxX;
                $nHeight = ceil($oHeight * $faktor);
                if ($ct == -1) {
                    $nTop = floor(($maxY - $nHeight) / 2);
                }
            } else {
                $nTop = 0;
                $nHeight = $maxY;
                $nWidth = ceil($oWidth * $faktor);
                if ($cl == -1) {
                    $nLeft = floor(($maxX - $nWidth) / 2);
                }
            }
            if ($crop) {
                if ($cropLeft == -1) {
                    $cropLeft = 0;
                } else {
                    $nLeft = 0;
                }
                if ($cropTop == -1) {
                    $cropTop = 0;
                } else {
                    $nTop = 0;
                }
            }
        } else {
            $nLeft = floor(($maxX - $oWidth) / 2);
            $nTop = floor(($maxY - $oHeight) / 2);
            $nWidth = $oWidth;
            $nHeight = $oHeight;
        }
    } else {
        # Abmessung des neuen Bildes berechnen
        if (($oWidth > $maxX) || ($oHeight > $maxY) || ($expand)) {
            if ($crop) {
                $faktor = max($maxX / $oWidth, $maxY / $oHeight);
            } else {
                $faktor = min($maxX / $oWidth, $maxY / $oHeight);
                $cropLeft = 0;
                $cropTop = 0;
            }
            if ($faktor == ($maxX / $oWidth)) {
                $nWidth = $maxX;
                $nHeight = ceil($oHeight * $faktor);
                $iWidth = $maxX;
                $iHeight = (($nHeight > $maxY) ? $maxY : $nHeight);
                if ($ct == -1) {
                    $nTop = (($nHeight > $maxY) ? floor(($maxY - $nHeight) / 2) : 0);
                }
            } else {
                $nHeight = $maxY;
                $nWidth = ceil($oWidth * $faktor);
                $iHeight = $maxY;
                $iWidth = (($nWidth > $maxX) ? $maxX : $nWidth);
                if ($cl == -1) {
                    $nLeft = (($nWidth > $maxX) ? floor(($maxX - $nWidth) / 2) : 0);
                }
            }
            if ($crop) {
                if ($cropLeft == -1) {
                    $cropLeft = 0;
                } else {
                    $nLeft = 0;
                }
                if ($cropTop == -1) {
                    $cropTop = 0;
                } else {
                    $nTop = 0;
                }
            }
        } else {
            # Bild ist kleiner und soll nicht vergrößert werden
            $iWidth = $nWidth = $oWidth;
            $iHeight = $nHeight = $oHeight;
        }
    }
    if ($cropLeft < 0) $cropLeft = 0;
    if ($cropTop < 0) $cropTop = 0;
    # Bild einlesen
    switch ($oType) {
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($img);
            break;
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($img);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($img);
            break;
        default:
            return false;
    }
    # Neues Bild erzeugen und Hintergrundfarbe einstellen
    $nImage = imagecreatetruecolor($iWidth, $iHeight);
    if (($oType == IMAGETYPE_GIF) || ($oType == IMAGETYPE_PNG)) {
        $transIdx = imagecolortransparent($image);
        if ($transIdx >= 0) {
            # Es gibt eine transparente Farbe (GIF oder PNG8)
            $transColor = imagecolorsforindex($image, $transIdx);
            $transIdx = imagecolorallocate($nImage, $transColor['red'], $transColor['green'], $transColor['blue']);
            imagefilledRectangle($nImage, 0, 0, $iWidth, $iHeight, $transIdx);
            imagecolortransparent($nImage, $transIdx);
        } elseif (($oType == IMAGETYPE_PNG) || (!$keeptype)) {
            # Ein PNG24 kriegt ein transparenter Hintergrund per Alpha-Kanal,
            # GIF-Bilder ohne Transparenz werden in PNG24 umgewandelt
            $oType = IMAGETYPE_PNG;
            imagealphablending($nImage, false);
            $oColor = imagecolorallocatealpha($nImage, 0, 0, 0, 127);
            imagefilledRectangle($nImage, 0, 0, $iWidth, $iHeight, $oColor);
            imagesavealpha($nImage, true);
        } else {
            # Andere GIF-Bilder kriegen eine Hintergrundfarbe
            $oColor = imagecolorallocate($nImage, hexdec(substr($fixedBG, 0, 2)), hexdec(substr($fixedBG, 2, 2)), hexdec(substr($fixedBG, 4, 2)));
            imagefilledrectangle($nImage, 0, 0, $iWidth, $iHeight, $oColor);
        }
    } else {
        if ($keepType) {
            # Andere Bilder (JPG) kriegen eine Hintergrundfarbe
            $oColor = imagecolorallocate($nImage, hexdec(substr($fixedBG, 0, 2)), hexdec(substr($fixedBG, 2, 2)), hexdec(substr($fixedBG, 4, 2)));
            imagefilledrectangle($nImage, 0, 0, $iWidth, $iHeight, $oColor);
        } else {
            # JPG-Bilder werden in PNG24 umgewandelt
            $oType = IMAGETYPE_PNG;
            imagealphablending($nImage, false);
            $oColor = imagecolorallocatealpha($nImage, 0, 0, 0, 127);
            imagefilledRectangle($nImage, 0, 0, $iWidth, $iHeight, $oColor);
            imagesavealpha($nImage, true);
        }
    }
    # Das Originalbild skaliert hinein kopieren
    imagecopyresampled($nImage, $image, $nLeft, $nTop, floor($cropLeft / $faktor), floor($cropTop / $faktor), ($nWidth + 2), ($nHeight + 2), $oWidth, $oHeight);
    # Das neue Bild speichern
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
    # Aufräumen
    imagedestroy($image);
    imagedestroy($nImage);
    # Pfad zurück liefern
    return $webFile;
}

/**
 * sitMakeCmsType()
 * 
 * Erzeugt ein CMS-Feld
 * 
 * Parameter:
 *   $type - Typ des zu generierenden Feldes
 *   $id - Nummer des zu generierenden Feldes
 * 
 * Generiert ein Feld eines CMS-Typs
 */
function sitMakeCmsType($type, $id) {
    global $db, $client, $lang, $cfg, $cfgClient, $area_tree, $sess, $perm, $area_rights, $item_rights, $_SESSION, $remakeCatTable, $remakeStrTable, $auth, $tpl, $edit, $a_content, $idartlang, $idcat, $idart;
    
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
 * sitMoveAllUploadFiles()
 *
 * Verschiebt alle Dateien eines Verzeichnisses
 *
 * Parameter:
 *   $source - Quellverzeichnis
 *   $dest - Zielverzeichnis
 *
 * Verschiebt alle Dateien eines Verzeichnisses im Upload-Bereich (unter /upload/)
 * und passt die Einträge in der Datenbank entsprechend an.
 */
function sitMoveAllUploadFiles($source, $dest) {
    global $cfgClient, $client, $db, $cfg;
    
    $source .= ((substr($source, -1) == '/') ? '' : '/');
    $dest .= ((substr($dest, -1) == '/') ? '' : '/');
    
    $a = array();
    $p = opendir($cfgClient[$client]['upl']['path'] . $source);
    while (($s = readdir($p)) !== false) {
        if (is_dir($cfgClient[$client]['upl']['path'] . $source . $s)) {
            continue;
        } elseif (strlen($s) > 2) {
            $a[] = $s;
        }
    }
    for ($i = 0, $n = count($a); $i < $n; $i ++) {
        rename($cfgClient[$client]['upl']['path'] . $source . $a[$i], $cfgClient[$client]['upl']['path'] . $dest . $a[$i]);
        $sql = 'UPDATE ' . $cfg['tab']['upl'] . '
                SET dirname = "' . $dest . '"
                WHERE ((dirname="' . $source . '")
                   AND (filename="' . $a[$i] . '"))';
        $db->query($sql);
    }
}

/**
 * sitSafeStringEscape()
 *
 * Escaped eine Zeichenfolge für SQL-Anweisungen
 *
 * Parameter:
 *   $string - Zu escapenden Zeichenfolge
 *
 * Escaped eine Zeichenfolge so, dass diese sicher in die Datenbank eingetragen
 * werden kann.
 */
function sitSafeStringEscape($string) { 
    $escapeCount = 0;
    $targetString = '';
    for($offset = 0; $offset < strlen($string); $offset ++) {
        switch ($c = $string{$offset}) {
            case "'":
                if ($escapeCount % 2 == 0) {
                    $targetString .= "\\";
                }
                $escapeCount = 0;
                $targetString .= $c;
                break;
            case '"':
                if ($escapeCount % 2 == 0) {
                    $targetString .= "\\";
                }
                $escapeCount = 0;
                $targetString .= $c;
                break;
            case '\\':
                $escapeCount ++ ;
                $targetString .= $c;
                break;
            default:
                $escapeCount = 0;
                $targetString .= $c;
        }
    }
    return $targetString;
}

/**
 * sitSendHtmlMail()
 *
 * Sendet eine HTML-Mail mit HTML- und Textteil
 *
 * Parameter:
 *   $html - HTML-Teil der Mail
 *   $subject - Betreffzeile der Mail
 *   $recipients - Array von Empfänger ('name' und 'email', mehrere möglich)
 *   $attachments - Dateipfad oder Array von Dateipfade für Anhänge (optional)
 *   $sname - Absendername (optional)
 *   $smail - Absenderadresse (optional)
 *   $mailer - Versandmethode ('mail' / 'qmail' / 'sendmail' / 'smtp') (optional)
 *   $sserver - SMTP-Server Adresse (optional)
 *   $slogin - SMTP Login (optional)
 *   $spass - SMTP Passwort (optional)
 *   $sport - SMTP Port (optional)
 *   $cc_recipients - Array von CC-Empfänger ('name' und 'email', mehrere möglich)
 *   $bcc_recipients - Array von BCC-Empfänger ('name' und 'email', mehrere möglich)
 *
 * Sendet eine HTML-Mail mit HTML- und Textteil an einen oder mehrere Empfänger
 * mit keinen oder mehrere Anhänge und liefert den Erfolgsstatus zurück.
 * Die Angaben zum Absender und den Mailer werden, sofern sie nicht mit angegeben
 * sind, aus den Mandanten- bzw. Systemeinstellungen ausgelesen.
 *   - email - sender-name
 *   - email - sender-email
 *   - email - mailer
 *   - email - smtp-server
 *   - email - smtp-login
 *   - email - smtp-password
 *   - email - smtp-port - 25
 * Die Empfänger werden als Array aus Name(n) und Email-Adresse(n) übergeben.
 * Beispiel 1: array('name' => 'xyz', 'email' => 'xyz@abc.de');
 * Beispiel 2: array(array('name' => 'xyz', 'email' => 'xyz@abc.de'), array('name'...
 */
function sitSendHtmlMail($html, $subject, $recipients, $attachments = '', $sname = '', $smail = '', $mailer = '', $sserver = '', $slogin = '', $spass = '', $sport = '', $cc_recipients = '', $bcc_recipients = '', $reply_to = '') {
    global $encoding, $lang, $cfg;
    
    # Eingaben ergänzen
    if (!is_array($attachments)) {
        $attachments = array($attachments);
    }
    $sname = html_entity_decode(((strlen($sname)) ? $sname : getEffectiveSetting('email', 'sender-name')), ENT_QUOTES, $encoding[$lang]);
    $smail = html_entity_decode(((strlen($smail)) ? $smail : getEffectiveSetting('email', 'sender-email')), ENT_QUOTES, $encoding[$lang]);
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
            $spass = html_entity_decode($spass, ENT_QUOTES, $encoding[$lang]);
        }
        $sport = intval(((strlen($sport)) ? $sport : getEffectiveSetting('email', 'smtp-port', 25)));
        if ($sport == 0) {
            $sport = 25;
        }
    }
    
    # Prüfen, ob genügend Angaben vorliegen
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
    $oLang->loadByPrimaryKey($lang);
    $oMail->setLanguage($oLang->getProperty('language', 'code'), $cfg['path']['contenido'] . $cfg['path']['external'] . 'PHPMailer/language/');
    $oMail->CharSet = $encoding[$lang];
    $oMail->IsHTML(true);
    $oMail->Mailer = $mailer;
    if ($mailer == 'smtp') {
        $oMail->SMTPAuth = true;
        $oMail->Host     = $sserver;
        $oMail->Port     = $sport;
        $oMail->Username = $slogin;
        $oMail->Password = $spass;
    }
    $oMail->Subject = html_entity_decode($subject, ENT_QUOTES, $encoding[$lang]);
    $oMail->From = $smail;
    $oMail->FromName = $sname;
    if (is_array($recipients[0])) {
        for ($i = 0, $n = count($recipients); $i < $n; $i ++) {
            if (strlen($recipients[$i]['email'])) {
                $oMail->AddAddress($recipients[$i]['email'], ((strlen($recipients[$i]['name'])) ? html_entity_decode($recipients[$i]['name'], ENT_QUOTES, $encoding[$lang]) : $recipients[$i]['email']));
            }
        }
    } else {
        $oMail->AddAddress($recipients['email'], ((strlen($recipients['name'])) ? html_entity_decode($recipients['name'], ENT_QUOTES, $encoding[$lang]) : $recipients['email']));
    }
    if (is_array($cc_recipients[0])) {
        for ($i = 0, $n = count($cc_recipients); $i < $n; $i ++) {
            if (strlen($cc_recipients[$i]['email'])) {
                $oMail->AddCC($cc_recipients[$i]['email'], ((strlen($cc_recipients[$i]['name'])) ? html_entity_decode($cc_recipients[$i]['name'], ENT_QUOTES, $encoding[$lang]) : $cc_recipients[$i]['email']));
            }
        }
    } elseif (is_array($cc_recipients)) {
        $oMail->AddCC($cc_recipients['email'], ((strlen($cc_recipients['name'])) ? html_entity_decode($cc_recipients['name'], ENT_QUOTES, $encoding[$lang]) : $cc_recipients['email']));
    }
    if (is_array($bcc_recipients[0])) {
        for ($i = 0, $n = count($bcc_recipients); $i < $n; $i ++) {
            if (strlen($bcc_recipients[$i]['email'])) {
                $oMail->AddBCC($bcc_recipients[$i]['email'], ((strlen($bcc_recipients[$i]['name'])) ? html_entity_decode($bcc_recipients[$i]['name'], ENT_QUOTES, $encoding[$lang]) : $bcc_recipients[$i]['email']));
            }
        }
    } elseif (is_array($bcc_recipients)) {
        $oMail->AddBCC($bcc_recipients['email'], ((strlen($bcc_recipients['name'])) ? html_entity_decode($bcc_recipients['name'], ENT_QUOTES, $encoding[$lang]) : $bcc_recipients['email']));
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
    $sMsg = html_entity_decode($sMsg, ENT_QUOTES, $encoding[$lang]);
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
 * sitSetClientProperty()
 *
 * Speichert eine Mandanteneinstellung
 *
 * Parameter:
 *   $type - Typ des Entrags (Text)
 *   $name - Name des Eintrags
 *   $value - Wert des Eintrags (Text)
 *
 * Speichert ein Eintrag in den Mandanteneinstellungen, überschreibt dabei
 * eine gleichnamige vorhandene Einstellung.
 */
function sitSetClientProperty($type, $name, $value) {	
	global $client, $cfg;
    
	if ((strlen($type)) && (strlen($name))) {
        $type = sitSafeStringEscape($type);
        $name = sitSafeStringEscape($name);
        $value = sitSafeStringEscape($value);
        $db = new DB_Contenido();
        $sql = 'SELECT value
                FROM ' . $cfg['tab']['properties'] . '
                WHERE ((idclient=' . $client . ')
                   AND (itemtype="clientsetting")
                   AND (type="' . $type . '")
                   AND (name="' . $name . '"))';
        $db->query($sql);
        if ($db->next_record()) {
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
        $db->query($sql);
	}
}

/**
 * sitTeaserText()
 *
 * Teasert einen Text an
 *
 * Parameter:
 *   $text - Zu teasernden Text
 *   $maxlength - Maximale Länge des Textes
 *
 * Der Text wird auf die maximale Anzahl Zeichen gekürzt, wobei der Schnitt nicht
 * mitten im Wort erfolgt, sondern davor.
 * Zuvor werden aus dem Text noch alle HTML-Tags entfernt.
 * Wenn der Text gekürzt wird (nur wenn der Text länger als der maximalen Anzahl
 * Zeichen ist), wird ein HTML-Zeichen &hellip; (...) angehängt.
 */
function sitTeaserText($text, $maxlength) {
    $sText1 = strip_tags(str_replace(array("\r\n", "\n"), ' ', $text));
    $sText2 = capiStrTrimAfterWord($sText1, intval($maxlength));
    if (strlen($sText2) < strlen($sText1)) {
        $sText2 .= '&hellip;';
    }
    return $sText2;
}

/**
 * quoteTypeWrap()
 *
 * Hilfsfunktion für sitArrayToString()
 */
function quoteTypeWrap($var) {
    switch (gettype($var)) {
        case 'string':
            return '"' . $var . '"';
            break;
        case 'NULL':
            return "null";
            break;
        //TODO: handle other variable types.. ( objects? )
        default :
            return $var;
            break;
    }
}
?>