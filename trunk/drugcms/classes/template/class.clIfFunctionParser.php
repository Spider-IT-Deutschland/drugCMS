<?php
/**
 * class.clIfFunctionParser.php
 * 
 * clIfFunctionParser class
 * Template parser class for IF constructs
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
* class clIfFunctionParser
*
* Implemenation des AbstractTemplateParser zum Auswerten von
* If-Konstrukten im Template
* Als Tags im Template sind vorgesehen:
*    - {if(Bedigung)}
*    - {else if(Bedingung)}
*    - {else}
*    - {/if}
*
* Ein If-Konstrukt besteht dabei mindestens aus {if(Bedingung)} und einem abschließenden {/if}.
* Das {else if(Bedingung)} kann beliebig oft hintereinander verwendet werden.
* Die Konstrukte an sich können beliebig tief geschachtelt werden.
* Die Bedinungen der {if()}- und {else if()}-Teile werden durch eval ausgewertet
* und können somit auch PHP-Funktionen interpretieren.
*/
include_once('class.clAbstractTemplateParser.php');
class clIfFunctionParser extends clAbstractTemplateParser {

     /**
      * Regexp-Pattern für {if(Bedingung)}
      * @var pattern_if
      */
    var $pattern_if =         "\{if\040*\((.*?)\)\}";

     /**
      * Regexp-Pattern für {else if(Bedingung)}
      * @var pattern_elseif
      */
    var $pattern_elseif =     "\{else if\040*\((.*?)\)\}";

     /**
      * Regexp-Pattern für {else}
      * @var pattern_else
      */
    var $pattern_else =     "\{else\}";

     /**
      * Regexp-Pattern für {/if}
      * @var pattern_endif
      */
    var $pattern_endif =     "\{\/if\}";
    
     /**
      * Länge des {if(Bedinung)}-Tags ohne Bedingung
      * @var patternlength_if
      */
    var $patternlength_if =            6;

     /**
      * Länge des {else if(Bedinung)}-Tags ohne Bedingung
      * @var patternlength_elseif
      */
    var $patternlength_elseif =     11;

     /**
      * Länge des {else}-Tags
      * @var patternlength_else
      */
    var $patternlength_else =         6;

     /**
      * Länge des {/if}-Tags
      * @var patternlength_endif
      */
    var $patternlength_endif =         5;



    /**
     * Konstruktor
     */
    public function __construct() {}

