<?php
/**
 * Project: 
 * drugCMS Content Management System
 * 
 * Description: 
 * Chains for SEO Check
 * 
 * Requirements: 
 * @con_php_req 5.2
 * 
 *
 * @package    drugCMS Backend plugins
 * @version    1.0.0
 * @author     René Mansveld
 * @copyright  Spider IT Deutschland <www.spider-it.de>
 * @license    http://www.drugcms.org/license/LICENSE.txt
 * @link       http://www.spider-it.de
 * @link       http://www.drugcms.org
 * @since      file available since drugCMS release 2.0.5
 * 
 * {@internal 
 *   created 2015-04-05
 *
 *   $Id$:
 * }}
 * 
 */

if(!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}

function seocheck_installPlugin($db, $cfg) {
    
    # Find the next free x00 index
    $sql = 'SELECT MAX(idarea) AS last
            FROM ' . $cfg['tab']['area'];
    $db->query($sql);
    $db->next_record();
    $id = ((floor($db->f('last') / 100) + 5) * 100);
    
    # Insert our record in the _area table
    $sql = 'INSERT INTO ' . $cfg['tab']['area'] . ' (idarea, parent_id, name, relevant, online, menuless)
            VALUES (' . $id . ', "con", "con_seocheck", 1, 1, 0)';
    $db->query($sql);
    $sql = 'UPDATE ' . $cfg['tab']['sequence'] . '
            SET last_id = ' . $id . '
            WHERE (seq_name="' . $cfg['tab']['area'] . '")';
    $db->query($sql);
    
    # Insert our record in the _files table
    $sql = 'INSERT INTO ' . $cfg['tab']['files'] . ' (idfile, idarea, filename, filetype)
            VALUES (' . $id . ', ' . $id . ', "seocheck/includes/include.right_bottom.php", "main")';
    $db->query($sql);
    $sql = 'UPDATE ' . $cfg['tab']['sequence'] . '
            SET last_id = ' . $id . '
            WHERE (seq_name="' . $cfg['tab']['files'] . '")';
    $db->query($sql);
    
    # Insert our record in the _frame_files table
    $sql = 'INSERT INTO ' . $cfg['tab']['framefiles'] . ' (idframefile, idarea, idframe, idfile)
            VALUES (' . $id . ', ' . $id . ', 4, ' . $id . ')';
    $db->query($sql);
    $sql = 'UPDATE ' . $cfg['tab']['sequence'] . '
            SET last_id = ' . $id . '
            WHERE (seq_name="' . $cfg['tab']['framefiles'] . '")';
    $db->query($sql);
    
    # Insert our record in the _nav_sub table
    $sql = 'INSERT INTO ' . $cfg['tab']['nav_sub'] . ' (idnavs, idnavm, idarea, level, location, online)
            VALUES (' . $id . ', 0, ' . $id . ', 1, "seocheck/xml/;navigation/content/article/seocheck", 1)';
    $db->query($sql);
    $sql = 'UPDATE ' . $cfg['tab']['sequence'] . '
            SET last_id = ' . $id . '
            WHERE (seq_name="' . $cfg['tab']['nav_sub'] . '")';
    $db->query($sql);
}

?>