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
class MigrateView5
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
		if(!$is5) {
            $code = MigrateBasic5::addNameSpace($plugin, $path, 'View', $code);
            $code = preg_replace('/\$this->Session->/', '$this->getRequest()->getSession()->', $code);
        }
		$code = MigrateBasic5::replaceCode($code, $is5);
		file_put_contents($path, $code);
		$this->log('ビュー：' . $path . ' をマイグレーションしました。', LogLevel::INFO, 'migrate_addon');
	}

}
