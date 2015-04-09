<?php
$edit = false;
$sContent = '';

$bOK = true;

# Check if the client's cache folder is enabled in .htaccess (if exists)
if (file_exists($cfgClient[$client]['path']['frontend'] . '.htaccess')) {
    $sCnt = file_get_contents($cfgClient[$client]['path']['frontend'] . '.htaccess');
    $bOK = preg_match('/\n *RewriteRule \^cache\/\.\*\$ - \[L\]/i', $sCnt);
    if (!$bOK) {
        $sContent .= i18n("In order to use this functionallity, you need to enable the client's cache folder in it's .htaccess file.<br />Please add the following line to the 'Exclude some files and directories from rewriting' section:<br />RewriteRule ^cache/.*$ - [L]", 'seocheck');
    }
} elseif (file_exists($cfg['path']['frontend'] . '.htaccess')) {
    $sClientDir = str_replace($cfg['path']['frontend'] . ((substr($cfg['path']['frontend'], -1) == '/') ? '' : '/'), '', $cfgClient[$client]['path']['frontend']);
    $sCnt = file_get_contents($cfg['path']['frontend'] . '.htaccess');
    $bOK = preg_match('/\n *RewriteRule \^' . str_replace('/', '\/', $sClientDir) . 'cache\/\.\*\$ - \[L\]/i', $sCnt);
    if (!$bOK) {
        $sContent .= str_replace('|1', $sClientDir, i18n("In order to use this functionallity, you need to enable the client's cache folder in your main .htaccess file.<br />Please add the following line to the 'Exclude some files and directories from rewriting' section:<br />RewriteRule ^|1cache/.*$ - [L]", 'seocheck'));
    }
}

if ($bOK) {
    
    # Check if the article to check is online
    $oArt = new Article($idart, $client, $lang, $idartlang);
    $bOK = ($oArt->getField('online') == 1);
    if (!$bOK) {
        $sContent .= i18n("In order to check this article, it must be online, without any restrictions.", 'seocheck');
    }
}

# If the client's cache folder is enabled, and the article is online, seocheck the page
if ($bOK) {
    $sFile = 'cache/seocheck-' . $idartlang . '.html';
    try {
        $sURL = 'https://www.seobility.net/de/seocheck/check?url=' . urlencode(Contenido_Url::getInstance()->build(array('idcat' => $idcat, 'idart' => $idart, 'client' => $client, 'lang' => $lang), true));
    } catch (InvalidArgumentException $e) {
        $sURL = 'https://www.seobility.net/de/seocheck/check?url=' . urlencode($cfgClient[$client]['path']['htmlpath'] . 'front_content.php?idartlang=' . $idartlang);
    }
    $errno = 0;
    $errmsg = '';
    if (!getRemoteContentToFile($sURL, $cfgClient[$client]['path']['frontend'] . $sFile, $errno, $errmsg)) {
        $sContent .= 'Error ' . $errno . ': ' . $errmsg;
    } else {
        $sCnt = file_get_contents($cfgClient[$client]['path']['frontend'] . $sFile);
        unlink($cfgClient[$client]['path']['frontend'] . $sFile);
        if (strlen($sCnt)) {
            
            # Eliminate rows we don't want to show
            while (($p1 = strpos($sCnt, '<div class="mcxrow">')) !== false) {
                $n1 = 1;
                $n2 = 0;
                $p2 = strpos($sCnt, '<div', ($p1 + 1));
                $p3 = strpos($sCnt, '</div>', ($p1 + 1));
                $p4 = 0;
                while ($n1 > $n2) {
                    if (($p2 > $p3) || ($p2 === false)) {
                        $n2 ++;
                        $p4 = $p3;
                        $p3 = strpos($sCnt,'</div', ($p3 + 1));
                    } else {
                        $n1 ++;
                        $p2 = strpos($sCnt, '<div', ($p2 + 1));
                    }
                }
                $sCnt = substr($sCnt, 0, $p1) . substr($sCnt, ($p4 + 6));
            }
            
            # Eliminate more rows
            while (($p1 = strpos($sCnt, '<div class="incontentbox')) !== false) {
                $n1 = 1;
                $n2 = 0;
                $p2 = strpos($sCnt, '<div', ($p1 + 1));
                $p3 = strpos($sCnt, '</div>', ($p1 + 1));
                $p4 = 0;
                while ($n1 > $n2) {
                    if (($p2 > $p3) || ($p2 === false)) {
                        $n2 ++;
                        $p4 = $p3;
                        $p3 = strpos($sCnt,'</div', ($p3 + 1));
                    } else {
                        $n1 ++;
                        $p2 = strpos($sCnt, '<div', ($p2 + 1));
                    }
                }
                $sCnt = substr($sCnt, 0, $p1) . substr($sCnt, ($p4 + 6));
            }
            
            # Eliminate the double ad
            if ($p1 = strrpos($sCnt, '<div class="col-md-12">')) {
                $n1 = 1;
                $n2 = 0;
                $p2 = strpos($sCnt, '<div', ($p1 + 1));
                $p3 = strpos($sCnt, '</div>', ($p1 + 1));
                $p4 = 0;
                while ($n1 > $n2) {
                    if (($p2 > $p3) || ($p2 === false)) {
                        $n2 ++;
                        $p4 = $p3;
                        $p3 = strpos($sCnt,'</div', ($p3 + 1));
                    } else {
                        $n1 ++;
                        $p2 = strpos($sCnt, '<div', ($p2 + 1));
                    }
                }
                $sCnt = substr($sCnt, 0, $p1) . substr($sCnt, ($p4 + 6));
                $sCnt = str_replace('<a ', '<a target="_blank" ', $sCnt);
            }
            
            file_put_contents($cfgClient[$client]['path']['frontend'] . $sFile, $sCnt);
            $sContent .= '<iframe src="' . $cfgClient[$client]['path']['htmlpath'] . $sFile . '" style="width: 100%; height: 1300px; border: 0px none;"></iframe>';
            
            # Insert js code to position and size the iframe
            $sSrc = '
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script type="text/javascript">
//<![CDATA[
    $(document).ready(function() {
        $("body").css("margin", "0px");
        $("iframe").css("width", $(window).width() + "px");
    });
//]]>
</script>
';
            $sContent .= $sSrc;
        } else {
            $sContent .= i18n("Error, no content returned", 'seocheck');
        }
    }
}
$oTpl = new Template();
$oTpl->setEncoding('UTF-8');
$oTpl->set('s', 'CONTENT', $sContent);
$oTpl->generate($cfg["path"]['contenido'] . $cfg["path"]["plugins"] . 'seocheck/templates/standard/page.html', false, false);
?>