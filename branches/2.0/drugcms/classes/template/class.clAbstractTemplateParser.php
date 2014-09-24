<?php
/**
 * class.clAbstractTemplateParser.php
 * 
 * clAbstractTemplateParser class
 * Abstract Superclass for Template parser classes
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

abstract class clAbstractTemplateParser {
    /**
     * Parst das übergeben Template
     *
     * @param $template string das zu parsende Template
     *
     * @return string das geparste Template
     */
    abstract public function parse($template);
}
?>