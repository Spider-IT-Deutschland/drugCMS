<?php
/**
 * Project: 
 * drugCMS Content Management System
 * 
 * Description: 
 * Culture codes (language and speach code combinations)
 * 
 * Requirements: 
 * @con_php_req 5.0
 * 
 *
 * @package    drugCMS Backend includes
 * @version    1.0.0
 * @author     René Mansveld
 * @copyright  Spider IT Deutschland <www.Spider-IT.de>
 * @license    http://www.drugCMS.org/license/LICENSE.txt
 * @link       http://www.Spider-IT.de
 * @link       http://www.drugCMS.org
 * @since      file available since drugCMS release 2.0.7
 *
 * IMPORTANT NOTICE
 * All short codes (those without country code, like 'en' or 'de') MUST be 
 * added as 'short' to the default culture ('en_US' gets 'en', 'de_DE' gets 
 * 'de'), even if this is the complete culture (like 'eo' for 'Esperanto').
 * 
 * {@internal 
 *   created 2015-09-01
 * }}
 */

if (!defined("CON_FRAMEWORK")) {
	die("Illegal call");
}

$aCultureCodes = array();

$aCultureCodes["eo"]            = array('native' => "Esperanto", 'short' => "eo", 'english' => "Esperanto");
$aCultureCodes["ia"]            = array('native' => "Interlingua", 'short' => "ia", 'english' => "Interlingua");
$aCultureCodes["ie"]            = array('native' => "Interlingue", 'short' => "ie", 'english' => "Interlingue");
$aCultureCodes["io"]            = array('native' => "Ido", 'short' => "io", 'english' => "Ido");
$aCultureCodes["vo"]            = array('native' => "Volapük", 'short' => "vo", 'english' => "Volapük");

