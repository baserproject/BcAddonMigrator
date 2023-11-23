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
	public function migrate(string $path): void
	{
		$code = file_get_contents($path);
		$code = MigrateBasic5::replaceCode($code);
//		$code = preg_replace('//', '', $code);
		file_put_contents($path, $code);
		$this->log('テーブル：' . $path . 'を マイグレーションしました。', LogLevel::INFO);
		
//		// コントローラー書き換え
//		if (is_dir($path)) {
//			$Folder = new Folder($path);
//			$files = $Folder->read(true, true, true);
//			if (!empty($files[1])) {
//				foreach($files[1] as $file) {
//					$File = new File($file);
//					$data = $File->read();
//					$data = preg_replace('/extends\s+BcPluginAppModel/', 'extends AppModel', $data);
//					$data = preg_replace('/\'notEmpty\'/', "'notBlank'", $data);
//					$data = preg_replace('/public[\s\t]*?\$useDbConfig[\s\t]*?=[\s\t]*?\'plugin\'[\s\t]*?;/', "", $data);
//					$File->write($data, 'w+', true);
//					$File->close();
//					$this->log('モデルファイル：' . basename($file) . 'を マイグレーションしました。', 'migration');
//				}
//			}
//		}
	}
	
}	
