<?php
/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * Contenido i18n Functions
 * 
 * @package     Contenido Backend includes
 * @version     $Rev$
 * @author      Timo A. Hummel
 * @author      Ortwin Pinke <o.pinke@php-backoffice.de>
 * @copyright   four for business AG <www.4fb.de>
 * @license     http://www.contenido.org/license/LIZENZ.txt
 * @link        http://www.4fb.de
 * @link        http://www.contenido.org
 * @since       file available since contenido release <= 4.6
 * 
 *   $Id$
 */

if(!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}

/**
 * trans($string)
 *
 * gettext wrapper (for future extensions). Usage:
 * trans("Your text which has to be translated");
 *
 * @param $string string The string to translate
 * @return string  Returns the translation
 * 
 * @deprecated since 4.8.16 CL, use i18n instead, function will be deleted in one of next versions
 */
function trans($string) {
    return i18n($string);
}
/**
 * i18n($string)
 *
 * gettext wrapper (for future extensions). Usage:
 * i18n("Your text which has to be translated");
 *
 * @param $string string The string to translate
 * @param $domain string The domain to look up
 * @return string  Returns the translation
 */
function i18n($string, $domain = "drugcms") {
    global $cfg, $i18nLanguage;
	
    // Auto initialization
    if (!isset($i18nLanguage))	{
        if (!isset($GLOBALS['belang'])) {
            if(isset($contenido) && $contenido) {
                // This is backend, we should trigger an error message here
                $stack = @debug_backtrace();
                $file = $stack[0]['file'];
                $line = $stack[0]['line'];
                cWarning ($file, $line, "i18nInit \$belang is not set");
            } // only send warning in backend	
            $GLOBALS['belang'] = false; // Needed - otherwise this won't work
        }
        i18nInit($cfg["path"]["contenido"].$cfg["path"]["locale"], $GLOBALS['belang']);
    }

    cInitializeArrayKey($cfg, "native_i18n", false);

    if (!$cfg["native_i18n"]) {
        return i18nEmulateGettext($string, $domain);
    }
    
    if (extension_loaded("gettext")) {
        if (function_exists("dgettext")) {
            if ($domain != "drugcms") {
                $translation = str_replace(array('|1', '|2'), array('<', '>'), htmlentities(str_replace(array('<', '>'), array('|1', '|2'), dgettext($domain, $string)), ENT_COMPAT, 'UTF-8', false));
                return $translation;
            } else {
                $translation = gettext($string);
                return $translation;
            }
        }
    }
    
    return i18nEmulateGettext($string, $domain);
}

/**
 * i18nEmulateGettext()
 *
 * Emulates GNU gettext
 *
 * @param $string string The string to translate
 * @param $domain string The domain to look up
 * @return string  Returns the translation
 */
function i18nEmulateGettext($string, $domain = "drugcms") {
    global $cfg, $i18nLanguage, $transFile, $i18nDomains, $_i18nTranslationCache, $encoding, $lang;
    
    if (!is_array($_i18nTranslationCache)) {
        $_i18nTranslationCache = array();
    }
    if (array_key_exists($string, $_i18nTranslationCache)) {
        return $_i18nTranslationCache[$string];
    }
    
    // Bad thing, gettext is not available. Let's emulate it
    if (!isset($i18nDomains[$domain]) || !file_exists($i18nDomains[$domain].$i18nLanguage."/LC_MESSAGES/".$domain.".po")) {
        return $string;
    }
    
    if (!isset($transFile[$domain])) {
        $transFile[$domain] = implode('',file($i18nDomains[$domain].$i18nLanguage."/LC_MESSAGES/".$domain.".po"));
    }
    if (isset($transFile[$domain])) {
        // Remove comments from file
        $transFile[$domain] = preg_replace('/^#.+/m', '', $transFile[$domain]);
        
        // Prepare for special po edit format 
        /* Something like: 
         #, php-format
         msgid ""
         "Hello %s,\n"
         "\n"
         "you've got a new reminder for the client '%s' at\n"
         "%s:\n"
         "\n"
         "%s"
         msgstr ""
         "Hallo %s,\n"
         "\n"
         "du hast eine Wiedervorlage erhalten für den Mandanten '%s' at\n"
         "%s:\n"
         "\n"
         "%s"
         has to be converted to:
         msgid "Hello %s,\n\nyou've got a new reminder for the client '%s' at\n%s:\n\n%s"
         msgstr "Hallo %s,\n\ndu hast eine Wiedervorlage erhalten f�r den Mandanten '%s' at\n%s:\n\n%s"
         */ 
        $transFile[$domain] = str_replace('"' . "\n" . '"', '', $transFile[$domain]);
    }
    
    $stringStart = strpos($transFile[$domain], '"'.str_replace(Array("\n", "\r", "\t"), Array('\n', '\r', '\t'), $string).'"');
    if ($stringStart === false) {
        return $string;
    }
    
    $results = array();
    preg_match("/msgid.*\"(".preg_quote(str_replace(Array("\n", "\r", "\t"), Array('\n', '\r', '\t'), $string),"/").")\"(?:\s*)?\nmsgstr(?:\s*)\"(.*)\"/", $transFile[$domain], $results);
    # Old: preg_match("/msgid.*\"".preg_quote($string,"/")."\".*\nmsgstr(\s*)\"(.*)\"/", $transFile[$domain], $results);
    if ((!isset($encoding)) || (!is_array($encoding)) || (count($encoding) == 0)) {
        // get encodings of all languages
        $encoding = array();
        $db = new DB_Contenido();
        $sql = "SELECT idlang, encoding FROM " . $cfg["tab"]["lang"];
        $db->query($sql);
        while ($db->next_record()) {
            $encoding[$db->f('idlang')] = $db->f('encoding');
        }
        $db->disconnect();
    }
    if (array_key_exists(1, $results)) {
        $_i18nTranslationCache[$string] = str_replace(array('|1', '|2'), array('<', '>'), html_entity_decode(htmlentities(str_replace(array('<', '>'), array('|1', '|2'), stripslashes(str_replace(Array('\n', '\r', '\t'), Array("\n", "\r", "\t"), $results[2]))), ENT_COMPAT, 'UTF-8', false), ENT_COMPAT, ((strlen($encoding[$lang])) ? strtoupper($encoding[$lang]) : 'UTF-8')));
        return $_i18nTranslationCache[$string];
    } else {
        return $string;
    }
}