    /**
     * @see AbstractTemplateParser#parse(string)
     */
    public function parse($template) {
        $array2_uncompletedConstructs = array(); //geöffnete, aber noch nicht geschlossene funktionen

        //hilfsvariablen
        $array_match_all = array();
        $array_match_part = array();
        $uncompletedConstructsIndex = 0;
        $elseifIndex = 0;
        $currentOffset = 0;
        $oldOffset = -1;
        
        $array_construct = array();
        
        $pattern_all = "/(?is)(" . $this->pattern_if . "|" . $this->pattern_elseif . "|" . $this->pattern_else . "|" . $this->pattern_endif . ")/";

          //das template solange nach konstruktteilen durchsuchen bis keine mehr gefunden werden
        while($currentOffset != $oldOffset) {
            $oldOffset = $currentOffset;
            
            //wenn irgendein teil einer if-konstruktion gefunden wird
            if(preg_match($pattern_all, $template, $array_match_all, PREG_OFFSET_CAPTURE, $currentOffset) > 0) {
                //herausfinden, welcher teil gefunden wurde
                //if-teil
                if(preg_match("/(?is)" . $this->pattern_if . "/", $array_match_all[0][0], $array_match_part) > 0) {
                    $uncompletedConstructsIndex++; //inkrement openFunctionIndex

                    $array2_uncompletedConstructs[$uncompletedConstructsIndex]['if']['condition'] = $array_match_part[1];
                    $array2_uncompletedConstructs[$uncompletedConstructsIndex]['if']['pos_start'] = $array_match_all[0][1];
                    # 2013-01-15 Mansveld: Längenberechnung berücksichtigte keine Leerstellen
                    #$array2_uncompletedConstructs[$uncompletedConstructsIndex]['if']['pos_end'] = $array2_uncompletedConstructs[$uncompletedConstructsIndex]['if']['pos_start'] + $this->patternlength_if + strlen($array_match_part[1]);
                    $array2_uncompletedConstructs[$uncompletedConstructsIndex]['if']['pos_end'] = $array_match_all[0][1] + strlen($array_match_part[0]);
                    $currentOffset = $array2_uncompletedConstructs[$uncompletedConstructsIndex]['if']['pos_end'];    
                }
                //elseif-teil
                else if(preg_match("/(?is)" . $this->pattern_elseif . "/", $array_match_all[0][0], $array_match_part) > 0) {
                    $elseifIndex = count($array2_uncompletedConstructs[$uncompletedConstructsIndex]['elseif']);

                    $array2_uncompletedConstructs[$uncompletedConstructsIndex]['elseif'][$elseifIndex]['condition'] = $array_match_part[1];
                    $array2_uncompletedConstructs[$uncompletedConstructsIndex]['elseif'][$elseifIndex]['pos_start'] = $array_match_all[0][1];
                    # 2013-01-15 Mansveld: Längenberechnung berücksichtigte keine Leerstellen
                    #$array2_uncompletedConstructs[$uncompletedConstructsIndex]['elseif'][$elseifIndex]['pos_end'] = $array2_uncompletedConstructs[$uncompletedConstructsIndex]['elseif'][$elseifIndex]['pos_start'] + $this->patternlength_elseif + strlen($array_match_part[1]);
                    $array2_uncompletedConstructs[$uncompletedConstructsIndex]['elseif'][$elseifIndex]['pos_end'] = $array_match_all[0][1] + strlen($array_match_part[0]);
                    $currentOffset = $array2_uncompletedConstructs[$uncompletedConstructsIndex]['elseif'][$elseifIndex]['pos_end'];    
                }
                //else-teil
                else if(preg_match("/(?is)" . $this->pattern_else . "/", $array_match_all[0][0], $array_match_part) > 0) {
                    $array2_uncompletedConstructs[$uncompletedConstructsIndex]['else']['pos_start'] = $array_match_all[0][1];
                    # 2013-01-15 Mansveld: Längenberechnung berücksichtigte keine Leerstellen
                    #$array2_uncompletedConstructs[$uncompletedConstructsIndex]['else']['pos_end'] = $array2_uncompletedConstructs[$uncompletedConstructsIndex]['else']['pos_start'] + $this->patternlength_else;
                    $array2_uncompletedConstructs[$uncompletedConstructsIndex]['else']['pos_end'] = $array_match_all[0][1] + strlen($array_match_part[0]);
                    $currentOffset = $array2_uncompletedConstructs[$uncompletedConstructsIndex]['else']['pos_end'];    
                }
                //endif
                else if(preg_match("/(?is)" . $this->pattern_endif . "/", $array_match_all[0][0], $array_match_part) > 0) {
                    $array2_uncompletedConstructs[$uncompletedConstructsIndex]['endif']['pos_start'] = $array_match_all[0][1];
                    # 2013-01-15 Mansveld: Längenberechnung berücksichtigte keine Leerstellen
                    #$array2_uncompletedConstructs[$uncompletedConstructsIndex]['endif']['pos_end'] = $array2_uncompletedConstructs[$uncompletedConstructsIndex]['endif']['pos_start'] + $this->patternlength_endif;
                    $array2_uncompletedConstructs[$uncompletedConstructsIndex]['endif']['pos_end'] = $array_match_all[0][1] + strlen($array_match_part[0]);
                    $currentOffset = $array2_uncompletedConstructs[$uncompletedConstructsIndex]['endif']['pos_end'];    
                    
                    
                    //gefundene komplette funktion sofort ersetzen
                    $array_construct = array_pop($array2_uncompletedConstructs);
                    $uncompletedConstructsIndex--;
                    $template = $this->replaceConstruct($array_construct, $template);
                    //offset korrigieren = anfang der ersetzten funktion
                    $currentOffset = $array_construct['if']['pos_start'];
                }                
            }
        } // end while
        
        if($uncompletedConstructsIndex > 0) { // wenn noch offene Funktionen vorhanden sind => Fehler im Template
            $template = "Fehler in IF-Konstruktionen. Folgende If-Statements sind nicht abgeschlossen:<br>\n";
            foreach($array2_uncompletedConstructs as $array_construct) {
                $template .= "- {if(" . $array_construct['if']['condition'] . ")}<br>\n";
            }
        }
        
        return $template;
    } // end function
    
    
    /**
     * Ersetzt die als Array übergebene Funktion durch
     * den ersten Teil mit einer wahren Bedingung
     *
     * @param $array_construct Array das komplette If-Konstrukt das ausgewertet werden soll
     * @param $template string das Template in dem das Konstrukt ersetzt werden soll
     *
     * @return string das Template mit dem ersetzten Konstrukt
     */
    private function replaceConstruct($array_construct, $template) {
        $array_elseif = array();        
        $key = 0;
        $replace_text = "";
        $boolConditionTrue = false;

        //prüfen ob der if-teil wahr ist und $replace_text finden
        if(eval("return " . $array_construct['if']['condition'] . ";")) {
            if(array_key_exists('elseif', $array_construct)) { //wenn ein elseif-teil existiert
                $replace_text = substr($template, $array_construct['if']['pos_end'], $array_construct['elseif'][0]['pos_start'] - $array_construct['if']['pos_end']);
            }
            else if(array_key_exists('else', $array_construct)) { //wenn nur ein else-teil existiert
                $replace_text = substr($template, $array_construct['if']['pos_end'], $array_construct['else']['pos_start'] - $array_construct['if']['pos_end']);
            }
            else {//wenn der nächste teil schon endif ist
                $replace_text = substr($template, $array_construct['if']['pos_end'], $array_construct['endif']['pos_start'] - $array_construct['if']['pos_end']);
            }
        }
        else {
            //alle elseif-teile prüfen
            if(is_array($array_construct['elseif'])) {
                foreach($array_construct['elseif'] as $key => $array_elseif) {
                    if(eval("return " . $array_elseif['condition'] . ";")) {
                        $boolConditionTrue = true;
                        
                        if(array_key_exists(($key + 1), $array_construct['elseif'])) { //wenn ein weiterer elseif-teil existiert
                            $replace_text = substr($template, $array_elseif['pos_end'], $array_construct['elseif'][$key+1]['pos_start'] - $array_elseif['pos_end']);
                        }
                        else if(array_key_exists('else', $array_construct)) { //wenn nächster teil nur ein else-teil ist
                            $replace_text = substr($template, $array_elseif['pos_end'], $array_construct['else']['pos_start'] - $array_elseif['pos_end']);
                        }
                        else {//wenn der nächste teil schon endif ist
                            $replace_text = substr($template, $array_elseif['pos_end'], $array_construct['endif']['pos_start'] - $array_elseif['pos_end']);
                        }
                        
                        break;
                    }
                }
            }
            
            //wenn bisher noch keine wahre Bedingung gefunden wurde
            if(!$boolConditionTrue) {
                if(array_key_exists('else', $array_construct)) { // wenn ein else-teil existiert
                    $replace_text = substr($template, $array_construct['else']['pos_end'], $array_construct['endif']['pos_start'] - $array_construct['else']['pos_end']);
                }
                else { // sonst wird das gesamte kontrukt durch einen leeren string ersetzt
                    $replace_text = "";
                }
            }
        } // end else
                    
        //if-konstruktion durch wahren teil ersetzen
        $template = substr_replace($template, $replace_text, $array_construct['if']['pos_start'], $array_construct['endif']['pos_end'] - $array_construct['if']['pos_start']);
        
        return $template;
    }
}
?>