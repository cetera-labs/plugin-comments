<?php
$a = \Cetera\Application::getInstance();
$i = $a->getConn()->fetchColumn("select count(*) from information_schema.statistics where table_name = 'comments' and index_name = 'material'");
if (!$i) $a->getConn()->executeQuery('ALTER TABLE `comments` ADD INDEX `material` (`material_type`, `material_id`);');