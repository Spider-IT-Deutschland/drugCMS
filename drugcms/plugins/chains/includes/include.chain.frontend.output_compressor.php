<?php
/**
 * Project: 
 * drugCMS Content Management System
 * 
 * Description: 
 * Generate compressed output for frontend css and js files
 * 
 * Client setting to switch output compression must look like this:
 * Type:    generator
 * Name:    output_compression
 * Value:   full / correct / off
 *          full = compress css and js files, link them in the head area (default)
 *          correct = move css and javascript file links to the head area
 *          off = no correction and compression (use for development only)
 * 
 * Requirements: 
 * @con_php_req 5.0
 * 
 *
 * @package    drugCMS Frontend classes
 * @version    1.0.0
 * @author     Rene Mansveld
 * @copyright  Spider IT Deutschland <www.spider-it.de>
 * @license    http://www.drugcms.org/license/LIZENZ.txt
 * @link       http://www.drugcms.org
 * 
 * {@internal 
 *   created 2012-12-16
 *
 *   $Id$: 
 * }}
 * 
 */
 
if(!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

function cecOutputCompressor($sCode) {
    global $cfgClient, $client;
    
    # How is output compression switched?
    $switch = getEffectiveSetting('generator', 'output_compression', 'full');
    if ($switch == 'off') {
        return $sCode;
    }
    
    # Remove comments (NOT IE conditional comment tags and HTML comments in Javascript blocks) from the code
    $p0 = strpos($sCode, '</body>');
    $p1 = strpos($sCode, '<!--');
    while (($p1 !== false) && ($p1 < $p0)) {
        if (substr($sCode, $p1, 7) == '<!--[if') {
            # IE conditional comment start tag
            $p2 = strpos($sCode, ']-->', ($p1 + 1));
            $p1 = strpos($sCode, '<!--', ($p2 + 1));
# TODO: Better recognition of script blocks
        } elseif ((substr(str_replace(array("\n", ' '), '', substr($sCode, 0, $p1)), -30) == '<script type="text/javascript">') || (substr(str_replace(array("\n", ' '), '', substr($sCode, 0, $p1)), -30) == "<script type='text/javascript'>")) {
            # HTML comment in Javascript block (e.g. <script type="text/javascript"><!--  --></script>)
            $p1 = strpos($sCode, '<!--', ($p1 + 1));
        } else {
            $p2 = strpos($sCode, '-->', $p1);
            while (substr($sCode, ($p2 - 6), 9) == 'endif]-->') {
                # IE conditional comment end tag, but in commented block
                $p2 = strpos($sCode, '-->', ($p2 + 1));
            }
            $tmp = substr($sCode, $p1, ($p2 - $p1 + 3));
            $sCode = str_replace($tmp, '', $sCode);
            $p0 = strpos($sCode, '</body>');
            $p1 = strpos($sCode, '<!--', $p1);
        }
    }
    
    # First find all style and script link tags and script blocks inside IE conditional comments (eg <!--[if IE]>...<![endif]-->)
    # We can move the complete block to the end of the head/body section if only link tags are included, otherwise we must move only
    # the links while rebuilding the IE conditional comments around them
    $IE = array();
    $IElinks = array();
    $IEscripts = array();
    $IEscriptblocks = array();
    $IEstyles = array();
    $p1 = strpos($sCode, '<!--[if');
    while ($p1 !== false) {
        # First get the exact start comment
        $p2 = strpos($sCode, '>', $p1);
        $c = substr($sCode, $p1, (($p2 - $p1) + 1));
        if (substr($sCode, ($p2 + 1), 5) == '<!-->') {
            $c .= '<!-->';
        }
        $IEtmp = array();
        $p2 = strpos($sCode, 'endif]-->', $p1);
        $tmp = substr($sCode, $p1, (($p2 - $p1) + 9));
        # Find all style link tags with rel="stylesheet"
        $p3 = strpos($tmp, '<link');
        while ($p3 !== false) {
            $p4 = strpos($tmp, '>', $p3);
            $tmp2 = substr($tmp, $p3, (($p4 - $p3) + 1));
            if ((strpos(strtolower($tmp2), 'rel="stylesheet"')) || (strpos(strtolower($tmp2), "rel='stylesheet'"))) {
                # Extract the path and filename
                $url = '';
                $p5 = strpos($tmp2, 'href=');
                if ($p5 !== false) { # shouldn't happen, an address is needed
                    $p5 += 6;
                    $char = substr($tmp2, ($p5 - 1), 1);
                    $p6 = strpos($tmp2, $char, $p5);
                    $url = substr($tmp2, $p5, ($p6 - $p5));
                    # Clean the url from unnessecary path information (just leave the folder name like "css/xxx.css")
                    $url = str_replace($cfgClient[$client]['path']['htmlpath'], '', $url);
                    $url = ((substr($url, 0, 1) == '/') ? substr($url, 1) : $url);
                    # Sort into array grouped by media type
                    $p5 = strpos($tmp2, 'media=');
                    if ($p5 !== false) {
                        $p5 += 7;
                        $char = substr($tmp2, ($p5 - 1), 1);
                        $p6 = strpos($tmp2, $char, $p5);
                        $media = substr($tmp2, $p5, ($p6 - $p5));
                        $IElinks[$c][$media][] = array('url' => $url, 'old' => $tmp2);
                    } else {
                        $IElinks[$c]['all'][] = array('url' => $url, 'old' => $tmp2);
                    }
                    $IEtmp[] = $tmp2;
                }
            }
            $p3 = strpos($tmp, '<link', $p4);
        }
        # Find all script link tags and script blocks
        $p3 = strpos($tmp, '<script');
        while ($p3 !== false) {
            $p4 = strpos($tmp, '>', $p3);
            # Check if this is included code, or code directly in the page
            if (substr($tmp, $p4, 10) == '></script>') {
                $tmp2 = substr($tmp, $p3, (($p4 - $p3) + 10));
                # Check if this script tag has a type or language set
                $p5 = strpos($tmp2, 'type=');
                if ($p5 !== false) {
                    $p5 += 6;
                    $char = substr($tmp2, ($p5 - 1), 1);
                    $p6 = strpos($tmp2, $char, $p5);
                    $type = substr($tmp2, $p5, ($p6 - $p5));
                    # If type isn't "text/javascript", leave the tag alone
                    if (strtolower($type) != 'text/javascript') {
                        continue;
                    }
                } else {
                    $p5 = strpos($tmp2, 'language=');
                    if ($p5 !== false) {
                        $p5 += 10;
                        $char = substr($tmp2, ($p5 - 1), 1);
                        $p6 = strpos($tmp2, $char, $p5);
                        $lang = substr($tmp2, $p5, ($p6 - $p5));
                        # If language isn't "javascript", leave the tag alone
                        if (strtolower($lang) != 'javascript') {
                            continue;
                        }
                    }
                }
                # We now definitely have a javascript link tag, save it in an array
                $url = '';
                $p5 = strpos($tmp2, 'src=');
                if ($p5 !== false) { # shouldn't happen, an address is needed
                    $p5 += 5;
                    $char = substr($tmp2, ($p5 - 1), 1);
                    $p6 = strpos($tmp2, $char, $p5);
                    $url = substr($tmp2, $p5, ($p6 - $p5));
                    # Clean the url from unnessecary path information (just leave the folder name like "js/xxx.js")
                    $url = str_replace($cfgClient[$client]['path']['htmlpath'], '', $url);
                    $url = ((substr($url, 0, 1) == '/') ? substr($url, 1) : $url);
                }
                # Exclude files with complete URLs or parameters from the compressor
                $compress = (((substr($url, 0, 7) == 'http://') || (substr($url, 0, 8) == 'https://') || (strpos($url, '?') !== false)) ? false : true);
                $IEscripts[$c][] = array('url' => $url, 'compress' => $compress, 'old' => $tmp2);
                $IEtmp[] = $tmp2;
            } else {
                # Script block
                $p4 = strpos($tmp, '</script>', $p3);
                $tmp2 = substr($tmp, $p3, (($p4 - $p3) + 9));
                $IEscriptblocks[$c][] = $tmp2;
                $IEtmp[] = $tmp2;
            }
            $p3 = strpos($tmp, '<script', $p4);
        }
        
        # Find all style blocks
        $p3 = strpos($tmp, '<style');
        while ($p3 !== false) {
            $p4 = strpos($tmp, '</style>', $p3);
            $tmp2 = substr($tmp, $p3, (($p4 - $p3) + 8));
            $IEstyles[$c][] = $tmp2;
            $IEtmp[] = $tmp2;
            $p3 = strpos($tmp, '<style', $p4);
        }
        
        # Now that we found all links and blocks in this IE conditional comment, we delete them from it and see what's left
        $tmp2 = $tmp;
        for ($i = 0, $n = count($IEtmp); $i < $n; $i ++) {
            $tmp2 = str_replace($IEtmp[$i], '', $tmp2);
        }
        # Remove line breaks and tabs
        $tmp2 = str_replace(array("\r\n", "\r", "\n", "\t"), '', $tmp2);
        # Remove spaces
        while (strpos($tmp2, ' ') !== false) {
            $tmp2 = str_replace(' ', '', $tmp2);
        }
        # Now check the length of the resultung string
        if (strlen($tmp2) <= (strlen($c) + 16)) { # (start tag + end tag)
            # The block can be taken out from it's original position
            $IE[] = $tmp;
        } else {
            # We must take all found links and blocks out of this IE conditional comment
            for ($i = 0, $n = count($IEtmp); $i < $n; $i ++) {
                $IE[] = $IEtmp[$i];
            }
        }
        # Move on to the next IE conditional comment block
        $p1 = strpos($sCode, '<!--[if', $p2);
    }
    
    # Delete IE conditional comments and IE conditional comment entries marked as deletable
    for ($i = 0, $n = count($IE); $i < $n; $i ++) {
        $sCode = str_replace($IE[$i], '', $sCode);
    }
    
    # Find all link tags with rel="stylesheet", grouped by their media= parameter ({screen|all|print|...})
    $stylesheets = array();
    $p1 = strpos($sCode, '<link');
    while ($p1 !== false) {
        $p2 = strpos($sCode, '>', $p1);
        $tmp = substr($sCode, $p1, (($p2 - $p1) + 1));
        if ((strpos(strtolower($tmp), 'rel="stylesheet"')) || (strpos(strtolower($tmp), "rel='stylesheet'"))) {
            # Extract the path and filename
            $url = '';
            $p3 = strpos($tmp, 'href=');
            if ($p3 !== false) { # shouldn't happen, an address is needed
                $p3 += 6;
                $char = substr($tmp, ($p3 - 1), 1);
                $p4 = strpos($tmp, $char, $p3);
                $url = substr($tmp, $p3, ($p4 - $p3));
                # Clean the url from unnessecary path information (just leave the folder name like "css/xxx.css")
                $url = str_replace($cfgClient[$client]['path']['htmlpath'], '', $url);
                $url = ((substr($url, 0, 1) == '/') ? substr($url, 1) : $url);
            }
            # Exclude files with complete URLs or parameters
            if ((substr($url, 0, 7) != 'http://') && (substr($url, 0, 8) != 'https://') && (strpos($url, '?') === false)) {
                # Sort into array grouped by media type
                $p3 = strpos($tmp, 'media=');
                if ($p3 !== false) {
                    $p3 += 7;
                    $char = substr($tmp, ($p3 - 1), 1);
                    $p4 = strpos($tmp, $char, $p3);
                    $media = substr($tmp, $p3, ($p4 - $p3));
                    $stylesheets[$media][] = array('url' => $url, 'old' => $tmp);
                } else {
                    $stylesheets['all'][] = array('url' => $url, 'old' => $tmp);
                }
            }
        }
        $p1 = strpos($sCode, '<link', $p2);
    }
    
    # Find all style blocks
    $styles = array();
    $p1 = strpos($sCode, '<style');
    while ($p1 !== false) {
        $p2 = strpos($sCode, '</style>', $p1);
        $tmp = substr($sCode, $p1, (($p2 - $p1) + 8));
        $styles[] = $tmp;
        $p1 = strpos($sCode, '<style', $p2);
    }
    
    # Rebuild the code for the non-IE-commented stylesheets
    foreach ($stylesheets as $media => $sheets) {
        # Correct or compress?
        if ($switch == 'correct') {
            for ($i = 0, $n = count($sheets); $i < $n; $i ++) {
                # Remove the old links for this media type
                if (strlen($sheets[$i]['url'])) {
                    $sCode = str_replace($sheets[$i]['old'], '', $sCode);
                }
                # Add the links to the end of the head section
                $p1 = strpos($sCode, '</head>');
                if (strlen($sheets[$i]['url'])) {
                    $sCode = substr($sCode, 0, $p1) . '<link rel="stylesheet" type="text/css" media="' . $media . '" href="' . $sheets[$i]['url'] . '" />' . "\n" . substr($sCode, $p1);
                }
            }
        } else {
            # Call the compressor class for each media type and replace the old links with the single new one
            $files = array();
            for ($i = 0, $n = count($sheets); $i < $n; $i ++) {
                if (strlen($sheets[$i]['url'])) {
                    # Find and extract included stylesheet files
                    if (is_file($cfgClient[$client]['path']['frontend'] . $sheets[$i]['url'])) {
                        $content = @file_get_contents($cfgClient[$client]['path']['frontend'] . $sheets[$i]['url']);
                        if (strrpos($sheets[$i]['url'], '/') > 0) {
                            $path = substr($sheets[$i]['url'], 0, (strrpos($sheets[$i]['url'], '/') + 1));
                        } else {
                            $path = '';
                        }
                        $p1 = strpos(strtolower($content), '@import url(');
                        while ($p1 !== false) {
                            $p2 = strpos($content, ');', $p1);
                            $url = str_replace(array('"', "'"), '', substr($content, ($p1 + 12), ($p2 - $p1 - 12)));
                            # Clean the url from unnessecary path information (just leave the folder name like "css/xxx.css")
                            $url = str_replace($cfgClient[$client]['path']['htmlpath'], '', $url);
                            $url = ((substr($url, 0, 1) == '/') ? substr($url, 1) : $url);
                            # Exclude files with complete URLs or parameters
                            if ((substr($url, 0, 7) != 'http://') && (substr($url, 0, 8) != 'https://') && (strpos($url, '?') === false)) {
                                $files[] = $path . $url;
                            } else {
                                # Files with absolute paths must be placed in the HTML head section
                                $p1 = strpos($sCode, '</head>');
                                $sCode = substr($sCode, 0, $p1) . '<link rel="stylesheet" type="text/css" media="' . $media . '" href="' . $path . $url . '" />' . "\n" . substr($sCode, $p1);
                            }
                            $content = substr($content, 0, $p1) . substr($content, ($p2 + 2));
                            $p1 = strpos(strtolower($content), '@import url(');
                        }
                        # Is there any content left in this file?
                        if (strlen(trim(str_replace(array("\r", "\n"), '', $content)))) {
                            # Yes, add it to the compressor, which will include it's remaining content in the compressed file
                            $files[] = $sheets[$i]['url'];
                        }
                    }
                }
            }
            if (count($files)) {
                $compressed = Output_Compressor::generate($cfgClient[$client]['path']['frontend'] . 'cache/', $files, 'css', $cfgClient[$client]['path']['htmlpath']);
                # Add the compressed file link to the end of the head section
                $p1 = strpos($sCode, '</head>');
                $sCode = substr($sCode, 0, $p1) . '<link rel="stylesheet" type="text/css" media="' . $media . '" href="front_content.php?action=get_compressed&amp;f=' . $compressed . '&amp;c=css" />' . "\n" . substr($sCode, $p1);
            }
            # Remove the old links for this media type
            for ($i = 0, $n = count($sheets); $i < $n; $i ++) {
                if (strlen($sheets[$i]['url'])) {
                    $sCode = str_replace($sheets[$i]['old'], '', $sCode);
                }
            }
        }
    }
    unset($stylesheets);
    unset($media);
    unset($sheets);
    
    # Rebuild the code for the IE-commented stylesheets
    if (count($IElinks)) {
        foreach ($IElinks as $condition => $links) {
            # Add the conditional comment start tag to the end of the head section
            $p1 = strpos($sCode, '</head>');
            $sCode = substr($sCode, 0, $p1) . $condition . "\n" . substr($sCode, $p1);
            # Put the links there
            foreach ($links as $media => $sheets) {
                # Correct or compress?
                if ($switch == 'correct') {
                    for ($i = 0, $n = count($sheets); $i < $n; $i ++) {
                        # Remove the old links for this media type
                        if (strlen($sheets[$i]['url'])) {
                            $sCode = str_replace($sheets[$i]['old'], '', $sCode);
                        }
                        # Add the links to the end of the head section
                        $p1 = strpos($sCode, '</head>');
                        if (strlen($sheets[$i]['url'])) {
                            $sCode = substr($sCode, 0, $p1) . '<link rel="stylesheet" type="text/css" media="' . $media . '" href="' . $sheets[$i]['url'] . '" />' . "\n" . substr($sCode, $p1);
                        }
                    }
                } else {
                    # Call the compressor class for each media type and replace the old links with the single new one
                    $files = array();
                    for ($i = 0, $n = count($sheets); $i < $n; $i ++) {
                        if (strlen($sheets[$i]['url'])) {
                            # Find and extract included stylesheet files
                            $content = @file_get_contents($cfgClient[$client]['path']['frontend'] . $sheets[$i]['url']);
                            if (strrpos($sheets[$i]['url'], '/') > 0) {
                                $path = substr($sheets[$i]['url'], 0, (strrpos($sheets[$i]['url'], '/') + 1));
                            } else {
                                $path = '';
                            }
                            $p1 = strpos(strtolower($content), '@import url(');
                            while ($p1 !== false) {
                                $p2 = strpos($content, ');', $p1);
                                $url = str_replace(array('"', "'"), '', substr($content, ($p1 + 12), ($p2 - $p1 - 12)));
                                # Clean the url from unnessecary path information (just leave the folder name like "css/xxx.css")
                                $url = str_replace($cfgClient[$client]['path']['htmlpath'], '', $url);
                                $url = ((substr($url, 0, 1) == '/') ? substr($url, 1) : $url);
                                # Exclude files with complete URLs or parameters
                                if ((substr($url, 0, 7) != 'http://') && (substr($url, 0, 8) != 'https://') && (strpos($url, '?') === false)) {
                                    $files[] = $path . $url;
                                } else {
                                    # Files with absolute paths must be placed in the HTML head section
                                    $p1 = strpos($sCode, '</head>');
                                    $sCode = substr($sCode, 0, $p1) . '<link rel="stylesheet" type="text/css" media="' . $media . '" href="' . $path . $url . '" />' . "\n" . substr($sCode, $p1);
                                }
                                $content = substr($content, 0, $p1) . substr($content, ($p2 + 2));
                                $p1 = strpos(strtolower($content), '@import url(');
                            }
                            # Is there any content left in this file?
                            if (strlen(trim(str_replace(array("\r", "\n"), '', $content)))) {
                                # Yes, add it to the compressor, which will include it's remaining content in the compressed file
                                $files[] = $sheets[$i]['url'];
                            }
                        }
                    }
                    # Make the paths absolute for the compressor
                    for ($i = 0, $n = count($files); $i < $n; $i ++) {
                        $files[$i] = $cfgClient[$client]['path']['frontend'] . $files[$i];
                    }
                    # Compress the files into a single one
                    $compressed = Output_Compressor::generate($cfgClient[$client]['path']['frontend'] . 'cache/', $files, 'css', $cfgClient[$client]['path']['htmlpath']);
                    if ($compressed) {
                        # Add the compressed file link to the end of the head section
                        $p1 = strpos($sCode, '</head>');
                        $sCode = substr($sCode, 0, $p1) . '<link rel="stylesheet" type="text/css" media="' . $media . '" href="front_content.php?action=get_compressed&amp;f=' . $compressed . '&amp;c=css" />' . "\n" . substr($sCode, $p1);
                    }
                    # Remove the old links for this media type
                    for ($i = 0, $n = count($sheets); $i < $n; $i ++) {
                        if (strlen($sheets[$i]['url'])) {
                            $sCode = str_replace($sheets[$i]['old'], '', $sCode);
                        }
                    }
                }
            }
            # Add the conditional comment end tag to the end of the head section
            $p1 = strpos($sCode, '</head>');
            $sCode = substr($sCode, 0, $p1) . ((substr($condition, -5) == '<!-->') ? '<!--' : '') . '<![endif]-->' . "\n" . substr($sCode, $p1);
        }
    }
    unset($IElinks);
    unset($condition);
    unset($links);
    unset($media);
    unset($sheets);
    unset($files);
    
    # Rebuild the code for the style blocks
    if (count($styles)) {
        for ($i = 0, $n = count($styles); $i < $n; $i ++) {
            $sCode = str_replace($styles[$i], '', $sCode);
            $p1 = strpos($sCode, '</head>');
            $sCode = substr($sCode, 0, $p1) . $styles[$i] . "\n" . substr($sCode, $p1);
        }
    }
    unset($styles);
    
    # Rebuild the code for the IE-commented style blocks
    if (count($IEstyles)) {
        foreach ($IEstyles as $condition => $styles) {
            # Add the conditional comment start tag to the end of the head section
            $p1 = strpos($sCode, '</head>');
            $sCode = substr($sCode, 0, $p1) . $condition . "\n" . substr($sCode, $p1);
            # Put the style blocks there
            for ($i = 0, $n = count($styles); $i < $n; $i ++) {
                $p1 = strpos($sCode, '</head>');
                $sCode = substr($sCode, 0, $p1) . $styles[$i] . "\n" . substr($sCode, $p1);
            }
            # Add the conditional comment end tag to the end of the head section
            $p1 = strpos($sCode, '</head>');
            $sCode = substr($sCode, 0, $p1) . '<![endif]-->' . "\n" . substr($sCode, $p1);
        }
    }
    unset($IEstyles);
    unset($condition);
    unset($styles);
    
    # Find all script link tags and script blocks
    $scripts = array();
    $scriptblocks = array();
    $p1 = strpos($sCode, '<script');
    while ($p1 !== false) {
        $p2 = strpos($sCode, '>', $p1);
        # Check if this is included code, or code directly in the page
        if (substr($sCode, $p2, 10) == '></script>') {
            # Script link
            $tmp = substr($sCode, $p1, (($p2 - $p1) + 10));
            # Check if this script tag has a type or language set
            $p3 = strpos($tmp, 'type=');
            if ($p3 !== false) {
                $p3 += 6;
                $char = substr($tmp, ($p3 - 1), 1);
                $p4 = strpos($tmp, $char, $p3);
                $type = substr($tmp, $p3, ($p4 - $p3));
                # If type isn't "text/javascript", leave the tag alone
                if (strtolower($type) != 'text/javascript') {
                    $p1 = strpos($sCode, '<script', $p2);
                    continue;
                }
            } else {
                $p3 = strpos($tmp, 'language=');
                if ($p3 !== false) {
                    $p3 += 10;
                    $char = substr($tmp, ($p3 - 1), 1);
                    $p4 = strpos($tmp, $char, $p3);
                    $lang = substr($tmp, $p3, ($p4 - $p3));
                    # If language isn't "javascript", leave the tag alone
                    if (strtolower($lang) != 'javascript') {
                        $p1 = strpos($sCode, '<script', $p2);
                        continue;
                    }
                }
            }
            # We now definitely have a javascript link tag, save it in an array
            $url = '';
            $p3 = strpos($tmp, 'src=');
            if ($p3 !== false) { # shouldn't happen, an address is needed
                $p3 += 5;
                $char = substr($tmp, ($p3 - 1), 1);
                $p4 = strpos($tmp, $char, $p3);
                $url = substr($tmp, $p3, ($p4 - $p3));
                # Clean the url from unnessecary path information (just leave the folder name like "js/xxx.js")
                $url = str_replace($cfgClient[$client]['path']['htmlpath'], '', $url);
                $url = ((substr($url, 0, 1) == '/') ? substr($url, 1) : $url);
            }
            # Exclude files with complete URLs or parameters from the compressor
            $compress = (((substr($url, 0, 7) == 'http://') || (substr($url, 0, 8) == 'https://') || (strpos($url, '?') !== false)) ? false : true);
            $scripts[] = array('url' => $url, 'compress' => $compress, 'old' => $tmp);
        } else {
            # Script block
            $p2 = strpos($sCode, '</script>', $p1);
            $tmp = substr($sCode, $p1, (($p2 - $p1) + 9));
            $scriptblocks[] = $tmp;
            $p1 = strpos($sCode, '<script', $p2);
        }
        $p1 = strpos($sCode, '<script', $p2);
    }
    
    # Delete the old script links from the page code
    if (count($scripts)) {
        for ($i = 0, $n = count($scripts); $i < $n; $i ++) {
            # Remove the old link
            $sCode = str_replace($scripts[$i]['old'], '', $sCode);
        }
    }
    
    # Delete the old script blocks from the page code
    if (count($scriptblocks)) {
        for ($i = 0, $n = count($scriptblocks); $i < $n; $i ++) {
            # Remove the old script block
            $sCode = str_replace($scriptblocks[$i], '', $sCode);
        }
    }
    
    # Call the compressor class and replace the old script links with the single new one
    $files = array();
    if (count($scripts)) {
        for ($i = 0, $n = count($scripts); $i < $n; $i ++) {
            # Correct or compress?
            if (($switch == 'correct') || ($scripts[$i]['compress'] == false)) {
                # First, if we have files in our array, compress them and output them as a single file (needed to presave the loading order)
                if (count($files)) {
                    $compressed = Output_Compressor::generate($cfgClient[$client]['path']['frontend'] . 'cache/', $files, 'js', $cfgClient[$client]['path']['htmlpath']);
                    $tmp = '<script type="text/javascript" src="front_content.php?action=get_compressed&amp;f=' . $compressed . '&amp;c=javascript"></script>';
                    # Add the link to the end of the body section
                    $p1 = strpos($sCode, '</body>');
                    $sCode = substr($sCode, 0, $p1) . $tmp . "\n" . substr($sCode, $p1);
                    $files = array();
                }
                # Create the link code
                $tmp = '<script type="text/javascript" src="' . $scripts[$i]['url'] . '"></script>';
                # Add the link to the end of the body section
                $p1 = strpos($sCode, '</body>');
                $sCode = substr($sCode, 0, $p1) . $tmp . "\n" . substr($sCode, $p1);
            } else {
                $files[] = $cfgClient[$client]['path']['frontend'] . $scripts[$i]['url'];
            }
        }
        if (count($files)) {
            # Compress the files into one single file
            $compressed = Output_Compressor::generate($cfgClient[$client]['path']['frontend'] . 'cache/', $files, 'js', $cfgClient[$client]['path']['htmlpath']);
            $tmp = '<script type="text/javascript" src="front_content.php?action=get_compressed&amp;f=' . $compressed . '&amp;c=javascript"></script>';
            # Add the link to the end of the body section
            $p1 = strpos($sCode, '</body>');
            $sCode = substr($sCode, 0, $p1) . $tmp . "\n" . substr($sCode, $p1);
        }
    }
    unset($scripts);
    unset($type);
    unset($lang);
    unset($files);
    
    # Rebuild the code for the script blocks
    if (count($scriptblocks)) {
        for ($i = 0, $n = count($scriptblocks); $i < $n; $i ++) {
            $sCode = str_replace($scriptblocks[$i], '', $sCode);
            $p1 = strpos($sCode, '</body>');
            $sCode = substr($sCode, 0, $p1) . $scriptblocks[$i] . "\n" . substr($sCode, $p1);
        }
    }
    unset($scriptblocks);
    
    # Rebuild the code for the IE-commented script links
    if (count($IEscripts)) {
        foreach ($IEscripts as $condition => $links) {
            # Add the conditional comment start tag to the end of the body section
            $p1 = strpos($sCode, '</body>');
            $sCode = substr($sCode, 0, $p1) . $condition . "\n" . substr($sCode, $p1);
            # Put the links there
            # Call the compressor class and replace the old script links with the single new one
            $files = array();
            if (count($links)) {
                for ($i = 0, $n = count($links); $i < $n; $i ++) {
                    # Correct or compress?
                    if (($switch == 'correct') || ($links[$i]['compress'] == false)) {
                        # First, if we have files in our array, compress them and output them as a single file (needed to presave the loading order)
                        if (count($files)) {
                            $compressed = Output_Compressor::generate($cfgClient[$client]['path']['frontend'] . 'cache/', $files, 'js', $cfgClient[$client]['path']['htmlpath']);
                            $tmp = '<script type="text/javascript" src="front_content.php?action=get_compressed&amp;f=' . $compressed . '&amp;c=javascript"></script>';
                            # Add the link to the end of the body section
                            $p1 = strpos($sCode, '</body>');
                            $sCode = substr($sCode, 0, $p1) . $tmp . "\n" . substr($sCode, $p1);
                            $files = array();
                        }
                        # Create the link code
                        $tmp = '<script type="text/javascript" src="' . $links[$i]['url'] . '"></script>';
                        # Add the link to the end of the body section
                        $p1 = strpos($sCode, '</body>');
                        $sCode = substr($sCode, 0, $p1) . $tmp . "\n" . substr($sCode, $p1);
                    } else {
                        $files[] = $cfgClient[$client]['path']['frontend'] . $links[$i]['url'];
                    }
                }
                if (count($files)) {
                    # Compress the files into one single file
                    $compressed = Output_Compressor::generate($cfgClient[$client]['path']['frontend'] . 'cache/', $files, 'js', $cfgClient[$client]['path']['htmlpath']);
                    $tmp = '<script type="text/javascript" src="front_content.php?action=get_compressed&amp;f=' . $compressed . '&amp;c=javascript"></script>';
                    # Add the link to the end of the body section
                    $p1 = strpos($sCode, '</body>');
                    $sCode = substr($sCode, 0, $p1) . $tmp . "\n" . substr($sCode, $p1);
                }
            }
            # Add the conditional comment end tag to the end of the body section
            $p1 = strpos($sCode, '</body>');
            $sCode = substr($sCode, 0, $p1) . '<![endif]-->' . "\n" . substr($sCode, $p1);
        }
    }
    unset($IEscripts);
    unset($condition);
    unset($links);
    
    # Rebuild the code for the IE-commented script blocks
    if (count($IEscriptblocks)) {
        foreach ($IEscriptblocks as $condition => $link) {
            # Add the conditional comment start tag to the end of the body section
            $p1 = strpos($sCode, '</body>');
            $sCode = substr($sCode, 0, $p1) . $condition . "\n" . $link . "\n" . '<![endif]-->' . "\n" . substr($sCode, $p1);
        }
    }
    unset($IEscriptblocks);
    unset($condition);
    unset($link);
    
    # Convert non unix type line breaks to unix type line breaks
    $sCode = str_replace(array("\r\n", "\r"), "\n", $sCode);
    
    if ($switch == 'full') {
        # Remove preceding and trailing spaces from each line except in <textarea> controls
        $aCnt = explode("\n", $sCode);
        $bTextarea = false;
        for ($i = 0, $n = count($aCnt); $i < $n; $i ++) {
            if (!$bTextarea) {
                $aCnt[$i] = trim($aCnt[$i]);
            }
            if (strpos(strtolower($aCnt[$i]), '<textarea') !== false) {
                $bTextarea = true;
            }
            if (strpos(strtolower($aCnt[$i]), '</textarea>') !== false) {
                $bTextarea = false;
            }
        }
        $sCode = implode("\n", $aCnt);
        unset($aCnt);
    }
    
    # Remove empty conditional comment tags
    $p1 = strpos($sCode, '<!--[if');
    while ($p1 !== false) {
        # First get the exact start comment
        $p2 = strpos($sCode, '>', $p1);
        $c = substr($sCode, $p1, (($p2 - $p1) + 1));
        if (substr($sCode, ($p2 + 1), 5) == '<!-->') {
            $c .= '<!-->';
        }
        $p2 = strpos($sCode, 'endif]-->', $p1);
        $tmp = substr($sCode, $p1, (($p2 - $p1) + 9));
        # Remove line breaks and tabs
        $tmp2 = str_replace(array("\r\n", "\r", "\n", "\t"), '', $tmp);
        # Remove spaces
        while (strpos($tmp2, ' ') !== false) {
            $tmp2 = str_replace(' ', '', $tmp2);
        }
        # Now check the length of the resultung string
        if (strlen($tmp2) <= (strlen($c) + 16)) { # (start tag + end tag)
            # The block can be taken out from it's original position
            $sCode = str_replace($tmp, '', $sCode);
        }
        $p1 = strpos($sCode, '<!--[if', ($p1 + 1));
    }
    
    # Remove blank lines except in <textarea> controls
    $aLines = explode("\n", $sCode);
    $sCode = '';
    $bTextarea = false;
    for ($i = 0, $n = count($aLines); $i < $n; $i ++) {
        if ((strlen(trim($aLines[$i]))) || ($bTextarea)) {
            if (strpos(strtolower($aLines[$i]), '<textarea') !== false) {
                $bTextarea = true;
            }
            $sCode .= $aLines[$i] . "\n";
            if (strpos(strtolower($aLines[$i]), '</textarea>') !== false) {
                $bTextarea = false;
            }
        }
    }
    unset($aLines);
    
    # Do some more cleanup
    unset($tmp);
    unset($tmp2);
    unset($char);
    unset($url);
    unset($p1);
    unset($p2);
    unset($p3);
    unset($p4);
    unset($p5);
    
    # Compress the HTML code?
    if ((strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) && (extension_loaded('zlib')) && (!ini_get("zlib.output_compression"))) {
        header('Content-Encoding: gzip');
        $sCode = gzencode($sCode, 6, FORCE_GZIP);
    }
    
    # Return the new page code
    return $sCode;
}
?>