<?php
/**
 * Project: 
 * drugCMS Content Management System
 * 
 * Description: 
 * Class for creating and outputing only one compressed file
 * 
 * Requirements: 
 * @con_php_req 5.0
 * 
 *
 * @package    drugCMS Content Types
 * @version    1.0.0
 * @author     Rene Mansveld
 * @copyright  Spider IT Deutschland <www.Spider-IT.de>
 * @license    http://www.conlite.org/license/LIZENZ.txt
 * @link       http://www.conlite.org
 * @since      file available since drugCMS release 2.0.0
 * 
 * {@internal 
 *   created 2012-12-15
 *
 *   $Id$:
 * }}
 * 
 */

/**
 * Class for creating and outputing only one compressed file
 */
abstract class Output_Compressor {
    
    /**
     * Worker-Function generate()
     *
     * @param string $sCachePath - Path to the cache folder
     * @param array string $asFiles - Files to include in the compressed output file
     * @return string - Returns the name of the compressed file in the cache folder
     * @access public
     */
    public function generate($sCachePath, $asFiles, $sFileExt, $sFrontendPath) {
        
        # Check for input files
        if ((!is_array($asFiles)) || (!count($asFiles))) {
            return false;
        }
        
        $lastCacheTime = 0;
        $lastChangeTime = 0;
        
        # Generate a unique filename for the compressed file
        $md5 = md5(implode('', $asFiles));
        
        # Check if a file with this name exists in the cache folder
        $found = self::findMatchingFilenames($sCachePath, strtolower($sFileExt), $md5);
        if ($found !== false) {
            # Matching files were found, check which one ist the latest one
            for ($i = 0, $n = count($found); $i < $n; $i ++) {
                $time = substr($found[$i], strlen($md5), 14);
                if ($time > $lastCacheTime) {
                    $lastCacheTime = $time;
                }
            }
        }
        
        # Get the date and time of the last modified input file
        for ($i = 0, $n = count($asFiles); $i < $n; $i ++) {
            if (file_exists($asFiles[$i])) {
                $time = date('YmdHis', filemtime($asFiles[$i]));
                if ($time > $lastChangeTime) {
                    $lastChangeTime = $time;
                }
            }
        }
        
        if ($lastCacheTime == $lastChangeTime) {
            # If the cache file has the date and time of the last changed file, just return it's name
            return $md5 . $lastCacheTime . '.' . $sFileExt;
        } else {
            # Create new cache files
            $content = '';
            
            # Read the input files
            for ($i = 0, $n = count($asFiles); $i < $n; $i ++) {
                if (file_exists($asFiles[$i])) {
                    $cnt = @file_get_contents($asFiles[$i]);
                    
                    # Remove file include commands (those files were added to the array or HTML head section)
                    if (strrpos($asFiles[$i], '/') > 0) {
                        $path = substr($asFiles[$i], 0, (strrpos($asFiles[$i], '/') + 1));
                    } else {
                        $path = '';
                    }
                    $p1 = strpos(strtolower($cnt), '@import url(');
                    while ($p1 !== false) {
                        $p2 = strpos($cnt, ');', $p1);
                        $cnt = substr($cnt, 0, $p1) . substr($cnt, ($p2 + 2));
                        $p1 = strpos(strtolower($cnt), '@import url(');
                    }
                    
                    # Append en extra command end character to javascript files
                    if ($sFileExt == 'js') {
                        $cnt .= ';';
                    }
                    
                    $content .= $cnt . "\n";
                }
            }
            
            # Compress the content
            switch ($sFileExt) {
                case 'css':
                    # Remove '/* ... */' comments
                    $content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content);
                    
                    # Convert line breaks and tabs to a single space
                    $content = str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $content);
                    
                    # Convert multiple spaces to a single space
                    while (strpos($content, '  ') !== false) {
                        $content = str_replace('  ', ' ', $content);
                    }
                    
                    # Remove preceding and trailing spaces
                    $content = trim($content);
                    
                    # Correct paths starting with '../' to start with the client HTML path
                    $content = str_replace(array('../../', '../'), $sFrontendPath, $content);
                    
                    break;
                case 'js':
                    # Convert tabs to a single space
                    $content = str_replace("\t", ' ', $content);
                    
                    # Convert multiple spaces to a single space
                    while (strpos($content, '  ') !== false) {
                        $content = str_replace('  ', ' ', $content);
                    }
                    
                    # Convert line breaks to UNIX style line breaks
                    $content = str_replace(array("\r\n", "\r"), "\n", $content);
                    
                    # Remove preceding and trailing spaces from each line
                    $aCnt = explode("\n", $content);
                    for ($i = 0, $n = count($aCnt); $i < $n; $i ++) {
                        $aCnt[$i] = trim($aCnt[$i]);
                    }
                    $content = implode("\n", $aCnt);
                    unset($aCnt);
                    
                    # Remove blank lines
                    while (strpos($content, "\n\n") !== false) {
                        $content = str_replace("\n\n", "\n", $content);
                    }
                    
                    break;
            }
            
