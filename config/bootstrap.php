<?php

use Cake\Log\Log;

Log::setConfig('migrate_addon', [
	'className' => 'File',
	'path' => LOGS,
	'file' => 'migrate_addon',
	'scopes' => ['migrate_addon'],
	'levels' => ['info', 'error']
]);

define('TMP_ADDON_MIGRATOR', TMP . 'addonmigrator' . DS);