$aCultureCodes["af"]            = array('native' => "Afrikaans", 'short' => "", 'english' => "Afrikaans");
$aCultureCodes["af_ZA"]         = array('native' => "Afrikaans (Suid Afrika)", 'short' => "af", 'english' => "Afrikaans (South Africa)");
$aCultureCodes["ar"]            = array('native' => "العربية", 'short' => "", 'english' => "Arabic");
$aCultureCodes["ar_AE"]         = array('native' => "العربية (الإمارات العربية المتحدة)", 'short' => "ar", 'english' => "Arabic (U.A.E.)");
$aCultureCodes["ar_BH"]         = array('native' => "العربية (البحرين)", 'short' => "", 'english' => "Arabic (Bahrain)");
$aCultureCodes["ar_DZ"]         = array('native' => "العربية (الجزائر)", 'short' => "", 'english' => "Arabic (Algeria)");
$aCultureCodes["ar_EG"]         = array('native' => "العربية (مصر)", 'short' => "", 'english' => "Arabic (Egypt)");
$aCultureCodes["ar_IQ"]         = array('native' => "العربية (العراق)", 'short' => "", 'english' => "Arabic (Iraq)");
$aCultureCodes["ar_JO"]         = array('native' => "العربية (الأردن)", 'short' => "", 'english' => "Arabic (Jordan)");
$aCultureCodes["ar_KW"]         = array('native' => "العربية (الكويت)", 'short' => "", 'english' => "Arabic (Kuwait)");
$aCultureCodes["ar_LB"]         = array('native' => "العربية (لبنان)", 'short' => "", 'english' => "Arabic (Lebanon)");
$aCultureCodes["ar_LY"]         = array('native' => "العربية (ليبيا)", 'short' => "", 'english' => "Arabic (Libya)");
$aCultureCodes["ar_MA"]         = array('native' => "العربية (المملكة المغربية)", 'short' => "", 'english' => "Arabic (Morocco)");
$aCultureCodes["ar_OM"]         = array('native' => "العربية (عمان)", 'short' => "", 'english' => "Arabic (Oman)");
$aCultureCodes["ar_QA"]         = array('native' => "العربية (قطر)", 'short' => "", 'english' => "Arabic (Qatar)");
$aCultureCodes["ar_SA"]         = array('native' => "العربية (المملكة العربية السعودية)", 'short' => "", 'english' => "Arabic (Saudi Arabia)");
$aCultureCodes["ar_SY"]         = array('native' => "العربية (سوريا)", 'short' => "", 'english' => "Arabic (Syria)");
$aCultureCodes["ar_TN"]         = array('native' => "العربية (تونس)", 'short' => "", 'english' => "Arabic (Tunisia)");
$aCultureCodes["ar_YE"]         = array('native' => "العربية (اليمن)", 'short' => "", 'english' => "Arabic (Yemen)");
$aCultureCodes["az"]            = array('native' => "Azərbaycan­ılı", 'short' => "", 'english' => "Azeri");
$aCultureCodes["az-Cyrl_AZ"]    = array('native' => "Азәрбајҹан (Азәрбајҹан)", 'short' => "", 'english' => "Azeri (Cyrillic, Azerbaijan)");
$aCultureCodes["az-Latn_AZ"]    = array('native' => "Azərbaycan­ılı (Azərbaycanca)", 'short' => "az", 'english' => "Azeri (Latin, Azerbaijan)");
$aCultureCodes["be"]            = array('native' => "Беларускі", 'short' => "", 'english' => "Belarusian");
$aCultureCodes["be_BY"]         = array('native' => "Беларускі (Беларусь)", 'short' => "be", 'english' => "Belarusian (Belarus)");
$aCultureCodes["bg"]            = array('native' => "български", 'short' => "", 'english' => "Bulgarian");
$aCultureCodes["bg_BG"]         = array('native' => "български (България)", 'short' => "bg", 'english' => "Bulgarian (Bulgaria)");
$aCultureCodes["bs-Latn_BA"]    = array('native' => "bosanski (Bosna i Hercegovina)", 'short' => "", 'english' => "Bosnian (Bosnia and Herzegovina)");
$aCultureCodes["ca"]            = array('native' => "català", 'short' => "", 'english' => "Catalan");
$aCultureCodes["ca_ES"]         = array('native' => "català (català)", 'short' => "ca", 'english' => "Catalan (Catalan)");
$aCultureCodes["cs"]            = array('native' => "čeština", 'short' => "", 'english' => "Czech");
$aCultureCodes["cs_CZ"]         = array('native' => "čeština (Česká republika)", 'short' => "cs", 'english' => "Czech (Czech Republic)");
$aCultureCodes["cy_GB"]         = array('native' => "Cymraeg (y Deyrnas Unedig)", 'short' => "", 'english' => "Welsh (United Kingdom)");
$aCultureCodes["da"]            = array('native' => "dansk", 'short' => "", 'english' => "Danish");
$aCultureCodes["da_DK"]         = array('native' => "dansk (Danmark)", 'short' => "da", 'english' => "Danish (Denmark)");
$aCultureCodes["de"]            = array('native' => "Deutsch", 'short' => "", 'english' => "German");
$aCultureCodes["de_AT"]         = array('native' => "Deutsch (Österreich)", 'short' => "", 'english' => "German (Austria)");
$aCultureCodes["de_CH"]         = array('native' => "Deutsch (Schweiz)", 'short' => "", 'english' => "German (Switzerland)");
$aCultureCodes["de_DE"]         = array('native' => "Deutsch (Deutschland)", 'short' => "de", 'english' => "German (Germany)");
$aCultureCodes["de_LI"]         = array('native' => "Deutsch (Liechtenstein)", 'short' => "", 'english' => "German (Liechtenstein)");
$aCultureCodes["de_LU"]         = array('native' => "Deutsch (Luxemburg)", 'short' => "", 'english' => "German (Luxembourg)");
$aCultureCodes["dv"]            = array('native' => "ދިވެހިބަސް", 'short' => "", 'english' => "Divehi");
$aCultureCodes["dv_MV"]         = array('native' => "ދިވެހިބަސް (ދިވެހި ރާއްޖެ)", 'short' => "dv", 'english' => "Divehi (Maldives)");
$aCultureCodes["el"]            = array('native' => "ελληνικά", 'short' => "", 'english' => "Greek");
$aCultureCodes["el_GR"]         = array('native' => "ελληνικά (Ελλάδα)", 'short' => "el", 'english' => "Greek (Greece)");
$aCultureCodes["en"]            = array('native' => "English", 'short' => "", 'english' => "English");
$aCultureCodes["en_029"]        = array('native' => "English (Caribbean)", 'short' => "", 'english' => "English (Caribbean)");
$aCultureCodes["en_AU"]         = array('native' => "English (Australia)", 'short' => "", 'english' => "English (Australia)");
$aCultureCodes["en_BZ"]         = array('native' => "English (Belize)", 'short' => "", 'english' => "English (Belize)");
$aCultureCodes["en_CA"]         = array('native' => "English (Canada)", 'short' => "", 'english' => "English (Canada)");
$aCultureCodes["en_GB"]         = array('native' => "English (United Kingdom)", 'short' => "", 'english' => "English (United Kingdom)");
$aCultureCodes["en_IE"]         = array('native' => "English (Eire)", 'short' => "", 'english' => "English (Ireland)");
$aCultureCodes["en_JM"]         = array('native' => "English (Jamaica)", 'short' => "", 'english' => "English (Jamaica)");
$aCultureCodes["en_NZ"]         = array('native' => "English (New Zealand)", 'short' => "", 'english' => "English (New Zealand)");
$aCultureCodes["en_PH"]         = array('native' => "English (Philippines)", 'short' => "", 'english' => "English (Republic of the Philippines)");
$aCultureCodes["en_TT"]         = array('native' => "English (Trinidad y Tobago)", 'short' => "", 'english' => "English (Trinidad and Tobago)");
$aCultureCodes["en_US"]         = array('native' => "English (United States)", 'short' => "en", 'english' => "English (United States)");
$aCultureCodes["en_ZA"]         = array('native' => "English (South Africa)", 'short' => "", 'english' => "English (South Africa)");
$aCultureCodes["en_ZW"]         = array('native' => "English (Zimbabwe)", 'short' => "", 'english' => "English (Zimbabwe)");
$aCultureCodes["es"]            = array('native' => "español", 'short' => "", 'english' => "Spanish");
$aCultureCodes["es_AR"]         = array('native' => "Español (Argentina)", 'short' => "", 'english' => "Spanish (Argentina)");
$aCultureCodes["es_BO"]         = array('native' => "Español (Bolivia)", 'short' => "", 'english' => "Spanish (Bolivia)");
$aCultureCodes["es_CL"]         = array('native' => "Español (Chile)", 'short' => "", 'english' => "Spanish (Chile)");
$aCultureCodes["es_CO"]         = array('native' => "Español (Colombia)", 'short' => "", 'english' => "Spanish (Colombia)");
$aCultureCodes["es_CR"]         = array('native' => "Español (Costa Rica)", 'short' => "", 'english' => "Spanish (Costa Rica)");
$aCultureCodes["es_DO"]         = array('native' => "Español (República Dominicana)", 'short' => "", 'english' => "Spanish (Dominican Republic)");
$aCultureCodes["es_EC"]         = array('native' => "Español (Ecuador)", 'short' => "", 'english' => "Spanish (Ecuador)");
$aCultureCodes["es_ES"]         = array('native' => "español (España)", 'short' => "es", 'english' => "Spanish (Spain)");
$aCultureCodes["es_GT"]         = array('native' => "Español (Guatemala)", 'short' => "", 'english' => "Spanish (Guatemala)");
$aCultureCodes["es_HN"]         = array('native' => "Español (Honduras)", 'short' => "", 'english' => "Spanish (Honduras)");
$aCultureCodes["es_MX"]         = array('native' => "Español (México)", 'short' => "", 'english' => "Spanish (Mexico)");
$aCultureCodes["es_NI"]         = array('native' => "Español (Nicaragua)", 'short' => "", 'english' => "Spanish (Nicaragua)");
$aCultureCodes["es_PA"]         = array('native' => "Español (Panamá)", 'short' => "", 'english' => "Spanish (Panama)");
$aCultureCodes["es_PE"]         = array('native' => "Español (Perú)", 'short' => "", 'english' => "Spanish (Peru)");
$aCultureCodes["es_PR"]         = array('native' => "Español (Puerto Rico)", 'short' => "", 'english' => "Spanish (Puerto Rico)");
$aCultureCodes["es_PY"]         = array('native' => "Español (Paraguay)", 'short' => "", 'english' => "Spanish (Paraguay)");
$aCultureCodes["es_SV"]         = array('native' => "Español (El Salvador)", 'short' => "", 'english' => "Spanish (El Salvador)");
$aCultureCodes["es_UY"]         = array('native' => "Español (Uruguay)", 'short' => "", 'english' => "Spanish (Uruguay)");
$aCultureCodes["es_VE"]         = array('native' => "Español (Republica Bolivariana de Venezuela)", 'short' => "", 'english' => "Spanish (Venezuela)");
$aCultureCodes["et"]            = array('native' => "eesti", 'short' => "", 'english' => "Estonian");
$aCultureCodes["et_EE"]         = array('native' => "eesti (Eesti)", 'short' => "et", 'english' => "Estonian (Estonia)");
$aCultureCodes["eu"]            = array('native' => "euskara", 'short' => "", 'english' => "Basque");
$aCultureCodes["eu_ES"]         = array('native' => "euskara (euskara)", 'short' => "eu", 'english' => "Basque (Basque)");
$aCultureCodes["fa"]            = array('native' => "فارسى", 'short' => "", 'english' => "Persian");
$aCultureCodes["fa_IR"]         = array('native' => "فارسى (ايران)", 'short' => "fa", 'english' => "Persian (Iran)");
$aCultureCodes["fi"]            = array('native' => "suomi", 'short' => "", 'english' => "Finnish");
$aCultureCodes["fi_FI"]         = array('native' => "suomi (Suomi)", 'short' => "fi", 'english' => "Finnish (Finland)");
$aCultureCodes["fo"]            = array('native' => "føroyskt", 'short' => "", 'english' => "Faroese");
$aCultureCodes["fo_FO"]         = array('native' => "føroyskt (Føroyar)", 'short' => "fo", 'english' => "Faroese (Faroe Islands)");
$aCultureCodes["fr"]            = array('native' => "français", 'short' => "", 'english' => "French");
$aCultureCodes["fr_BE"]         = array('native' => "français (Belgique)", 'short' => "", 'english' => "French (Belgium)");
$aCultureCodes["fr_CA"]         = array('native' => "français (Canada)", 'short' => "", 'english' => "French (Canada)");
$aCultureCodes["fr_CH"]         = array('native' => "français (Suisse)", 'short' => "", 'english' => "French (Switzerland)");
$aCultureCodes["fr_FR"]         = array('native' => "français (France)", 'short' => "fr", 'english' => "French (France)");
$aCultureCodes["fr_LU"]         = array('native' => "français (Luxembourg)", 'short' => "", 'english' => "French (Luxembourg)");
$aCultureCodes["fr_MC"]         = array('native' => "français (Principauté de Monaco)", 'short' => "", 'english' => "French (Principality of Monaco)");
$aCultureCodes["gl"]            = array('native' => "galego", 'short' => "", 'english' => "Galician");
$aCultureCodes["gl_ES"]         = array('native' => "galego (galego)", 'short' => "gl", 'english' => "Galician (Galician)");
$aCultureCodes["gu"]            = array('native' => "ગુજરાતી", 'short' => "", 'english' => "Gujarati");
$aCultureCodes["gu_IN"]         = array('native' => "ગુજરાતી (ભારત)", 'short' => "gu", 'english' => "Gujarati (India)");
$aCultureCodes["he"]            = array('native' => "עברית", 'short' => "", 'english' => "Hebrew");
$aCultureCodes["he_IL"]         = array('native' => "עברית (ישראל)", 'short' => "he", 'english' => "Hebrew (Israel)");
$aCultureCodes["hi"]            = array('native' => "हिंदी", 'short' => "", 'english' => "Hindi");
$aCultureCodes["hi_IN"]         = array('native' => "हिंदी (भारत)", 'short' => "hi", 'english' => "Hindi (India)");
$aCultureCodes["hr"]            = array('native' => "hrvatski", 'short' => "", 'english' => "Croatian");
$aCultureCodes["hr_BA"]         = array('native' => "hrvatski (Bosna i Hercegovina)", 'short' => "", 'english' => "Croatian (Bosnia and Herzegovina)");
$aCultureCodes["hr_HR"]         = array('native' => "hrvatski (Hrvatska)", 'short' => "hr", 'english' => "Croatian (Croatia)");
$aCultureCodes["hu"]            = array('native' => "magyar", 'short' => "", 'english' => "Hungarian");
$aCultureCodes["hu_HU"]         = array('native' => "magyar (Magyarország)", 'short' => "hu", 'english' => "Hungarian (Hungary)");
$aCultureCodes["hy"]            = array('native' => "Հայերեն", 'short' => "", 'english' => "Armenian");
$aCultureCodes["hy_AM"]         = array('native' => "Հայերեն (Հայաստան)", 'short' => "hy", 'english' => "Armenian (Armenia)");
$aCultureCodes["id"]            = array('native' => "Bahasa Indonesia", 'short' => "", 'english' => "Indonesian");
$aCultureCodes["id_ID"]         = array('native' => "Bahasa Indonesia (Indonesia)", 'short' => "id", 'english' => "Indonesian (Indonesia)");
$aCultureCodes["is"]            = array('native' => "íslenska", 'short' => "", 'english' => "Icelandic");
$aCultureCodes["is_IS"]         = array('native' => "íslenska (Ísland)", 'short' => "is", 'english' => "Icelandic (Iceland)");
$aCultureCodes["it"]            = array('native' => "italiano", 'short' => "", 'english' => "Italian");
$aCultureCodes["it_CH"]         = array('native' => "italiano (Svizzera)", 'short' => "", 'english' => "Italian (Switzerland)");
$aCultureCodes["it_IT"]         = array('native' => "italiano (Italia)", 'short' => "it", 'english' => "Italian (Italy)");
$aCultureCodes["ja"]            = array('native' => "日本語", 'short' => "", 'english' => "Japanese");
$aCultureCodes["ja_JP"]         = array('native' => "日本語 (日本)", 'short' => "ja", 'english' => "Japanese (Japan)");
$aCultureCodes["ka"]            = array('native' => "ქართული", 'short' => "", 'english' => "Georgian");
$aCultureCodes["ka_GE"]         = array('native' => "ქართული (საქართველო)", 'short' => "ka", 'english' => "Georgian (Georgia)");
$aCultureCodes["kk"]            = array('native' => "Қазащb", 'short' => "", 'english' => "Kazakh");
$aCultureCodes["kk_KZ"]         = array('native' => "Қазақ (Қазақстан)", 'short' => "kk", 'english' => "Kazakh (Kazakhstan)");
$aCultureCodes["kn"]            = array('native' => "ಕನ್ನಡ", 'short' => "", 'english' => "Kannada");
$aCultureCodes["kn_IN"]         = array('native' => "ಕನ್ನಡ (ಭಾರತ)", 'short' => "kn", 'english' => "Kannada (India)");
$aCultureCodes["ko"]            = array('native' => "한국어", 'short' => "", 'english' => "Korean");
$aCultureCodes["kok"]           = array('native' => "कोंकणी", 'short' => "", 'english' => "Konkani");
$aCultureCodes["kok_IN"]        = array('native' => "कोंकणी (भारत)", 'short' => "", 'english' => "Konkani (India)");
$aCultureCodes["ko_KR"]         = array('native' => "한국어 (대한민국)", 'short' => "ko", 'english' => "Korean (Korea)");
$aCultureCodes["ky"]            = array('native' => "Кыргыз", 'short' => "", 'english' => "Kyrgyz");
$aCultureCodes["ky_KG"]         = array('native' => "Кыргыз (Кыргызстан)", 'short' => "ky", 'english' => "Kyrgyz (Kyrgyzstan)");
$aCultureCodes["lt"]            = array('native' => "lietuvių", 'short' => "", 'english' => "Lithuanian");
$aCultureCodes["lt_LT"]         = array('native' => "lietuvių (Lietuva)", 'short' => "lt", 'english' => "Lithuanian (Lithuania)");
$aCultureCodes["lv"]            = array('native' => "latviešu", 'short' => "", 'english' => "Latvian");
$aCultureCodes["lv_LV"]         = array('native' => "latviešu (Latvija)", 'short' => "lv", 'english' => "Latvian (Latvia)");
$aCultureCodes["mi_NZ"]         = array('native' => "Reo Māori (Aotearoa)", 'short' => "", 'english' => "Maori (New Zealand)");
$aCultureCodes["mk"]            = array('native' => "македонски јазик", 'short' => "", 'english' => "Macedonian");
$aCultureCodes["mk_MK"]         = array('native' => "македонски јазик (Македонија)", 'short' => "mk", 'english' => "Macedonian (Former Yugoslav Republic of Macedonia)");
$aCultureCodes["mn"]            = array('native' => "Монгол хэл", 'short' => "", 'english' => "Mongolian");
$aCultureCodes["mn_MN"]         = array('native' => "Монгол хэл (Монгол улс)", 'short' => "mn", 'english' => "Mongolian (Cyrillic, Mongolia)");
$aCultureCodes["mr"]            = array('native' => "मराठी", 'short' => "", 'english' => "Marathi");
$aCultureCodes["mr_IN"]         = array('native' => "मराठी (भारत)", 'short' => "mr", 'english' => "Marathi (India)");
$aCultureCodes["ms"]            = array('native' => "Bahasa Malaysia", 'short' => "", 'english' => "Malay");
$aCultureCodes["ms_BN"]         = array('native' => "Bahasa Malaysia (Brunei Darussalam)", 'short' => "", 'english' => "Malay (Brunei Darussalam)");
$aCultureCodes["ms_MY"]         = array('native' => "Bahasa Malaysia (Malaysia)", 'short' => "ms", 'english' => "Malay (Malaysia)");
$aCultureCodes["mt_MT"]         = array('native' => "Malti (Malta)", 'short' => "", 'english' => "Maltese (Malta)");
$aCultureCodes["nb_NO"]         = array('native' => "norsk, bokmål (Norge)", 'short' => "", 'english' => "Norwegian, Bokmål (Norway)");
$aCultureCodes["nl"]            = array('native' => "Nederlands", 'short' => "", 'english' => "Dutch");
$aCultureCodes["nl_BE"]         = array('native' => "Nederlands (België)", 'short' => "", 'english' => "Dutch (Belgium)");
$aCultureCodes["nl_NL"]         = array('native' => "Nederlands (Nederland)", 'short' => "nl", 'english' => "Dutch (Netherlands)");
$aCultureCodes["nn_NO"]         = array('native' => "norsk, nynorsk (Noreg)", 'short' => "", 'english' => "Norwegian, Nynorsk (Norway)");
$aCultureCodes["no"]            = array('native' => "norsk", 'short' => "no", 'english' => "Norwegian");
$aCultureCodes["ns_ZA"]         = array('native' => "Sesotho sa Leboa (Afrika Borwa)", 'short' => "", 'english' => "Northern Sotho (South Africa)");
$aCultureCodes["pa"]            = array('native' => "ਪੰਜਾਬੀ", 'short' => "", 'english' => "Punjabi");
$aCultureCodes["pa_IN"]         = array('native' => "ਪੰਜਾਬੀ (ਭਾਰਤ)", 'short' => "pa", 'english' => "Punjabi (India)");
$aCultureCodes["pl"]            = array('native' => "polski", 'short' => "", 'english' => "Polish");
$aCultureCodes["pl_PL"]         = array('native' => "polski (Polska)", 'short' => "pl", 'english' => "Polish (Poland)");
$aCultureCodes["pt"]            = array('native' => "Português", 'short' => "", 'english' => "Portuguese");
$aCultureCodes["pt_BR"]         = array('native' => "Português (Brasil)", 'short' => "", 'english' => "Portuguese (Brazil)");
$aCultureCodes["pt_PT"]         = array('native' => "português (Portugal)", 'short' => "pt", 'english' => "Portuguese (Portugal)");
$aCultureCodes["quz_BO"]        = array('native' => "runasimi (Bolivia Suyu)", 'short' => "", 'english' => "Quechua (Bolivia)");
$aCultureCodes["quz_EC"]        = array('native' => "runasimi (Ecuador Suyu)", 'short' => "", 'english' => "Quechua (Ecuador)");
$aCultureCodes["quz_PE"]        = array('native' => "runasimi (Peru Suyu)", 'short' => "", 'english' => "Quechua (Peru)");
$aCultureCodes["ro"]            = array('native' => "română", 'short' => "", 'english' => "Romanian");
$aCultureCodes["ro_RO"]         = array('native' => "română (România)", 'short' => "ro", 'english' => "Romanian (Romania)");
$aCultureCodes["ru"]            = array('native' => "русский", 'short' => "", 'english' => "Russian");
$aCultureCodes["ru_RU"]         = array('native' => "русский (Россия)", 'short' => "ru", 'english' => "Russian (Russia)");
$aCultureCodes["sa"]            = array('native' => "संस्कृत", 'short' => "", 'english' => "Sanskrit");
$aCultureCodes["sa_IN"]         = array('native' => "संस्कृत (भारतम्)", 'short' => "sa", 'english' => "Sanskrit (India)");
$aCultureCodes["se_FI"]         = array('native' => "davvisámegiella (Suopma)", 'short' => "", 'english' => "Sami (Northern) (Finland)");
$aCultureCodes["se_NO"]         = array('native' => "davvisámegiella (Norga)", 'short' => "", 'english' => "Sami (Northern) (Norway)");
$aCultureCodes["se_SE"]         = array('native' => "davvisámegiella (Ruoŧŧa)", 'short' => "", 'english' => "Sami (Northern) (Sweden)");
$aCultureCodes["sk"]            = array('native' => "slovenčina", 'short' => "", 'english' => "Slovak");
$aCultureCodes["sk_SK"]         = array('native' => "slovenčina (Slovenská republika)", 'short' => "sk", 'english' => "Slovak (Slovakia)");
$aCultureCodes["sl"]            = array('native' => "slovenski", 'short' => "", 'english' => "Slovenian");
$aCultureCodes["sl_SI"]         = array('native' => "slovenski (Slovenija)", 'short' => "sl", 'english' => "Slovenian (Slovenia)");
$aCultureCodes["sma_NO"]        = array('native' => "åarjelsaemiengiele (Nöörje)", 'short' => "", 'english' => "Sami (Southern) (Norway)");
$aCultureCodes["sma_SE"]        = array('native' => "åarjelsaemiengiele (Sveerje)", 'short' => "", 'english' => "Sami (Southern) (Sweden)");
$aCultureCodes["smj_NO"]        = array('native' => "julevusámegiella (Vuodna)", 'short' => "", 'english' => "Sami (Lule) (Norway)");
$aCultureCodes["smj_SE"]        = array('native' => "julevusámegiella (Svierik)", 'short' => "", 'english' => "Sami (Lule) (Sweden)");
$aCultureCodes["smn_FI"]        = array('native' => "sämikielâ (Suomâ)", 'short' => "", 'english' => "Sami (Inari) (Finland)");
$aCultureCodes["sms_FI"]        = array('native' => "sääm´ǩiõll (Lää´ddjânnam)", 'short' => "", 'english' => "Sami (Skolt) (Finland)");
$aCultureCodes["sq"]            = array('native' => "shqipe", 'short' => "", 'english' => "Albanian");
$aCultureCodes["sq_AL"]         = array('native' => "shqipe (Shqipëria)", 'short' => "sq", 'english' => "Albanian (Albania)");
$aCultureCodes["sr"]            = array('native' => "srpski", 'short' => "", 'english' => "Serbian");
$aCultureCodes["sr-Cyrl_BA"]    = array('native' => "српски (Босна и Херцеговина)", 'short' => "", 'english' => "Serbian (Cyrillic) (Bosnia and Herzegovina)");
$aCultureCodes["sr-Cyrl_CS"]    = array('native' => "српски (Србија)", 'short' => "", 'english' => "Serbian (Cyrillic, Serbia)");
$aCultureCodes["sr-Latn_BA"]    = array('native' => "srpski (Bosna i Hercegovina)", 'short' => "", 'english' => "Serbian (Latin) (Bosnia and Herzegovina)");
$aCultureCodes["sr-Latn_CS"]    = array('native' => "srpski (Srbija)", 'short' => "sr", 'english' => "Serbian (Latin, Serbia)");
$aCultureCodes["sv"]            = array('native' => "svenska", 'short' => "", 'english' => "Swedish");
$aCultureCodes["sv_FI"]         = array('native' => "svenska (Finland)", 'short' => "", 'english' => "Swedish (Finland)");
$aCultureCodes["sv_SE"]         = array('native' => "svenska (Sverige)", 'short' => "sv", 'english' => "Swedish (Sweden)");
$aCultureCodes["sw"]            = array('native' => "Kiswahili", 'short' => "", 'english' => "Kiswahili");
$aCultureCodes["sw_KE"]         = array('native' => "Kiswahili (Kenya)", 'short' => "sw", 'english' => "Kiswahili (Kenya)");
$aCultureCodes["syr"]           = array('native' => "ܣܘܪܝܝܐ", 'short' => "", 'english' => "Syriac");
$aCultureCodes["syr_SY"]        = array('native' => "ܣܘܪܝܝܐ (سوريا)", 'short' => "syr", 'english' => "Syriac (Syria)");
$aCultureCodes["ta"]            = array('native' => "தமிழ்", 'short' => "", 'english' => "Tamil");
$aCultureCodes["ta_IN"]         = array('native' => "தமிழ் (இந்தியா)", 'short' => "ta", 'english' => "Tamil (India)");
$aCultureCodes["te"]            = array('native' => "తెలుగు", 'short' => "", 'english' => "Telugu");
$aCultureCodes["te_IN"]         = array('native' => "తెలుగు (భారత దేశం)", 'short' => "te", 'english' => "Telugu (India)");
$aCultureCodes["th"]            = array('native' => "ไทย", 'short' => "", 'english' => "Thai");
$aCultureCodes["th_TH"]         = array('native' => "ไทย (ไทย)", 'short' => "th", 'english' => "Thai (Thailand)");
$aCultureCodes["tn_ZA"]         = array('native' => "Setswana (Aforika Borwa)", 'short' => "", 'english' => "Tswana (South Africa)");
$aCultureCodes["tr"]            = array('native' => "Türkçe", 'short' => "", 'english' => "Turkish");
$aCultureCodes["tr_TR"]         = array('native' => "Türkçe (Türkiye)", 'short' => "tr", 'english' => "Turkish (Turkey)");
$aCultureCodes["tt"]            = array('native' => "Татар", 'short' => "", 'english' => "Tatar");
$aCultureCodes["tt_RU"]         = array('native' => "Татар (Россия)", 'short' => "tt", 'english' => "Tatar (Russia)");
$aCultureCodes["uk"]            = array('native' => "україньска", 'short' => "", 'english' => "Ukrainian");
$aCultureCodes["uk_UA"]         = array('native' => "україньска (Україна)", 'short' => "uk", 'english' => "Ukrainian (Ukraine)");
$aCultureCodes["ur"]            = array('native' => "اُردو", 'short' => "", 'english' => "Urdu");
$aCultureCodes["ur_PK"]         = array('native' => "اُردو (پاکستان)", 'short' => "ur", 'english' => "Urdu (Islamic Republic of Pakistan)");
$aCultureCodes["uz"]            = array('native' => "U'zbek", 'short' => "", 'english' => "Uzbek");
$aCultureCodes["uz-Cyrl_UZ"]    = array('native' => "Ўзбек (Ўзбекистон)", 'short' => "", 'english' => "Uzbek (Cyrillic, Uzbekistan)");
$aCultureCodes["uz-Latn_UZ"]    = array('native' => "U'zbek (U'zbekiston Respublikasi)", 'short' => "uz", 'english' => "Uzbek (Latin, Uzbekistan)");
$aCultureCodes["vi"]            = array('native' => "Tiếng Việt", 'short' => "", 'english' => "Vietnamese");
$aCultureCodes["vi_VN"]         = array('native' => "Tiếng Việt (Việt Nam)", 'short' => "vi", 'english' => "Vietnamese (Vietnam)");
$aCultureCodes["xh_ZA"]         = array('native' => "isiXhosa (uMzantsi Afrika)", 'short' => "", 'english' => "Xhosa (South Africa)");
$aCultureCodes["zh_CHS"]        = array('native' => "中文(简体)", 'short' => "", 'english' => "Chinese (Simplified)");
$aCultureCodes["zh_CHT"]        = array('native' => "中文(繁體)", 'short' => "", 'english' => "Chinese (Traditional)");
$aCultureCodes["zh_CN"]         = array('native' => "中文(中华人民共和国)", 'short' => "", 'english' => "Chinese (People's Republic of China)");
$aCultureCodes["zh_HK"]         = array('native' => "中文(香港特别行政區)", 'short' => "", 'english' => "Chinese (Hong Kong S.A.R.)");
$aCultureCodes["zh_MO"]         = array('native' => "中文(澳門特别行政區)", 'short' => "", 'english' => "Chinese (Macao S.A.R.)");
$aCultureCodes["zh_SG"]         = array('native' => "中文(新加坡)", 'short' => "", 'english' => "Chinese (Singapore)");
$aCultureCodes["zh_TW"]         = array('native' => "中文(台灣)", 'short' => "", 'english' => "Chinese (Taiwan)");
$aCultureCodes["zu_ZA"]         = array('native' => "isiZulu (iNingizimu Afrika)", 'short' => "", 'english' => "Zulu (South Africa)");
?>