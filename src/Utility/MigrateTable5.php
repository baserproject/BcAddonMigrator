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
 * Class MigrateTable5
 */
class MigrateTable5
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
		$code = MigrateBasic5::addNameSpace($plugin, $path, 'Model' . DS . 'Table', $code);
		$code = MigrateBasic5::replaceCode($code);
		$code = self::setClassName($path, $code);
		$code = self::replaceEtc($code);
		file_put_contents($path, $code);
		$this->log('テーブル：' . $path . ' をマイグレーションしました。', LogLevel::INFO);
	}
	
	/**
	 * クラス名をファイル名に合わせる
	 * @param string $path
	 * @param string $code
	 * @return array|string|string[]|null
	 */
	public static function setClassName(string $path, string $code)
	{
		$className = basename($path, '.php');
		$code = preg_replace('/class\s+[a-zA-Z0-9]+\s/', "class $className ", $code);
		return $code;
	}
	
	/**
	 * その他の置き換え
	 * @param $code
	 * @return array|string|string[]|null
	 */
	public static function replaceEtc($code)
	{
		$code = preg_replace('/extends\s+AppModel/', 'extends \BaserCore\Model\Table\AppTable', $code);
		return $code;
	}
}	
