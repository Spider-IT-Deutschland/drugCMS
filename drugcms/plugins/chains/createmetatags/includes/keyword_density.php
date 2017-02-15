<?php
// security check
defined('CON_FRAMEWORK') or die('Illegal call');

/**
 * Compare int values
 * 
 * @param int $a
 * @param int $b
 * @return int 0 for equal, 1 for higher and -1 for lower
 */
function __cmp($a, $b) {
    if ($a == $b) return 0;
    return ($a > $b) ? -1 : 1;
}

/**
 * 
 * @param array $singlewordcounter
 * @param int $maxKeywords
 * @return array Array with valued keywords
 */
function stripCount($singlewordcounter, $maxKeywords = 15) {
    // strip all with only 1
    $tmp = array();
    $result = array();
    $tmpToRemove = 1;
    
    foreach ($singlewordcounter as $key => $value) {
        if ($value > $tmpToRemove) {
            $tmp[$key] = $value;
        }
    }
    
    if (sizeof($tmp) <= $maxKeywords) {
        foreach ($tmp as $key => $value) {
            $result[] = $key;
        }
    } else {
        $dist = array();
        foreach ($tmp as $key => $value) {
            $dist[$value]++;
        }
        uksort($dist, "__cmp");
        reset($dist);
        $count = 0;
        $resultset = array();
        $useQuantity = array();
        
        foreach ($dist as $key => $value) {
            $_count = $count + $value;
            if ($_count <= $maxKeywords) {
                $count += $value;
                $useQuantity[] = $key;
            } else {
                break;
            }
        }
        // run all keywords and select by quantities to use
        foreach ($singlewordcounter as $key => $value) {
            if (in_array($value, $useQuantity)) {
                $result[] = $key;
            }
        }
    }
    return $result;
}

/**
 * Generate keywords from content
 * 
 * @version 1.0
 * @since 2.0.0
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * 
 * @param string $sHeadline
 * @param string $sText
 * @param string $sEncoding
 * @param int $iMinLen
 * @return mixed commaseparated string of keywords or false
 */
function keywordDensity($sHeadline, $sText, $sEncoding = "UTF-8", $iMinLen = 5) {
    global $cfg, $encoding, $lang;
    
    $sHeadline = strip_tags($sHeadline);
    $sText = strip_tags($sText);
    $sText = html_entity_decode($sText, ENT_QUOTES, $sEncoding);
    
    $sAll = ($sHeadline . ' ' . $sHeadline . ' ' . $sText);
    if (mb_strtoupper($sEncoding) != 'UTF-8') {
        mb_convert_encoding($sAll, 'UTF-8', $sEncoding);
    }
    
    $db = new DB();
    $sql = 'SELECT value
            FROM ' . $cfg['tab']['properties'] . '
            WHERE ((itemtype="idlang")
               AND (type="language")
               AND (name="code")
               AND (itemid=' . $lang . '))';
    $db->query($sql);
    if ($db->next_record()) {
        $sLang = dirname(__FILE__) . '/../conf/stopwords_' . $db->f('value') . '.txt';
        if (!is_file($sLang)) {
            $sLang = '';
        }
    } else {
        $sLang = '';
    }
    $db->disconnect();
    include_once(dirname(dirname(__FILE__)) . '/classes/class.keywords.php');
    $t2k = new text2keywords(array($sLang));
    $aKeywords = $t2k->GetTags($sAll);
    $aKeywords = array_slice($aKeywords, 0, 15, true);
    
    if (mb_strtoupper($sEncoding) != 'UTF-8') {
        mb_convert_encoding($sAll, $sEncoding, 'UTF-8');
    }
    return implode(', ', array_keys($aKeywords));
}

/**
 * Check keyword against stopword list
 * 
 * @version 1.0
 * @since 2.0.0
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @todo move stopwords to sqlite
 * 
 * @global int $lang
 * @global array $encoding
 * @param string $sWord
 * @return boolean
 */
