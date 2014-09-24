<?php
/**
 * Class that will return the tags/keywords of the given text
 * 
 * Add Stopfile words as array: "file|[splitter]" (default:splitter=newline)
 * array("de_stopwords.txt") ' each has own line, separated by \r\n
 * array("de_stopwords.txt|,") 'words separated by ,
 * 
 * @param array $stopFiles
 */
class text2keywords {
	/**
	 * strim some chars like ,:;
	 *
	 * @var boolean
	 */
	public $strip_chars = true;
 
	/**
	 * extra words that should not count
	 *
	 * @var array
	 */
	public $myUnwanted = array();
 
	/**
	 * minimal word long, short will removed
	 *
	 * @var integer
	 */
	public $minString = 3;
 
	/**
	 * Filter words on len an trim it
	 *
	 * @var boolean
	 */
	public $FilterWords = false;
 
	private $_stopWords = array();
 
	/**
	 * Add Stopfile words array: "file|[splitter]" (default:splitter=newline)
	 * array("de_stopwords.txt") ' each has own line, separated by \r\n
	 * array("de_stopwords.txt|,") 'words separated by ,
	 * 
	 * @param array $stopFiles
	 */
	function __construct($stopFiles=Array(),$TrimClean=false) {
		$words1=array();
		if (count($stopFiles)>0) {
			foreach($stopFiles as $key=>$file) {
				$split=$this->GetSplitter($file);
				if ($split=="") {
					$wordsfile=$this->ReadLineFromFile($file);
					$words=$this->SplitWords(str_replace(array("\r\n", "\r"), "\n", $wordsfile));
				} else {
					$wordsfile=$this->ReadLineFromFile(str_replace('|'.$split,"",$file));
					$words=$this->SplitWords($wordsfile,$split);
				}
 
				$this->_stopWords =array_merge($this->_stopWords,$words);
			}
		}
	}
    
	/**
	 * Return tags filtered by stop/unwated words and ordered by usage
	 *
	 * @param string $text
	 * @return array
	 */
	function GetTags($text) {
			$text=strtolower($text);
			if ($this->strip_chars==true) $text=$this->f_strip_chars($text);
			$words=$this->SplitWords($text,' +');
            $words = array_diff($words, array(''));
			if (count($this->_stopWords)>0) $words = array_diff($words, $this->_stopWords);
			if (count($this->myUnwanted)>0)	$words = array_diff($words, $this->myUnwanted);
 
			if ($this->FilterWords==true) $words=$this->f_FilterWords($words); 
 
			$keywordCounts = array_count_values( $words );
			arsort( $keywordCounts, SORT_NUMERIC );
			return $keywordCounts;
	}
 
	private function f_FilterWords($words) {
		$words1=array();
		foreach($words as $word) {
			$word=trim($word);
			if (strlen($word)>=$this->minString) {
				$words1[]=trim($word);
			}
		}
		return $words1;		
	}
 
	/**
	 * return all words in raw format
	 *
	 * @param string $text
	 * @return array
	 */
	function GetAllWords($text) {
		if ($this->strip_chars==true) $text=$this->f_strip_chars($text);
		return $this->SplitWords($text,' +');
	}
 
	private function f_strip_chars($text) {
		$text= preg_replace("/\r\n|\r|\n/"," ",$text);
		return preg_replace("/\.|,|:|;|-|'|\(|\)|\"|\!|\?/"," ",$text);
	}	
 
	private function GetSplitter($txt) {
		if (strlen(strstr($txt,'|'))>0) {
			return str_replace("|","",strstr($txt,'|'));
		}
        return "";
	}
 
	private function SplitWords($text,$splitter="\n") {
		$text=strtolower($text);
		return mb_split($splitter, $text);
	}
 
	private function ReadLineFromFile($datei) {
		if (!file_exists($datei)) return "";
		$back="";
		$fhandler=fopen($datei,"r");
		while(!feof($fhandler))	{
			$tHelp=fgets($fhandler);
			if ($this->StartsWithComment($tHelp)==false) $back .= $tHelp;
		}
		fclose($fhandler);
		return $back;
	}
 
	/**
	 * StartsWithComment
	 * Tests if a line starts with space,',*,#,/,; or \.
	 *
	 * @param     string
	 * @param     string
	 * @return    bool
	 */
	private function StartsWithComment($str){
		return preg_match('/^[ |\'|*|#|\/|;|\\\]/', $str);
	}
}
?>