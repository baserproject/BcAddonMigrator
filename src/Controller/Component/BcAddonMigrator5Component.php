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

namespace BcAddonMigrator\Controller\Component;

use BcAddonMigrator\Utility\MigrateController5;
use Cake\Controller\Component;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;

/**
 * BcAddonMigrator4Component
 */
class BcAddonMigrator5Component extends Component implements BcAddonMigratorInterface
{
	
	/**
	 * プラグイン用メッセージ
	 * @var array
	 */
	public $__pluginMessage = [
		'コントローラーにおいて、$this->Session->setFlash() 利用できなくなりました。 $this->Flash->set() または、<br>$this->setMessage(\'メッセージ\', 警告メッセージかどうか：true Or false, DBにログとして保存するかどうか：true Or false, フラッシュメッセージかどうか：true Or false) を利用してください。'
	];
	
	/**
	 * テーマ用メッセージ
	 *
	 * @var array
	 */
	public $__themeMessage = [
		'ビューにおいて、$this->BcForm->create() の第２引数で、url キーの配列の中以外で action キーは利用できなくなりました。action キーは、url キーの配列の中に指定しなおしてください。（例：$this->BcForm->create(\'ModelName\', [\'url\' => [\'action\' => \'action-name\']]）',
		'jQuery2系の利用に伴い、チェックボックスの操作において、attr(\'checked\') が利用できなくなりました。prop(\'checked\') を利用してください。',
		'コントローラーのアクション名が index の場合、コントローラー名だけでアクセスできなくなりました。リンクを設定する場合、末尾にスラッシュを付与したURLに書き換えてください。'
	];
	
	/**
	 * プラグイン用メッセージを取得する
	 *
	 * @return array
	 */
	public function getPluginMessage(): array
	{
		return array_merge($this->__pluginMessage, $this->__themeMessage);
	}
	
	/**
	 * テーマ用メッセージを取得する
	 *
	 * @return array
	 */
	public function getThemeMessage(): array
	{
		return $this->__themeMessage;
	}
	
	/**
	 * プラグインのマイグレーションを実行
	 *
	 * @param string $plugin プラグイン名
	 * @param string $php phpの実行ファイルのパス
	 */
	public function migratePlugin(string $plugin): void
	{
		$plugin = $this->migrateBasicDir($plugin);
		$this->makePluginClass($plugin);
		$this->migrateStructure($plugin);
		
		$pluginPath = BASER_PLUGINS . $plugin . DS . 'src' . DS;
		
		$this->migrateController($plugin, $pluginPath . 'Controller');
//		$this->migrateComponent($pluginPath . 'Controller' . DS . 'Component');
//		$this->migrateModel($pluginPath . 'Model');
//		$this->migrateBehavior($pluginPath . 'Model' . DS . 'Behavior');
//		$this->migratePluginConfig($pluginPath . 'Config');
//		$this->migrateHelper($pluginPath . 'View' . DS . 'Helper');
//		$this->migrateView($pluginPath . 'View');
	}
	
	/**
	 * テーマのマイグレーションを実行する
	 *
	 * @param string $theme テーマ名
	 */
	public function migrateTheme(string $theme): void
	{
		return;
		$theme = $this->migrateBasicDir($theme);
		$this->makePluginClass($plugin);
		$this->migrateStructure($theme);
		$themePath = WWW_ROOT . 'theme' . DS . $theme;
		$this->migrateHelper($themePath . DS . 'Helper');
		$this->migrateView($themePath);
	}
	
	/**
	 * アドオンを基本的なフォルダ構成にする
	 * @param string $plugin
	 * @return string
	 */
	public function migrateBasicDir(string $plugin): string
	{
		$newName = \Cake\Utility\Inflector::camelize($plugin);
		if ($plugin !== $newName) {
			rename(APP . 'Plugin' . DS . $plugin, APP . 'Plugin' . DS . $newName);
		}
		$pluginPath = BASER_PLUGINS . $plugin . DS;
		if (!is_dir($pluginPath . 'src')) (new \Cake\Filesystem\Folder())->create($pluginPath . 'src');
		if (is_dir($pluginPath . 'Test')) rename($pluginPath . 'Test', $pluginPath . 'tests');
		if (is_dir($pluginPath . 'tests' . DS . 'Case')) rename($pluginPath . 'tests' . DS . 'Case', $pluginPath . 'tests' . DS . 'TestCase');
		return $newName;
	}
	
