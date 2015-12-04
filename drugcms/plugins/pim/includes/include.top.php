<?php
/**
 * Project:
 * drugCMS Content Management System
 *
 * Description:
 * Plugin Manager
 *
 * Requirements:
 * @con_php_req 5.4
 *
 *
 * @package    drugCMS Backend plugins
 * @version    1.0
 * @author     René Mansveld
 * @copyright  Spider IT Deutschland
 * @license    http://www.drugcms.org/license/LICENCE.txt
 * @link       http://www.drugcms.de
 * @link       http://www.spider-it.de
 * @since      file available since drugCMS release 2.1.0
 *
 * {@internal
 *   created  2015-11-23
 * }}
 *
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

$plugin_name = "pim";

plugin_include($plugin_name, 'includes/config.plugin.php');

if (!$perm->have_perm_area_action($plugin_name, $plugin_name)) {
    exit;
}

$oTpl = new Template();

$sContent = '<div id="navcontainer" style="padding-left: 250px;">
    <div class="frame_handle" style="display: none; float: left;">
        <img style="border: 0px; float: left;" src="images/frame_handle_re.gif">
        <img id="toggleimage" border="0" src="images/spacer.gif" onclick="parent.parent.frameResize.toggle()" style="margin: 7px 0px 0px 7px;">
    </div>
    <ul id="navlist">';
if ($perm->have_perm_area_action($plugin_name, 'plugins')) {
    $sContent .= '
        <li id="pim_0" class="item">
            <a' . (($perm->have_perm_area_action($plugin_name, 'plugins')) ? ' class="current"' : '') . ' href="main.php?area=pim&amp;what=plugins&amp;frame=4&amp;contenido=' . $contenido . '" target="right_bottom" onclick="sub.clicked(this)">' . i18n("Plugins", $plugin_name) . '</a>
        </li>';
}
if ($perm->have_perm_area_action($plugin_name, 'repositories')) {
    $sContent .= '
        <li id="pim_1" class="item">
            <a' . ((!$perm->have_perm_area_action($plugin_name, 'plugins')) ? ' class="current"' : '') . ' href="main.php?area=pim&amp;what=repositories&amp;frame=4&amp;contenido=' . $contenido . '" target="right_bottom" onclick="sub.clicked(this)">' . i18n("Repositories", $plugin_name) . '</a>
        </li>';
}
$sContent .= '
    </ul>
</div>';

$oTpl->set('s', 'CONTENT', $sContent);
$oTpl->generate($cfg['path']['contenido'] . $cfg['path']['plugins'] . $plugin_name . '/templates/default/page.html');
?>