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
    require(dirname(__FILE__) . '/include.cultures.php');
	
    foreach ($aCultureCodes as $key => $value)
	{
        if ((strpos($accept, '-') !== false) && ($accept == str_replace('_', '-', $key)))
		{
			return $key;
		}
        elseif ($accept == $value['short'])
        {
            return $key;
        }
	}
	
	# Whoops, still here? Set "English (United States)" as the default
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
		'ar_AA' => array('العربية','البلدان العربية', 'ISO8859-6', 'ar','en', 'Arabic', 'Arabic Countries'),
		'be_BY' => array('беларускі', 'Беларусь', 'ISO8859-5', 'be', 'en', 'Byelorussian', 'Belarus'),
		'bg_BG' => array('български','България', 'ISO8859-5', 'bg', 'en', 'Bulgarian', 'Bulgaria'),
		'cs_CZ' => array('čeština', 'česká republika', 'ISO8859-2', 'cs', 'cz', 'Czech', 'Czech Republic'),
		'da_DK' => array('dansk', 'Danmark', 'ISO8859-1', 'da', 'dk', 'Danish', 'Denmark'),
		'de_CH' => array('Deutsch', 'Schweiz', 'ISO8859-1', 'de-ch', 'de', 'German', 'Switzerland'),
		'de_DE' => array('Deutsch', 'Deutschland', 'ISO8859-1', 'de', 'de', 'German', 'Germany'),
		'el_GR' => array('ελληνικά', 'Ελλάδα', 'ISO8859-7', 'el', 'en', 'Greek', 'Greece'),
		'en_GB' => array('English', 'Great Britain', 'ISO8859-1', 'en-gb', 'en', 'English', 'Great Britain'),
		'en_US' => array('English', 'United States', 'ISO8859-1', 'en', 'en', 'English', 'United States'),
		'es_ES' => array('español', 'España', 'ISO8859-1', 'es', 'es', 'Spanish', 'Spain'),
		'fi_FI' => array('suomi', 'Suomi', 'ISO8859-1', 'fi', 'en', 'Finnish', 'Finland'),
		'fr_BE' => array('français', 'Belgique', 'ISO8859-1', 'fr-be', 'fr', 'French', 'Belgium'),
		'fr_CA' => array('français', 'Canada', 'ISO8859-1', 'fr-ca', 'fr', 'French', 'Canada'),
		'fr_FR' => array('français', 'France', 'ISO8859-1', 'fr', 'fr', 'French', 'France'),
		'fr_CH' => array('français', 'Suisse', 'ISO8859-1', 'fr-ch', 'fr', 'French', 'Switzerland'),
		'hr_HR' => array('hrvatski', 'Hrvatska', 'ISO8859-2', 'hr', 'en', 'Croatian', 'Croatia'),
		'hu_HU' => array('magyar', 'Magyarország', 'ISO8859-2', 'hu', 'hu', 'Hungarian', 'Hungary'),
		'is_IS' => array('icelandic', 'Iceland', 'ISO8859-1', 'is', 'en', 'Icelandic', 'Iceland'),
		'it_IT' => array('italiano', 'Italia', 'ISO8859-1', 'it', 'it', 'Italian', 'Italy'),
		'iw_IL' => array('עברית', 'ישראל', 'ISO8859-8', 'he', 'he', 'Hebrew', 'Israel'),
		'nl_BE' => array('Vlaams', 'België', 'ISO8859-1', 'nl-be', 'nl', 'Dutch', 'Belgium'),
		'nl_NL' => array('Nederlands', 'Nederland', 'ISO8859-1', 'nl', 'nl', 'Dutch', 'Netherlands'),
		'no_NO' => array('norsk', 'Norge', 'ISO8859-1', 'no', 'en', 'Norwegian', 'Norway'),
		'pl_PL' => array('polski', 'Polska', 'ISO8859-2', 'pl', 'en', 'Polish', 'Poland'),
		'pt_BR' => array('Brazillian', 'Brasil', 'ISO8859-1', 'pt-br', 'br', 'Brazillian', 'Brazil'),
		'pt_PT' => array('português', 'Portugal', 'ISO8859-1', 'pt', 'en', 'Portuguese', 'Portugal'),
		'ro_RO' => array('român', 'România', 'ISO8859-2', 'ro', 'en', 'Romanian', 'Romania'),
		'ru_RU' => array('русский', 'Россия', 'ISO8859-5', 'ru', 'ru', 'Russian', 'Russia'),
		'sh_SP' => array('Srpski latinica', 'Jugoslavija', 'ISO8859-2', 'sr', 'en', 'Serbian Latin', 'Yugoslavia'),
		'sl_SI' => array('slovenski', 'Slovenija', 'ISO8859-2', 'sl', 'en', 'Slovene', 'Slovenia'),
		'sk_SK' => array('slovenčina', 'Slovensko', 'ISO8859-2', 'sk', 'en', 'Slovak', 'Slovakia'),
		'sq_AL' => array('shqiptar', 'Shqipëri', 'ISO8859-1', 'sq', 'en', 'Albanian', 'Albania'),
		'sr_SP' => array('Српски латиница', 'Југославија', 'ISO8859-5', 'sr-cy', 'en', 'Serbian Cyrillic', 'Yugoslavia'),
		'sv_SE' => array('svenska', 'Sverige', 'ISO8859-1', 'sv', 'se', 'Swedish', 'Sweden'),
		'tr_TR' => array('Türk', 'türkiye', 'ISO8859-9', 'tr', 'tr', 'Turkish', 'Turkey')
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