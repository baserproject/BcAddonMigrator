<?php
/**
 * baserCMS :  Based Website Development Project <https://basercms.net>
 * Copyright (c) NPO baser foundation <https://baserfoundation.org/>
 *
 * @copyright     Copyright (c) NPO baser foundation
 * @link          https://basercms.net baserCMS Project
 * @since         5.0.7
 * @license       https://basercms.net/license/index.html MIT License
 */

namespace BcAddonMigrator\Controller\Component\ver5;

use Psr\Log\LogLevel;
use Cake\Log\LogTrait;

/**
 * Class MigrateView5
 */
class MigrateServiceProvider5
{

	/**
	 * Trait
	 */
	use LogTrait;

	/**
	 * マイグレーション
	 * @param string $plugin
	 * @param string $prefix
	 * @param string $path
	 * @return void
	 */
	public function migrate(string $path, bool $is5): void
	{
	    if(in_array(basename($path), \Cake\Core\Configure::read('BcAddonMigrator.ignoreFiles'))) {
            return;
        }
		$code = file_get_contents($path);
		if(!$is5) return;
        $code = preg_replace('/protected[^(\r\n)]+?\$provides =/', 'protected array $provides =', $code);
        $code = preg_replace('/public[^(\r\n)]+?\$provides =/', 'protected array $provides =', $code);
		file_put_contents($path, $code);
		$this->log('サービス：' . $path . ' をマイグレーションしました。', LogLevel::INFO, 'migrate_addon');
	}

}
