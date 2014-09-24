<?php
if(!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

$plugin_name = 'db_backup';
plugin_include($plugin_name, 'includes/config.plugin.php');

if ((!$perm->have_perm_area_action($plugin_name, $plugin_name)) && (!$cronjob)) {
    exit;
}

$sHTMLOutput  = '
    <div id="b" class="active">
        <a href="' . $sess->url('main.php?area=' . $plugin_name . '&amp;frame=4&amp;mode=0') . '" target="right_bottom" onclick="document.getElementById(\'b\').className=\'active\';document.getElementById(\'r\').className=\'\';">' . i18n("Backup", $plugin_name) . '</a>
    </div>
    <div  id="r">
        <a href="' . $sess->url('main.php?area=' . $plugin_name . '&amp;frame=4&amp;mode=1') . '" target="right_bottom" onclick="document.getElementById(\'b\').className=\'\';document.getElementById(\'r\').className=\'active\';">' . i18n("Restore", $plugin_name) . '</a>
    </div>';
$tpl->set('s', 'content', $sHTMLOutput);

// Navi nur ausgeben, wenn der Backupordner korrekt angelegt wurde
if (($pathfinder) && ($permissionfinder)) {
    $tpl->generate($cfg[$plugin_name]['templates']['navi']);
}
?>