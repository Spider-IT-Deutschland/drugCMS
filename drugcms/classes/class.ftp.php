<?php
/**
 * Project:
 * drugCMS Content Management System
 *
 * Description:
 * FTP communication class
 *
 * Requirements:
 * @con_php_req 5.4
 *
 *
 * @package    drugCMS core
 * @version    1.0
 * @author     René Mansveld
 * @copyright  Spider IT Deutschland
 * @license    http://www.drugcms.org/license/LICENCE.txt
 * @link       http://www.drugcms.org
 * @link       http://www.spider-it.de
 * @since      file available since drugCMS release 2.1.0
 *
 * {@internal
 *   created  2016-02-24
 * }}
 *
 */

class ftp {
    var $conn = false;
    var $pasv = true;
    var $temp = '';
    var $cdir = '/';
    
    public function __construct($host, $port, $user, $password, $rootdir) {
        global $cfg;
        
        $this->temp = $cfg['path']['contenido'] . 'data/cache/';
        $this->conn = ftp_connect($host, $port);
        if ($this->conn !== false) {
            if (ftp_login ($this->conn, $user, $password)) {
                $this->path = ftp_pwd($this->conn);
                ftp_pasv($this->conn, $this->pasv);
                return true;
            }
        }
        return false;
    }
    
    public function __destruct() {
        if ($this->conn) {
            ftp_quit($this->conn);
        }
    }
    
    public function ls($dir) {
        $list = ftp_rawlist($this->conn, $dir);
        $p = '/';
        // mod
        $p .= '([ldps-]{1})([rwx-]{9})\s+';
        // int
        $p .= '([\d]+)\s+';
        // user
        $p .= '([\da-z-_]+)\s+';
        // group
        $p .= '([\da-z-_]+)\s+';
        // size
        $p .= '([\d]+)\s+';
        // date
        $p .= '(\S+\s+\S+\s+\S+)\s+';
        // filename
        $p .= '(.+)';
        // end
        $p .= '$/si';
        $j = 0;
        $k = 0;
        $o = array();
        $d = array();
        foreach ($list as $entry) {
            preg_match($p, $entry, $match);
            if (($match[8] != '.') and ($match[8] != '..')) {
                if ($match[1] == 'd') {
/*
                    $o[$j]['all'] = $match[0];
                    $o[$j]['type'] = $match[1];
                    $o[$j]['mod'] = $match[2];
                    $o[$j]['bit'] = $match[3];
                    $o[$j]['user'] = $match[4];
                    $o[$j]['group'] = $match[5];
                    $o[$j]['size'] = $match[6];
                    $o[$j]['date'] = $match[7];
                    $o[$j]['file'] = $match[8];
*/
                    $o[$j] = $match[8];
                    $j++;
                } else {
/*
                    $d[$k]['all'] = $match[0];
                    $d[$k]['type'] = $match[1];
                    $d[$k]['mod'] = $match[2];
                    $d[$k]['bit'] = $match[3];
                    $d[$k]['user'] = $match[4];
                    $d[$k]['group'] = $match[5];
                    $d[$k]['size'] = $match[6];
                    $d[$k]['date'] = $match[7];
                    $d[$k]['file'] = $match[8];
*/
                    $d[$k] = $match[8];
                    $k++;
                }
            }
        }
        // Ordnerarray sortieren
        natcasesort($o);
        // Ordnerarray ausgeben
        while (list($key, $value) = each($o)) {
            $i++;
            $r[$i]['file'] = $value;
            $r[$i]['type'] = 'd';
        }
        unset($o);
        // Dateiarray sortieren
        natcasesort($d);
        // Dateiarray ausgeben
        while (list($key, $value) = each($d)) {
            $i++;
            $r[$i]['file'] = $value;
            $r[$i]['type'] = '-';
        }
        unset($d);
        return $r;
    }
    
    public function cd($dir) {
        return $this->chdir($dir);
    }
    
    public function chdir($dir) {
        if (ftp_chdir($this->conn, $dir)) {
            $this->cdir = $dir;
            return true;
        }
        return false;
    }
    
    public function mkdir($dir) {
        return ftp_mkdir($this->conn, $dir);
    }
    
    public function rmdir($dir, $recursive = false) {
        if ($recursive) {
            $list = $this->ls($dir);
            foreach ($list as $entry) {
                if ($entry['type'] == 'd') {
                    $this->rmdir($dir . '/' . $entry['file'], true);
                }
                else {
                    $this->rm($dir . '/' . $entry['file']);
                }
            }
        }
        return ftp_rmdir($this->conn, $dir);
    }
    
    public function isdir($dir) {
        if (ftp_chdir($this->conn, $dir)) {
            ftp_chdir($this->conn, $this->cdir);
            return true;
        }
        return false;
    }
    
    public function rm($file) {
        return ftp_delete($this->conn, $file);
    }
    
    public function delete($file) {
        return ftp_delete($this->conn, $file);
    }
    
    public function get($file) {
        $tmpfile = md5(microtime());
        $tmpfile = $this->temp . $tmpfile;
        ftp_get ($this->conn, $tmpfile, $file, FTP_BINARY);
        $fp = fopen($file, 'r');
        $data = fread($fp, filesize($file));
        fclose ($fp);
        unlink ($tmpfile);
        return $data;
    }
    
    public function put($file, $sourcefile) {
        return ftp_put($this->conn, $file, $sourcefile, FTP_BINARY);
    }
    
    public function putAll($source_directory, $target_directory) {
        $d = dir($source_directory);
        
        # do this for each file in the directory
        while ($file = $d->read()) {
            
            # to prevent an infinite loop
            if ($file != "." && $file != "..") {
                
                # do the following if it is a directory
                if (is_dir($source_directory . '/' . $file)) {
                    
                    if (!$this->chdir($target_directory . '/' . $file)) {
                        
                        # create directories that do not yet exist
                        if (!$this->mkdir($target_directory . '/' . $file)) {
                            return false;
                        }
                    }
                    
                    # recursive part
                    if (!$this->putAll($source_directory . '/' . $file, $target_directory . '/' . $file)) {
                        return false;
                    }
                }
                else {
                    
                    # put the files
                    if (!$this->put($target_directory . '/' . $file, $source_directory . '/' . $file)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public function quit() {
        $ret = ftp_quit($this->conn);
        if ($ret) {
            $this->conn = false;
        }
        return $ret;
    }
}
?>