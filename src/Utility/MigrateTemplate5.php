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

namespace BcAddonMigrator\Utility;

use Cake\Log\LogTrait;
use Psr\Log\LogLevel;

/**
 * Class MigrateTemplate5
 */
class MigrateTemplate5
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
	public function migrate(string $path): void
	{
		$code = file_get_contents($path);
		$code = MigrateBasic5::replaceCode($code);
		$code = preg_replace('/\$this->BcForm->/', '$this->BcAdminForm->', $code);
		$code = preg_replace('/\$this->BcAdminForm->input\(/', '$this->BcAdminForm->control(', $code);
		file_put_contents($path, $code);
		$this->log('テンプレート：' . $path . 'を マイグレーションしました。', LogLevel::INFO);
	}
	
}	
