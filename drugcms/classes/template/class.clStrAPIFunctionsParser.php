<?php
/**
 * class.clStrAPIFunctionsParser.php
 * 
 * clStrAPIFunctionsParser class
 * Template parser class for drugCMS string shorten functions
 * 
 * @package drugCMS
 * @subpackage CoreClasses
 * @version $Rev$
 * 
 * $Id$
 */
/**
 * @package     drugCMS Backend classes
 * @version     1.1
 * @author      Stefan Welpot
 * @modified    René Mansveld
 * @copyright   drugCMS <www.drugcms.org>
 * @license     http://www.drugcms.org/license/LIZENZ.txt
 * @link        http://www.drugcms.org
 * @since       file available since drugCMS release 2.0.0
 */

/**
* class clStrAPIFunctionsParser
*
* Implemenation des AbstractTemplateParser zum Auswerten von
* ContenidoStrAPIFunktionen.
* Als erlaubte Funktionen sind vorgesehen:
*    - {capiStrTrimHard(STRING, LÄNGE)}
*    - {capiStrTrimAfterWord(STRING, LÄNGE)}
*    - {capiStrTrimSentence(STRING, LÄNGE)}
*
* Die die Funktionen werden ertrahiert, ausgewertet und anschließend durch das Ergebnis ersetzt.
*
*
* @author Stefan Welpot
* @version 1.0
*/
include_once('class.clAbstractTemplateParser.php');
class clStrAPIFunctionsParser extends clAbstractTemplateParser {

     /**
      * zu ersetzende Funktionen
      * @var array_strAPIFunctions
      */
    var $array_strAPIFunctions = array(
                                    "capiStrTrimHard",
                                    "capiStrTrimAfterWord",
                                    "capiStrTrimSentence"
                                );
                                

     /**
      * Konstruktor
      */
    public function __construct() {}
    
     /**
      * @see AbstractTemplateParser#parse(string)
      */
    public function parse($template) {
        $anzahlMatches = 0;
        $array2_matches = array();
        $evaledCode = "";
        
        foreach($this->array_strAPIFunctions as $functionname) {
            $anzahlMatches = preg_match_all("/(?si)\{" . preg_quote($functionname, "/") . "\(\"?(.*?)\"?,\040?(\d+?)\)\}/", $template, $array2_matches);

            for($i = ($anzahlMatches - 1); $i >= 0; $i--) {
                $evaledCode = eval("return " . $functionname . "(\"" . $array2_matches[1][$i] . "\", " . $array2_matches[2][$i] . ");");
                
                $template = preg_replace(
                                "/(?si)\{" . preg_quote($functionname, "/") . "\(\"?" . preg_quote($array2_matches[1][$i], "/") . "\"?,\040?" . preg_quote($array2_matches[2][$i], "/") . "\)\}/",
                                $evaledCode,
                                $template
                );
                
            } //end for(i)
        } // end foreach

        return $template;
    }
}
?>