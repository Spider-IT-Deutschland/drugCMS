<?php
function myfile($url) {
    if (function_exists('curl_version')) {
        $options = array(
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER         => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING       => "",       // handle compressed
            CURLOPT_USERAGENT      => "spider", // who am i
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 10,       // timeout on connect
            CURLOPT_TIMEOUT        => 10,       // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
        );
        
        $ch = @curl_init($url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $errno   = curl_errno($ch);
        $errmsg  = curl_error($ch);
        $header  = curl_getinfo($ch);
        curl_close($ch);
        
        if (($errno == 0) && ($header['http_code'] == 200)) {
            return explode("\n", str_replace(array("\r\n", "\r"), "\n", $content));
        }
    } else {
        // URL zerlegen
        $parsedurl = @parse_url($url);
        // Host ermitteln, ungültigen Aufruf abfangen
        if (empty($parsedurl['host'])) {
            return null;
        }
        $host = $parsedurl['host'];
        // Pfadangabe ermitteln
        if (empty($parsedurl['path'])) {
            $documentpath = '/';
        } else {
            $documentpath = $parsedurl['path'];
        }
        // Parameter ermitteln
        if (!empty($parsedurl['query'])) {
            $documentpath .= '?' . $parsedurl['query'];
        }
        // Port ermitteln
        if (!empty($parsedurl['port'])) {
            $port = $parsedurl['port'];
        } else {
            $port = 80;
        }
        // Socket öffnen
        $fp = @fsockopen($host, $port, $errno, $errstr, 30);
        if (!$fp) {
            return null;
        }
        // Request senden
        fputs ($fp, "GET {$documentpath} HTTP/1.0\r\nHost: {$host}\r\n\r\n");
        // Header auslesen
        do {
            $line = chop(fgets($fp));
        } while ((!empty($line)) && (!feof($fp)));
        // Daten auslesen
        $result = Array();
        while (!feof($fp)) {
            $result[] = fgets($fp);
        }
        // Socket schliessen
        fclose($fp);
        // Ergebnis-Array zurückgeben
        return $result;
    }
}
function prepareStringForOutput($sIn, $sCode = 'ISO-8859-1') {
    global $encoding, $lang;
    
    if ((strtoupper($sCode) == 'UTF-8') && (strtoupper($encoding[$lang]) != 'UTF-8')) {
        return utf8_decode($sIn);
    } elseif ((strtoupper($encoding[$lang]) == 'UTF-8') && (strtoupper($sCode) != 'UTF-8')) {
        return utf8_encode($sIn);
    } else {
        return $sIn;
    }
}
?>