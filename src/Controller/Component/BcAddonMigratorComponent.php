<?php

namespace BcAddonMigrator\Controller\Component;

use Cake\Controller\Component;

/**
 * BcDbMigratorComponent
 */
class BcAddonMigratorComponent extends Component
{
	
	/**
	 * CakePHPのマイグレーターを実行
	 *
	 * @param string $plugin
	 * @param string $php
	 */
	public function migratePluginByCake($plugin, $php = 'php')
	{
		if (!$php) {
			$php = 'php';
		}
		
		$cake = ROOT . DS . 'lib' . DS . 'Cake' . DS . 'Console' . DS . 'cake.php';
		$command = 'upgrade all --plugin';
		
		// CakePHP UpgradeShell 実行
		ob_start();
		passthru($php . ' ' . $cake . ' ' . $command . ' ' . $plugin);
		$this->log(ob_get_clean(), 'migration');
	}
	
}