            # Save a new uncompressed cache file, use date and time of the last modified file in the filename
            $fp = fopen($sCachePath . $md5 . $lastChangeTime . '.' . $sFileExt, 'w');
            fwrite($fp, $content);
            fclose($fp);
            
            if (extension_loaded('zlib')) {
                # Save a new compressed cache file, use date and time of the last modified file in the filename
                $fp = fopen($sCachePath . $md5 . $lastChangeTime . '.' . $sFileExt . '.gz', 'w');
                fwrite($fp, gzencode($content, 6, FORCE_GZIP));
                fclose($fp);
            }
            
            # Delete all older versions of the cache file
            for ($i = 0, $n = count($found); $i < $n; $i ++) {
                @unlink($sCachePath . $found[$i]);
                @unlink($sCachePath . $found[$i] . '.gz');
            }
            
            # Return the name of the new cache file
            return $md5 . $lastChangeTime . '.' . $sFileExt;
        }
    }
    
    /**
     * Worker-Function output()
     *
     * @param string $sCachePath - Path to the cache folder
     * @param string $sFilename - Name of the file to deliver
     * @param string $sContentType - Content-Type of the file (eg 'css', 'html' or 'javascript')
     */
    public function output($sCachePath, $sFilename, $sContentType) {
        header('Content-Type: text/' . $sContentType);
        if ((is_file($sCachePath . $sFilename . '.gz')) && (strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) && (extension_loaded('zlib')) && (!ini_get('zlib.output_compression'))) {
            header('Content-Length: ' . filesize($sCachePath . $sFilename . '.gz'));
            header('Content-Encoding: gzip');
            readfile($sCachePath . $sFilename . '.gz');
        } elseif (is_file($sCachePath . $sFilename)) {
            header('Content-Length: ' . filesize($sCachePath . $sFilename));
            readfile($sCachePath . $sFilename);
        }
    }
    
    /**
     * private helper function findMatchingFilenames()
     *
     * @param string $sCachePath - Path to the cache folder
     * @param string $sFileExt - filename extension (eg css)
     * @param string $sFilenamePart - Beginning part of the filename
     * @return - Returns an array of the matching files found or false
     * @access private
     */
    private function findMatchingFilenames($sCachePath, $sFileExt, $sFilenamePart) {
        $pattern = '[0123456789]';
        # Find only files with 14 numerical characters in the filename (yyyymmddHHiiss)
        $files = glob($sCachePath . $sFilenamePart . str_repeat($pattern, 14) . '.' . $sFileExt);
        if (empty($files)) {
            # No suiting filename was found
            return false;
        } else {
            # Return the filenames of the found files
            return array_map('basename', $files);
        }
    }
}
?>