/**
 * i18nInit()
 *
 * Initializes the i18n stuff.
 *
 * @global string $i18nLanguage
 * @global array $i18nDomains
 * @param string $localePath
 * @param string $langCode 
 */
function i18nInit ($localePath, $langCode) {
    global $i18nLanguage, $i18nDomains;
    
    if(function_exists("bindtextdomain")) {
        /* Bind the domain "drugcms" to our locale path */
        bindtextdomain("drugcms", $localePath);

        /* Set the default text domain to "drugcms" */
        textdomain("drugcms");

        /* Half brute-force to set the locale. */
        if(!ini_get("safe_mode")) {
            putenv("LANG=$langCode");
        }

        if(defined("LC_MESSAGES")) {
            setlocale(LC_MESSAGES, $langCode);
        }

        setlocale(LC_CTYPE, $langCode);
    }
    
    $i18nDomains["drugcms"] = $localePath;    
    $i18nLanguage = $langCode;
}

/**
 * i18nRegisterDomain()
 *
 * Registers a new i18n domain.
 *
 * @param $localePath string Path to the locales
 * @param $domain string Domain to bind to
 * @return string  Returns the translation
 */
function i18nRegisterDomain ($domain, $localePath) {
    global $i18nDomains;
    
    if(function_exists("bindtextdomain")) {
        /* Bind the domain "drugcms" to our locale path */
        bindtextdomain($domain, $localePath);
    }
    $i18nDomains[$domain] = $localePath;
}

/**
 * i18nStripAcceptLanguages($accept)
 *
 * Strips all unnecessary information from the $accept string.
 * Example: de,nl;q=0.7,en-us;q=0.3 would become an array with de,nl,en-us
 *
 * @return array Array with the short form of the accept languages  
 */
function i18nStripAcceptLanguages($accept) {
    $languages = explode(',', $accept);
    foreach($languages as $value)	{
        $components = explode(';', $value);
        $shortLanguages[] = $components[0];
    }	
    return ($shortLanguages);
}

/**
 * i18nMatchBrowserAccept($accept)
 *
 * Tries to match the language given by $accept to
 * one of the languages in the system.
 *
 * @return string The locale key for the given accept string 
 */
function i18nMatchBrowserAccept ($accept)
{
	$available_languages = i18nGetAvailableLanguages();
	
	/* Try to match the whole accept string */
	foreach ($available_languages as $key => $value)
	{
		list($country, $lang, $encoding, $shortaccept) = $value;
		
		if ($accept	== $shortaccept)
		{
			return $key;
		}
	}
	
	/* Whoops, we are still here. Let's match the stripped-down string.
       Example: de-ch isn't in the list. Cut it down after the "-" to "de"
       which should be in the list. */
       
    $accept = substr($accept,0,2);
	foreach ($available_languages as $key => $value)
	{
		list($country, $lang, $encoding, $shortaccept) = $value;
		
		if ($accept	== $shortaccept)
		{
			return $key;
		}
	}

	/* Whoops, still here? Seems that we didn't find any language. Return
       the default (german, yikes) */
    #return false;
    # 2014-05-24: Now we set "English (United States)" as the default
    return 'en_US';
}

/**
 * i18nGetAvailableLanguages()
 *
 * Returns the available_languages array to prevent globals.
 *
 * @return array All available languages
 */
