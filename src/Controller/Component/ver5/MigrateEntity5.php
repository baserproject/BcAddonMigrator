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
 * Class MigrateTable5
 */
class MigrateEntity5
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
	public function migrate(string $plugin, string $path, bool $is5): void
	{
	    if(in_array(basename($path), \Cake\Core\Configure::read('BcAddonMigrator.ignoreFiles'))) {
            return;
        }
		$code = file_get_contents($path);
        $code = preg_replace('/protected[^(\r\n)]+?\$_accessible =/', 'protected array $_accessible =', $code);
        $code = preg_replace('/protected[^(\r\n)]+?\$_hidden =/', 'protected array $_hidden =', $code);
		file_put_contents($path, $code);
		$this->log('エンティティ：' . $path . ' をマイグレーションしました。', LogLevel::INFO, 'migrate_addon');
	}

}