	/**
	 * プラグインクラスを作成する
	 * @param string $plugin
	 * @return void
	 */
	public function makePluginClass(string $plugin)
	{
		$srcPath = BASER_PLUGINS . $plugin . DS . 'src';
		if (file_exists($srcPath . DS . 'Plugin.php')) return;
		(new \Cake\Filesystem\Folder())->create($srcPath);
		$file = new \Cake\Filesystem\File($srcPath . DS . 'Plugin.php');
		$file->write("<?php
namespace {$plugin};
use BaserCore\BcPlugin;
class Plugin extends BcPlugin {}");
	}
	
	/**
	 * プラグインの構造変更を実行
	 *
	 * @param string $plugin プラグイン名
	 * @param string $php phpの実行ファイルのパス
	 */
	public function migrateStructure(string $plugin)
	{
		$pluginPath = BASER_PLUGINS . $plugin . DS;
		if (is_dir($pluginPath . 'Config')) rename($pluginPath . 'Config', $pluginPath . 'Config');
		if (is_dir($pluginPath . 'View')) rename($pluginPath . 'View', $pluginPath . 'templates');
		foreach(['Controller', 'Model', 'Event', 'Lib', 'Vendor'] as $dir) {
			if (is_dir($pluginPath . $dir)) rename($pluginPath . $dir, $pluginPath . 'src' . DS . $dir);
		}
		if (is_dir($pluginPath . 'templates')) {
			$files = (new \Cake\Filesystem\Folder($pluginPath . 'templates'))->read();
			foreach($files[0] as $dir) {
				switch($dir) {
					case 'Elements':
						rename($pluginPath . 'templates' . DS . $dir, $pluginPath . 'templates' . DS . 'element');
						$this->moveAdminTemplates($plugin, 'element');
						break;
					case 'Layouts':
						rename($pluginPath . 'templates' . DS . $dir, $pluginPath . 'templates' . DS . 'layout');
						$this->moveAdminTemplates($plugin, 'layout');
						break;
					case 'Emails':
						rename($pluginPath . 'templates' . DS . $dir, $pluginPath . 'templates' . DS . 'email');
						$this->moveAdminTemplates($plugin, 'email');
						break;
					default:
						$this->moveAdminTemplates($plugin, $dir);
						break;
				}
			}
		}
	}
	
	/**
	 * 管理画面用のテンプレートを移動する
	 * @param string $plugin
	 * @param string $name
	 * @return void
	 */
	public function moveAdminTemplates(string $plugin, string $name)
	{
		$templatesPath = BASER_PLUGINS . $plugin . DS . 'templates' . DS;
		if (!is_dir($templatesPath . 'Admin')) {
			(new \Cake\Filesystem\Folder())->create($templatesPath . 'Admin');
		}
		$files = (new \Cake\Filesystem\Folder($templatesPath . $name))->read();
		foreach($files[0] as $dir) {
			if ($dir !== 'admin') continue;
			$adminPath = $templatesPath . $name . DS . $dir . DS;
			$files = (new \Cake\Filesystem\Folder($adminPath))->read();
			$files = $files[0] + $files[1];
			foreach($files as $file) {
				if (!is_dir($templatesPath . 'Admin' . DS . $name)) {
					(new \Cake\Filesystem\Folder())->create($templatesPath . 'Admin' . DS . $name);
				}
				rename($adminPath . $file, $templatesPath . 'Admin' . DS . $name . DS . $file);
			}
			(new \Cake\Filesystem\Folder($adminPath))->delete($adminPath);
		}
	}
	
	/**
	 * コントローラーファイルのマイグレーションを実行
	 *
	 * @param string $path コントローラーディレクトリへの実行パス
	 */
	public function migrateController(string $plugin, string $path)
	{
		// コントローラー書き換え
		if (!is_dir($path)) return;
		$Folder = new Folder($path);
		$files = $Folder->read(true, true, true);
		foreach($files[0] as $dir) {
			$this->migrateController($plugin, $dir);
		}
		foreach($files[1] as $file) {
			(new MigrateController5)->migrate(
				$plugin, 
				$this->getSubDir($plugin, $file), 
				$file
			);
		}
	}
	
	public function getSubDir($plugin, $path)
	{
		$path = dirname($path);
		$subDir = str_replace(BASER_PLUGINS . $plugin . DS . 'src' . DS . 'Controller', '', $path);
		$subDir = preg_replace('/^\//', '', $subDir);
		return $subDir;
	}
	
	/**
	 * コンポーネントファイルのマイグレーションを実行
	 *
	 * @param string $path コンポーネントディレクトリのパス
	 */
	public function migrateComponent($path)
	{
		
	}
	
	/**
	 * モデルファイルのマイグレーションを実行
	 *
	 * @param string $path モデルディレクトリのパス
	 */
	public function migrateModel($path)
	{
		// コントローラー書き換え
		if (is_dir($path)) {
			$Folder = new Folder($path);
			$files = $Folder->read(true, true, true);
			if (!empty($files[1])) {
				foreach($files[1] as $file) {
					$File = new File($file);
					$data = $File->read();
					$data = preg_replace('/extends\s+BcPluginAppModel/', 'extends AppModel', $data);
					$data = preg_replace('/\'notEmpty\'/', "'notBlank'", $data);
					$data = preg_replace('/public[\s\t]*?\$useDbConfig[\s\t]*?=[\s\t]*?\'plugin\'[\s\t]*?;/', "", $data);
					$File->write($data, 'w+', true);
					$File->close();
					$this->log('モデルファイル：' . basename($file) . 'を マイグレーションしました。', 'migration');
				}
			}
		}
	}
	
	/**
	 * ビヘイビアファイルのマイグレーションを実行
	 *
	 * @param string $path ビヘイビアディレクトリのパス
	 */
	public function migrateBehavior($path)
	{
		
	}
	
	/**
	 * プラグイン設定ファイルのマイグレーションを実行
	 *
	 * @param string $path 設定ファイルのパス
	 * @param string $plugin 古いプラグイン名
	 * @param string $newPlugin 新しいプラグイン名
	 */
	public function migratePluginConfig($path)
	{
		$file = $path . DS . 'init.php';
		$File = new File($file);
		$data = $File->read();
		$data = preg_replace('/\$this->Plugin->initDb\(\'plugin\'\,/', '$this->Plugin->initDb(', $data);
		$File->write($data, 'w+', true);
		$File->close();
		$this->log('init.php を マイグレーションしました。', 'migration');
	}
	
	/**
	 * ヘルパーファイルのマイグレーションを実行
	 *
	 * @param string $path ヘルパーディレクトリのパス
	 */
	public function migrateHelper($path)
	{
		
	}
	
	/**
	 * ビューファイルのマイグレーションを実行
	 *
	 * @param string $path ビューディレクトリのパス
	 * @param string $plugin 古いプラグイン名
	 * @param string $newPlugin 新しいプラグイン名
	 */
	public function migrateView($path)
	{
		
	}
	
}