function i18nGetAvailableLanguages ()
{
	/* Array notes: 
		First field: Language 
		Second field: Country 
		Third field: ISO-Encoding 
		Fourth field: Browser accept mapping 
		Fifth field: SPAW language 
	*/ 
	$aLanguages = array( 
		'ar_AA' => array('Arabic','Arabic Countries', 'ISO8859-6', 'ar','en'), 
		'be_BY' => array('Byelorussian', 'Belarus', 'ISO8859-5', 'be', 'en'), 
		'bg_BG' => array('Bulgarian','Bulgaria', 'ISO8859-5', 'bg', 'en'), 
		'cs_CZ' => array('Czech', 'Czech Republic', 'ISO8859-2', 'cs', 'cz'), 
		'da_DK' => array('Danish', 'Denmark', 'ISO8859-1', 'da', 'dk'), 
		'de_CH' => array('German', 'Switzerland', 'ISO8859-1', 'de-ch', 'de'), 
		'de_DE' => array('German', 'Germany', 'ISO8859-1', 'de', 'de'), 
		'el_GR' => array('Greek', 'Greece', 'ISO8859-7', 'el', 'en'), 
		'en_GB' => array('English', 'Great Britain', 'ISO8859-1', 'en-gb', 'en'), 
		'en_US' => array('English', 'United States', 'ISO8859-1', 'en', 'en'), 
		'es_ES' => array('Spanish', 'Spain', 'ISO8859-1', 'es', 'es'), 
		'fi_FI' => array('Finnish', 'Finland', 'ISO8859-1', 'fi', 'en'), 
		'fr_BE' => array('French', 'Belgium', 'ISO8859-1', 'fr-be', 'fr'), 
		'fr_CA' => array('French', 'Canada', 'ISO8859-1', 'fr-ca', 'fr'), 
		'fr_FR' => array('French', 'France', 'ISO8859-1', 'fr', 'fr'), 
		'fr_CH' => array('French', 'Switzerland', 'ISO8859-1', 'fr-ch', 'fr'), 
		'hr_HR' => array('Croatian', 'Croatia', 'ISO8859-2', 'hr', 'en'), 
		'hu_HU' => array('Hungarian', 'Hungary', 'ISO8859-2', 'hu', 'hu'), 
		'is_IS' => array('Icelandic', 'Iceland', 'ISO8859-1', 'is', 'en'), 
		'it_IT' => array('Italian', 'Italy', 'ISO8859-1', 'it', 'it'), 
		'iw_IL' => array('Hebrew', 'Israel', 'ISO8859-8', 'he', 'he'), 
		'nl_BE' => array('Dutch', 'Belgium', 'ISO8859-1', 'nl-be', 'nl'), 
		'nl_NL' => array('Dutch', 'Netherlands', 'ISO8859-1', 'nl', 'nl'), 
		'no_NO' => array('Norwegian', 'Norway', 'ISO8859-1', 'no', 'en'), 
		'pl_PL' => array('Polish', 'Poland', 'ISO8859-2', 'pl', 'en'), 
		'pt_BR' => array('Brazillian', 'Brazil', 'ISO8859-1', 'pt-br', 'br'), 
		'pt_PT' => array('Portuguese', 'Portugal', 'ISO8859-1', 'pt', 'en'), 
		'ro_RO' => array('Romanian', 'Romania', 'ISO8859-2', 'ro', 'en'), 
		'ru_RU' => array('Russian', 'Russia', 'ISO8859-5', 'ru', 'ru'), 
		'sh_SP' => array('Serbian Latin', 'Yugoslavia', 'ISO8859-2', 'sr', 'en'), 
		'sl_SI' => array('Slovene', 'Slovenia', 'ISO8859-2', 'sl', 'en'), 
		'sk_SK' => array('Slovak', 'Slovakia', 'ISO8859-2', 'sk', 'en'), 
		'sq_AL' => array('Albanian', 'Albania', 'ISO8859-1', 'sq', 'en'), 
		'sr_SP' => array('Serbian Cyrillic', 'Yugoslavia', 'ISO8859-5', 'sr-cy', 'en'), 
		'sv_SE' => array('Swedish', 'Sweden', 'ISO8859-1', 'sv', 'se'),
		'tr_TR' => array('Turkish', 'Turkey', 'ISO8859-9', 'tr', 'tr') 
	);

	return ($aLanguages); 
}

/**
 * translate strings in modules
 * 
 * @global int $cCurrentModule
 * @global string $lang
 * @global cApiModuleTranslationCollection $mi18nTranslator
 * @param string $string string to translate
 * @return string translated string 
 */
function mi18n($string) {
    global $cCurrentModule, $lang, $mi18nTranslator;
    
    if(!is_object($mi18nTranslator)) {
        $mi18nTranslator = new cApiModuleTranslationCollection;
    }
    return $mi18nTranslator->fetchTranslation($cCurrentModule, $lang, $string);
}	
?>