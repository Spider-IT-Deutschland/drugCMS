UPDATE !PREFIX!_mod SET `input` = REPLACE(`input`, 'new template', 'new Template');
UPDATE !PREFIX!_mod SET `input` = REPLACE(`input`, 'new phpmailer', 'new PHPMailer');
UPDATE !PREFIX!_mod SET `input` = REPLACE(`input`, 'cInclude(\'classes\', \'class.template.php\')', '#cInclude(\'classes\', \'class.template.php\')');
UPDATE !PREFIX!_mod SET `input` = REPLACE(`input`, 'cInclude("classes", "class.template.php")', '#cInclude("classes", "class.template.php")');
UPDATE !PREFIX!_mod SET `output` = REPLACE(`output`, 'new template', 'new Template');
UPDATE !PREFIX!_mod SET `output` = REPLACE(`output`, 'new phpmailer', 'new PHPMailer');
UPDATE !PREFIX!_mod SET `output` = REPLACE(`output`, 'cInclude(\'classes\', \'class.template.php\')', '#cInclude(\'classes\', \'class.template.php\')');
UPDATE !PREFIX!_mod SET `output` = REPLACE(`output`, 'cInclude("classes", "class.template.php")', '#cInclude("classes", "class.template.php")');
