<?php
/**
 * class.clCounterFunctionParser.php
 * 
 * clCounterFunctionParser class
 * Template parser class for counters
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
* class clCounterFunctionParser
*
* Implemenation des AbstractTemplateParser zur Verwendung von
* Zählern im Template
* Als Tag im Template ist vorgesehen:
*    - {counter PARAMETER}
* Die PARAMETER sind:
*    - name=ZÄHLERNAME
*    - start=STARTWERT (default: 0)
*    - step=SCHRITTWEITE (default: 1)
*    - print=(true|false) (default: true)
* Alle Parameter sind optional.
*
* Über die Name-Eigenschaft können mehrere unschiedliche Zähler verwendet werden.
* Bei jedem Vorkommen des {counter}-Tags wird der gleichnamige Zähler um STEP erhöht.
* Wird die Eigenschaft print auf true oder gar nicht gesetzt erfolgt eine Ausgabe des
* Zählerwerts an der Stelle des {counter}-Tags
*/
include_once('class.clAbstractTemplateParser.php');
class clCounterFunctionParser extends clAbstractTemplateParser {

     /**
      * Regexp-Pattern für {counter PARAMETER}
      * @var pattern_countertag
      */
    var $pattern_countertag =    "/(?si)\{counter(.*?\})/";

     /**
      * Regexp-Pattern für Parameter name=NAME
      * @var pattern_countername
      */
    var $pattern_countername =    "/(?i)name\040?\=(.+?)(\040|\})/";

     /**
      * Regexp-Pattern für Parameter start=STARTWERT
      * @var pattern_counterstart
      */
    var $pattern_counterstart =    "/(?i)start\040?\=(\d+?)(\040|\})/";

     /**
      * Regexp-Pattern für Parameter step=SCHRITTWEITE
      * @var pattern_counterstep
      */
    var $pattern_counterstep =    "/(?i)step\040?\=(\d+?)(\040|\})/";

     /**
      * Regexp-Pattern für Parameter print=(true|false)
      * @var pattern_counterprint
      */
    var $pattern_counterprint =    "/(?i)print\040?\=(true|false)(\040|\})/";
    
     /**
      * Defaultwert für Parameter name
      * @var default_countername
      */
    var $default_countername = "unnamed";

     /**
      * Defaultwert für Parameter start
      * @var default_counterstart
      */
    var $default_counterstart = 0;

     /**
      * Defaultwert für Parameter step
      * @var default_counterstep
      */
    var $default_counterstep = 1;

     /**
      * Defaultwert für Parameter print
      * @var default_counterprint
      */
    var $default_counterprint = true;
    
    
    /**
     * Konstruktor
     */
    public function __construct() {}

    /**
     * @see AbstractTemplateParser#parse(string)
     */
    public function parse($template) {
        $counterParameters = "";
        $counterKey = "";
        $array_matches = array();
        $array_countermatches = array();
        $array_initCounters = array();
        $boolPrint = $this->default_counterprint;
        $boolDoNotInkr = false;
        
        preg_match_all($this->pattern_countertag, $template, $array_countermatches, PREG_SET_ORDER);

        for($i = 0; $i < count($array_countermatches); $i++) {
            $counterKey = "";
            
            $counterParameters = $array_countermatches[$i][1];

            //Attribut Countername auslesen, falls vorhanden
            preg_match($this->pattern_countername, $counterParameters, $array_matches);
            if(count($array_matches) > 0) {
                $counterKey = $array_matches[1];
            }
            else { // sonst defaultname
                $counterKey = $this->default_countername;
            }

                            
            if(!array_key_exists($counterKey, $array_initCounters)) { // neuen Counter initialisieren
                $array_initCounters[$counterKey]['value'] = $this->default_counterstart;
                $array_initCounters[$counterKey]['step'] = $this->default_counterstep;
                $boolDoNotInkr = true;
            }
            

            //Attribut step
            preg_match($this->pattern_counterstep, $counterParameters, $array_matches);
            if(count($array_matches) > 0) {
                $array_initCounters[$counterKey]['step'] = $array_matches[1];
            }
    

            //Attribut start
            preg_match($this->pattern_counterstart, $counterParameters, $array_matches);
            if(count($array_matches) > 0) {
                $array_initCounters[$counterKey]['value'] = $array_matches[1];
                $boolDoNotInkr = true;
            }
            
            
            // value regulär erhöhen
            if(!$boolDoNotInkr) {
                $array_initCounters[$counterKey]['value'] = $array_initCounters[$counterKey]['value'] + $array_initCounters[$counterKey]['step'];
            }
            else {
                $boolDoNotInkr = false;
            }
            
            
            //Attribut print
            preg_match($this->pattern_counterprint, $counterParameters, $array_matches);
            if(count($array_matches) > 0) {
                $boolPrint = (strcasecmp("true", $array_matches[1]) == 0) ? true : false;
            }
            else {
                $boolPrint = $this->default_counterprint;
            }


            if($boolPrint) {
                $template = preg_replace($this->pattern_countertag, $array_initCounters[$counterKey]['value'], $template, 1);
            }
            else {
                $template = preg_replace($this->pattern_countertag, '', $template, 1);
            }
        }
        
        return $template;
    }
}
?>