function clIsStopWord($sWord) {
    global $lang, $encoding;    
    $aStopWords = array();
    $aStopWords['de_DE'] = array("aber", "als", "am", "an", "auch", "auf", "aus", "bei", "bin",
        "bis", "bist", "da", "dadurch", "daher", "darum", "das", "daß", "dass", "dein",
        "deine", "dem", "den", "der", "des", "dessen", "deshalb", "die", "dies", "dieser", "dieses",
        "doch", "dort", "du", "durch", "ein", "eine", "einem", "einen", "einer", "eines", "er",
        "es", "euer", "eure", "für", "hatte", "hatten", "hattest", "hattet", "hier", "hinter",
        "ich", "ihr", "ihre", "im", "in", "ist", "ja", "jede", "jedem", "jeden", "jeder", "jedes",
        "jener", "jenes", "jetzt", "kann", "kannst", "können", "könnt", "machen", "mein",
        "meine", "mit", "muß", "mußt", "musst", "müssen", "müßt", "nach", "nachdem", "nein",
        "nicht", "nun", "oder", "seid", "sein", "seine", "sich", "sie",
        "sind", "soll", "sollen", "sollst", "sollt", "sonst", "soweit", "sowie", "und", "unser",
        "unsere", "unter", "vom", "von", "vor", "wann", "warum", "was", "weiter", "weitere", "wenn",
        "wer", "werde", "werden", "werdet", "weshalb", "wie", "wieder", "wieso", "wir", "wird",
        "wirst", "wo", "woher", "wohin", "zu", "zum", "zur", "über");
    $aStopWords['en_EN'] = $aStopWords['en_US'] = array("a", "able", "about", "above", "abst",
        "accordance", "according", "accordingly", "across", "act", "actually", "added", "adj",
        "affected", "affecting", "affects", "after", "afterwards", "again", "against", "ah", "all",
        "almost", "alone", "along", "already", "also", "although", "always", "am", "among",
        "amongst", "an", "and", "announce", "another", "any", "anybody", "anyhow", "anymore",
        "anyone", "anything", "anyway", "anyways", "anywhere", "apparently", "approximately",
        "are", "aren", "arent", "arise", "around", "as", "aside", "ask", "asking", "at", "auth",
        "available", "away", "awfully", "b", "back", "be", "became", "because", "become", "becomes",
        "becoming", "been", "before", "beforehand", "begin", "beginning", "beginnings", "begins",
        "behind", "being", "believe", "below", "beside", "besides", "between", "beyond", "biol",
        "both", "brief", "briefly", "but", "by", "c", "ca", "came", "can", "cannot", "can't",
        "cause", "causes", "certain", "certainly", "co", "com", "come", "comes", "contain",
        "containing", "contains", "could", "couldnt", "d", "date", "did", "didn't", "different",
        "do", "does", "doesn't", "doing", "done", "don't", "down", "downwards", "due", "during",
        "e", "each", "ed", "edu", "effect", "eg", "eight", "eighty", "either", "else", "elsewhere",
        "end", "ending", "enough", "especially", "et", "et-al", "etc", "even", "ever", "every",
        "everybody", "everyone", "everything", "everywhere", "ex", "except", "f", "far", "few",
        "ff", "fifth", "first", "five", "fix", "followed", "following", "follows", "for", "former",
        "formerly", "forth", "found", "four", "from", "further", "furthermore", "g", "gave", "get",
        "gets", "getting", "give", "given", "gives", "giving", "go", "goes", "gone", "got", "gotten",
        "h", "had", "happens", "hardly", "has", "hasn't", "have", "haven't", "having", "he", "hed",
        "hence", "her", "here", "hereafter", "hereby", "herein", "heres", "hereupon", "hers",
        "herself", "hes", "hi", "hid", "him", "himself", "his", "hither", "home", "how", "howbeit",
        "however", "hundred", "i", "id", "ie", "if", "i'll", "im", "immediate", "immediately",
        "importance", "important", "in", "inc", "indeed", "index", "information", "instead", "into",
        "invention", "inward", "is", "isn't", "it", "itd", "it'll", "its", "itself", "i've", "j",
        "just", "k", "keep", "keeps", "kept", "kg", "km", "know", "known", "knows", "l", "largely",
        "last", "lately", "later", "latter", "latterly", "least", "less", "lest", "let", "lets",
        "like", "liked", "likely", "line", "little", "'ll", "look", "looking", "looks", "ltd", "m",
        "made", "mainly", "make", "makes", "many", "may", "maybe", "me", "mean", "means", "meantime",
        "meanwhile", "merely", "mg", "might", "million", "miss", "ml", "more", "moreover", "most",
        "mostly", "mr", "mrs", "much", "mug", "must", "my", "myself", "n", "na", "name", "namely",
        "nay", "nd", "near", "nearly", "necessarily", "necessary", "need", "needs", "neither",
        "never", "nevertheless", "new", "next", "nine", "ninety", "no", "nobody", "non", "none",
        "nonetheless", "noone", "nor", "normally", "nos", "not", "noted", "nothing", "now", "nowhere",
        "o", "obtain", "obtained", "obviously", "of", "off", "often", "oh", "ok", "okay", "old",
        "omitted", "on", "once", "one", "ones", "only", "onto", "or", "ord", "other", "others",
        "otherwise", "ought", "our", "ours", "ourselves", "out", "outside", "over", "overall", "owing",
        "own", "p", "page", "pages", "part", "particular", "particularly", "past", "per", "perhaps",
        "placed", "please", "plus", "poorly", "possible", "possibly", "potentially", "pp", "predominantly",
        "present", "previously", "primarily", "probably", "promptly", "proud", "provides", "put",
        "q", "que", "quickly", "quite", "qv", "r", "ran", "rather", "rd", "re", "readily", "really",
        "recent", "recently", "ref", "refs", "regarding", "regardless", "regards", "related",
        "relatively", "research", "respectively", "resulted", "resulting", "results", "right",
        "run", "s", "said", "same", "saw", "say", "saying", "says", "sec", "section", "see",
        "seeing", "seem", "seemed", "seeming", "seems", "seen", "self", "selves", "sent", "seven",
        "several", "shall", "she", "shed", "she'll", "shes", "should", "shouldn't", "show",
        "showed", "shown", "showns", "shows", "significant", "significantly", "similar", "similarly",
        "since", "six", "slightly", "so", "some", "somebody", "somehow", "someone", "somethan",
        "something", "sometime", "sometimes", "somewhat", "somewhere", "soon", "sorry", "specifically",
        "specified", "specify", "specifying", "still", "stop", "strongly", "sub", "substantially",
        "successfully", "such", "sufficiently", "suggest", "sup", "sure", "t", "take", "taken",
        "taking", "tell", "tends", "th", "than", "thank", "thanks", "thanx", "that", "that'll",
        "thats", "that've", "the", "their", "theirs", "them", "themselves", "then", "thence",
        "there", "thereafter", "thereby", "thered", "therefore", "therein", "there'll", "thereof",
        "therere", "theres", "thereto", "thereupon", "there've", "these", "they", "theyd", "they'll",
        "theyre", "they've", "think", "this", "those", "thou", "though", "thoughh", "thousand",
        "throug", "through", "throughout", "thru", "thus", "til", "tip", "to", "together", "too",
        "took", "toward", "towards", "tried", "tries", "truly", "try", "trying", "ts", "twice",
        "two", "u", "un", "under", "unfortunately", "unless", "unlike", "unlikely", "until", "unto",
        "up", "upon", "ups", "us", "use", "used", "useful", "usefully", "usefulness", "uses", "using",
        "usually", "v", "value", "various", "'ve", "very", "via", "viz", "vol", "vols", "vs", "w",
        "want", "wants", "was", "wasn't", "way", "we", "wed", "welcome", "we'll", "went", "were",
        "weren't", "we've", "what", "whatever", "what'll", "whats", "when", "whence", "whenever",
        "where", "whereafter", "whereas", "whereby", "wherein", "wheres", "whereupon", "wherever",
        "whether", "which", "while", "whim", "whither", "who", "whod", "whoever", "whole", "who'll",
        "whom", "whomever", "whos", "whose", "why", "widely", "willing", "wish", "with", "within",
        "without", "won't", "words", "world", "would", "wouldn't", "www", "x", "y", "yes", "yet",
        "you", "youd", "you'll", "your", "youre", "yours", "yourself", "yourselves", "you've", "z", "zero");    
    
    if(is_int($lang) && is_array($encoding)) {
        $sUseEnc = $encoding[$lang];
        if(empty($sUseEnc) || !array_key_exists($sUseEnc, $aStopWords)) {
            $sUseEnc = "de_DE";
        }
    }
    
    if(in_array(utf8_encode($sWord), $aStopWords['de_DE'])) {
        return true;
    }
    return false;
}
?>