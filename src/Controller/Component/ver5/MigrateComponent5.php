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
 * Class MigrateComponent5
 */
class MigrateComponent5
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
	public function migrate(string $plugin, string $path): void
	{
		$code = file_get_contents($path);
		$code = MigrateBasic5::addNameSpace($plugin, $path, 'Controller' . DS . 'Component', $code);
		$code = MigrateBasic5::replaceCode($code);
		$code = preg_replace('/extends Component/', 'extends \Cake\Controller\Component', $code);
		file_put_contents($path, $code);
		$this->log('コンポーネント：' . $path . ' をマイグレーションしました。', LogLevel::INFO);
	}

}
