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
 * Class MigrateConfig5
 */
class MigrateConfig5
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
		$code = MigrateBasic5::replaceCode($code, $is5);
		$code = $this->convertToReturnArray($code);
		file_put_contents($path, $code);
		$this->log('コンフィグ：' . $path . ' をマイグレーションしました。', LogLevel::INFO, 'migrate_addon');
	}

	/**
	 * $config への代入形式の設定ファイルを、配列を return する形式に変換する
	 *
	 * baserCMS 4 の設定ファイルは `$config['Key'] = [...];` のように変数へ代入する形式だが、
	 * baserCMS 5（CakePHP 5）の Configure::load() は配列を return することを要求し、
	 * さもなければ「did not return an array」で致命的エラーになる。
	 *
	 * `$config['A']['B'] = ...` のような多段キーや `'BcApp.adminNavigation'` のような
	 * ドット記法キーを配列リテラルへ機械的に畳み込むのは誤変換の危険が高いため、
	 * 代入はそのまま残し、末尾に `return $config;` を追加する方式を採る。
	 *
	 * @param string $code
	 * @return string
	 */
	protected function convertToReturnArray(string $code): string
	{
		// $config を使っていない設定ファイル（routes.php / init.php 等）は対象外
		if (!preg_match('/\$config\s*(\[|=)/', $code)) {
			return $code;
		}
		// 既に return がある場合は何もしない
		foreach(token_get_all($code) as $token) {
			if (is_array($token) && $token[0] === T_RETURN) {
				return $code;
			}
		}
		// 閉じタグがあれば取り除いてから追記する
		$code = preg_replace('/\?>\s*$/', '', rtrim($code));
		return $code . "\n\nreturn \$config ?? [];\n";
	}

}
