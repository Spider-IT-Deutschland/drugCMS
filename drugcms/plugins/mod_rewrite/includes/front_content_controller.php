<?php
/**
 * Project:
 * Contenido Content Management System
 *
 * Description:
 * Mod Rewrite front_content.php controller. Does some preprocessing jobs, tries
 * to set following variables, depending on mod rewrite configuration and if
 * request part exists:
 * - $client
 * - $changeclient
 * - $lang
 * - $changelang
 * - $idart
 * - $idcat
 *
 * Requirements:
 * @con_php_req 5.0
 *
 *
 * @package    Contenido Backend plugins
 * @version    0.1
 * @author     Murat Purc <murat@purc.de>
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since Contenido release 4.8.15
 *
 * {@internal
 *   created  2008-05-xx
 *
 *   $Id$:
 * }}
 *
 */


defined('CON_FRAMEWORK') or die('Illegal call');


global $client, $changeclient, $cfgClient, $lang, $changelang, $idart, $idcat, $path;

ModRewriteDebugger::add(ModRewrite::getConfig(), 'front_content_controller.php mod rewrite config');


// create an mod rewrite controller instance and execute processing
$oMRController = new ModRewriteController($_SERVER['REQUEST_URI']);
$oMRController->execute();

if ($oMRController->errorOccured()) {

    // an error occured (idcat and or idart couldn't catched by controller)
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');

    $iRedirToErrPage = ModRewrite::getConfig('redirect_invalid_article_to_errorsite', 0);
    // try to redirect to errorpage if desired
    if (($iRedirToErrPage == 1) && ((int) $client > 0) && ((int) $lang > 0)) {
        global $errsite_idcat, $errsite_idart, $error;

        if ($cfgClient['set'] != 'set')   {
            rereadClients();
        }

        $error = 1;
        $idcat = $errsite_idcat[$client];
        $idart = $errsite_idart[$client];
    } elseif (($iRedirToErrPage == 1) && (((int) $client == 0) || ((int) $lang == 0))) {
        $filename = $_SERVER['DOCUMENT_ROOT'] . '/404.html';
        if (file_exists($filename)) {
            readfile($filename);
        } else {
            echo '<!DOCTYPE html>
<html>
    <head>
        <title>404 Not Found</title>
        <meta name="author"         content="drugCMS">                                                                      
        <meta name="keywords"       content="">                                                                             
        <meta name="description"    content="The page you requested coud not be found on this server">                      
        <meta name="robots"         content="noindex,nofollow">                                                             
        <meta name="revisit-after"  content="0">                                                                            
        <meta name="generator"      content="drugCMS">                                                                      
    </head>
    
    <body>
        <h1>404 Not Found</h1>
        <p>The page you requested coud not be found on this server. Please check and correct the address you entered and try again.</p>
        <hr>
        <p style="font-size: small;">' . $_SERVER['SERVER_SOFTWARE'] . ' running on ' . $_SERVER['HTTP_HOST'] . '</p>
    </body>
</html>';
        }
        exit();
    }

} else {

    // set some global variables

    if ($oMRController->getClient()) {
        $client = $oMRController->getClient();
    }

    if ($oMRController->getChangeClient()) {
        $changeclient = $oMRController->getChangeClient();
    }

    if ($oMRController->getLang()) {
        $lang = $oMRController->getLang();
    }

    if ($oMRController->getChangeLang()) {
        $changelang = $oMRController->getChangeLang();
    }

    if ($oMRController->getIdArt()) {
        $idart = $oMRController->getIdArt();
    }

    if ($oMRController->getIdCat()) {
        $idcat = $oMRController->getIdCat();
    }

    if ($oMRController->getPath()) {
        $path = $oMRController->getPath();
    }

}

// some debugs
ModRewriteDebugger::add($mr_preprocessedPageError, 'mr $mr_preprocessedPageError', __FILE__);
ModRewriteDebugger::add($idart, 'mr $idart', __FILE__);
ModRewriteDebugger::add($idcat, 'mr $idcat', __FILE__);
ModRewriteDebugger::add($lang, 'mr $lang', __FILE__);
ModRewriteDebugger::add($client, 'mr $client', __FILE__);

