<?php
/**
 * Project: 
 * drugCMS Content Management System
 * 
 * Description: 
 * Include file for editiing content of type CMS_QRCODE
 * 
 * Requirements: 
 * @con_php_req 5.0
 * 
 *
 * @package    drugCMS Backend includes
 * @version    1.0.0
 * @author     Rene Mansveld
 * @copyright  Spider-IT Deutschland <www.spider-it.de>
 * @license    http://www.drugcms.org/license/LIZENZ.txt
 * @link       http://www.drugcms.org
 * @since      file available since drugCMS release 2.0
 * 
 * {@internal 
 *   created 2012-12-06
 *   modified 2012-12-20, Rene Mansveld
 *
 *   $Id$:
 * }}
 * 
 */

if(!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}

if ($doedit == "1") {
	conSaveContentEntry ($idartlang, "CMS_QRCODE", $typenr, $CMS_QRCODE);
	conMakeArticleIndex ($idartlang, $idart);
	conGenerateCodeForArtInAllCategories($idart);
	header("Location:".$sess->url($cfgClient[$client]["path"]["htmlpath"]."front_content.php?area=$tmp_area&idart=$idart&idcat=$idcat&lang=$lang&changeview=edit&client=$client")."");
}
header("Content-Type: text/html; charset={$encoding[$lang]}");
?>
<html>
<head>
<title></title>
    <link rel="stylesheet" type="text/css" href="<?php print $cfg["path"]["contenido_fullhtml"] . $cfg["path"]["styles"] ?>drugcms.css">
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $encoding[$lang] ?>">
</head>
<body>
<table width="100%"  border=0 cellspacing="0" cellpadding="0" bgcolor="#ffffff">
  <tr>
    <td width="10" rowspan="4"><img src="<?php print $cfg["path"]["contenido_fullhtml"].$cfg["path"]["images"] ?>spacer.gif" width="10" height="10"></td>
    <td width="100%"><img src="<?php print $cfg["path"]["contenido_fullhtml"].$cfg["path"]["images"] ?>spacer.gif" width="10" height="10"></td>
    <td width="10" rowspan="4"><img src="<?php print $cfg["path"]["contenido_fullhtml"].$cfg["path"]["images"] ?>spacer.gif" width="10" height="10"></td>
  </tr>
  <tr>
    <td>

<?php
       getAvailableContentTypes($idartlang);
        
        echo "  <FORM name=\"editcontent\" method=\"post\" action=\"".$cfg["path"]["contenido_fullhtml"].$cfg["path"]["includes"]."include.backendedit.php\">";
        $sess->hidden_session();
        echo "  <INPUT type=hidden name=lang value=\"$lang\">";
        echo "  <INPUT type=hidden name=typenr value=\"$typenr\">";
        echo "  <INPUT type=hidden name=idart value=\"$idart\">";
        echo "  <INPUT type=hidden name=action value=\"10\">";
        echo "  <INPUT type=hidden name=type value=\"$type\">";
        echo "<INPUT type=hidden name=doedit value=1>";        
        echo "  <INPUT type=hidden name=idcat value=\"$idcat\">";
        echo "  <INPUT type=hidden name=idartlang value=\"$idartlang\">";
        echo "<INPUT type=hidden name=changeview value=\"edit\">";
        echo "  <TABLE cellpadding=$cellpadding cellspacing=$cellpadding border=0>";
        echo "  <TR><TD valign=top class=text_medium>&nbsp;".$typenr.".&nbsp;".$a_description[$type][$typenr].":&nbsp;</TD><TD class=content>";
        echo "  <input type=text name=CMS_QRCODE value=\"".urldecode($a_content[$type][$typenr])."\" style=\"width: 400px;\">";
        echo "  </TD></TR>";
        $tmp_area = "con_editcontent";
        echo "  <TR valign=top><TD colspan=2><br>
                      <a href=".$sess->url($cfgClient[$client]["path"]["htmlpath"]."front_content.php?area=$tmp_area&idart=$idart&idcat=$idcat&lang=$lang")."><img src=\"".$cfg["path"]["contenido_fullhtml"].$cfg["path"]["images"]."but_cancel.gif\" border=0></a>
                      <INPUT type=image name=submit value=editcontent src=\"".$cfg["path"]["contenido_fullhtml"].$cfg["path"]["images"]."but_ok.gif\" border=0>
                      </TD></TR>";

        echo "  </TABLE>
                      </FORM>";

?>

</td></tr></table>
</body>
</HTML>