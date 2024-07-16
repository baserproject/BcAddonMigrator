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

use BaserCore\Utility\BcFile;
use BcAddonMigrator\Controller\Component\ver5\MigrateEvent5;
use BcAddonMigrator\Controller\Component\ver5\MigrateBehavior5;
use BcAddonMigrator\Controller\Component\ver5\MigrateComponent5;
use BcAddonMigrator\Controller\Component\ver5\MigrateConfig5;
use BcAddonMigrator\Controller\Component\ver5\MigrateController5;
use BcAddonMigrator\Controller\Component\ver5\MigrateHelper5;
use BcAddonMigrator\Controller\Component\ver5\MigrateServiceProvider5;
use BcAddonMigrator\Controller\Component\ver5\MigrateTable5;
use BcAddonMigrator\Controller\Component\ver5\MigrateTemplate5;
use BcAddonMigrator\Controller\Component\ver5\MigrateView5;
use Laminas\Diactoros\UploadedFile;

/**
 * BcAddonMigrator4Component
 */
class BcAddonMigrator5Component extends BcAddonMigratorComponent implements BcAddonMigratorInterface
{

	/**
	 * プラグイン用メッセージ
	 * @var array
	 */
	public $__pluginMessage = [
	];

	/**
	 * テーマ用メッセージ
	 *
	 * @var array
	 */
	public $__themeMessage = [
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
	 * @param array $postData
     * @return bool|string
	 */
	public function migratePlugin(UploadedFile $file)
	{
	    $plugin = parent::setup($file);
	    if(!$plugin) return false;
	    $is5 = $is51 = false;
        if(file_exists(TMP_ADDON_MIGRATOR . $plugin . DS . 'src' . DS . 'Plugin.php')) {
            $is5 = true;
        } elseif(file_exists(TMP_ADDON_MIGRATOR . $plugin . DS . 'src' . DS . $plugin . 'Plugin.php')) {
            $is51 = true;
        }
        if($is51) return $plugin;

        $plugin = $this->migrateBasicDir($plugin, $is5);
        $this->makePluginClass($plugin, $is5);
        $this->migratePluginStructure($plugin, $is5);

        $pluginPath = TMP_ADDON_MIGRATOR . $plugin . DS;
        $srcPath = $pluginPath . 'src' . DS;

        $this->migrateAddonConfig($plugin, 'Plugin', $pluginPath . 'config.php', $is5);
        $this->migrateConfig($pluginPath . 'config', $is5);
        $this->migrateController($plugin, $srcPath . 'Controller', $is5);
        $this->migrateEvent($plugin, $srcPath . 'Event', $is5);
        $this->migrateComponent($plugin, $srcPath . 'Controller' . DS . 'Component', $is5);
        $this->migrateTable($plugin, $srcPath . 'Model' . DS . 'Table', $is5);
        $this->migrateBehavior($plugin, $srcPath . 'Model' . DS . 'Behavior', $is5);
        $this->migrateHelper($plugin, $srcPath . 'View' . DS . 'Helper', $is5);
        $this->migrateView($plugin, $srcPath . 'View', $is5);
        $this->migrateServiceProvider($srcPath . 'ServiceProvider', $is5);

        $templatePath = TMP_ADDON_MIGRATOR . $plugin . DS . 'templates' . DS;
        $this->migrateTemplate($templatePath, $is5);

		return $plugin;
	}

	/**
	 * プラグイン設定ファイルのマイグレーションを実行
	 * @param string $plugin
	 * @param string $type
	 * @param string $path
	 * @return void
	 */
	public function migrateAddonConfig(string $plugin, string $type, string $path, $is5)
	{
	    if($is5) return;
		if (!file_exists($path)) {
			$file = new \BaserCore\Utility\BcFile($path);
			$file->write("<?php
return [
	'type' => '{$type}',
	'title' => '{$plugin}',
	'description' => '',
	'author' => '',
	'url' => '',
];");
		} else {
			$config = include $path;
			if(is_array($config)) return;
			if(!isset($title)) $title = $plugin;
			if(!isset($description)) $description = '';
			if(!isset($author)) $author = '';
			if(!isset($url)) $url = '';
			if(!isset($adminLink)) $adminLink = [];
			if(!isset($installMessage)) $installMessage = '';
			if($adminLink) {
				if(!empty($adminLink['plugin'])) {
					$adminLink['plugin'] = \Cake\Utility\Inflector::camelize($adminLink['plugin']);
				}
				if(!empty($adminLink['controller'])) {
					$adminLink['controller'] = \Cake\Utility\Inflector::camelize($adminLink['controller']);
				}
				$adminLink = var_export($adminLink, true);
				$adminLink = str_replace('array (', '[', $adminLink);
				$adminLink = str_replace(')', ']', $adminLink);
				$adminLink = str_replace("\n", '', $adminLink);
			} else {
				$adminLink = '[]';
			}
			$file = new \BaserCore\Utility\BcFile($path);
			$file->write("<?php
return [
	'type' => '{$type}',
	'title' => '{$title}',
	'description' => '{$description}',
	'author' => '{$author}',
	'url' => '{$url}',
	'adminLink' => {$adminLink},
	'installMessage' => '{$installMessage}',
];");
		}
	}

	/**
	 * テーマのマイグレーションを実行する
	 *
	 * @param string $theme テーマ名
     * @return bool|string
	 */
	public function migrateTheme(UploadedFile $file)
	{
	    $theme = parent::setup($file);
	    if(!$theme) return false;
		$is5 = $is51 = false;
        if(file_exists(TMP_ADDON_MIGRATOR . $theme . DS . 'src' . DS . 'Plugin.php')) {
            $is5 = true;
        } elseif(file_exists(TMP_ADDON_MIGRATOR . $theme . DS . 'src' . DS . $theme . 'Plugin.php')) {
            $is51 = true;
        }
        if($is51) return $theme;

		$theme = $this->migrateBasicDir($theme, $is5);
		$this->makePluginClass($theme, $is5);
		$this->migrateThemeStructure($theme, $is5);

		$themePath = TMP_ADDON_MIGRATOR . $theme . DS;
		$srcPath = $themePath . 'src' . DS;

		$this->migrateAddonConfig($theme, 'Theme', $themePath . 'config.php', $is5);
		$this->migrateConfig($themePath . 'config', $is5);
		$this->migrateHelper($theme, $srcPath . 'View' . DS . 'Helper', $is5);

		$templatePath = TMP_ADDON_MIGRATOR . $theme . DS . 'templates' . DS;
		$this->migrateTemplate($templatePath, $is5);
		return $theme;
	}

	/**
	 * アドオンを基本的なフォルダ構成にする
	 * @param string $plugin
	 * @return string
	 */
	public function migrateBasicDir(string $plugin, bool $is5): string
	{
		$newName = \Cake\Utility\Inflector::camelize($plugin);
		if($is5) return $newName;

		if ($plugin !== $newName) {
		    rename(TMP_ADDON_MIGRATOR . $plugin, TMP_ADDON_MIGRATOR . 'tmp_plugin');
			rename(TMP_ADDON_MIGRATOR . 'tmp_plugin', TMP_ADDON_MIGRATOR . $newName);
		}
		$pluginPath = TMP_ADDON_MIGRATOR . $plugin . DS;
		if (!is_dir($pluginPath . 'src')) (new \BaserCore\Utility\BcFolder())->create($pluginPath . 'src');
		if (is_dir($pluginPath . 'Test')) rename($pluginPath . 'Test', $pluginPath . 'tests');
		if (is_dir($pluginPath . 'tests' . DS . 'Case')) rename($pluginPath . 'tests' . DS . 'Case', $pluginPath . 'tests' . DS . 'TestCase');
		return $newName;
	}

	/**
	 * プラグインクラスを作成する
	 * @param string $plugin
	 * @return void
	 */
	public function makePluginClass(string $plugin, bool $is5)
	{
		$srcPath = TMP_ADDON_MIGRATOR . $plugin . DS . 'src';
		if ($is5) {
		    $newPath = $srcPath . DS . $plugin . 'Plugin.php';
		    rename($srcPath . DS . 'Plugin.php', $newPath);
		    $file = new BcFile($newPath);
		    $content = $file->read();
		    $content = str_replace('class Plugin', 'class ' . $plugin . 'Plugin', $content);
		    $file->write($content);
            return;
        }
		(new \BaserCore\Utility\BcFolder())->create($srcPath);
		$file = new \BaserCore\Utility\BcFile($srcPath . DS . $plugin . 'Plugin.php');
		$file->write("<?php
namespace {$plugin};
use BaserCore\BcPlugin;
class {$plugin}Plugin extends BcPlugin {}");
	}

	/**
	 * プラグインの構造変更を実行
	 *
	 * @param string $plugin プラグイン名
	 * @param string $php phpの実行ファイルのパス
	 */
	public function migratePluginStructure(string $plugin, bool $is5)
	{
	    if($is5) return;
		$pluginPath = TMP_ADDON_MIGRATOR . $plugin . DS;

		// Config
		if (is_dir($pluginPath . 'Config')) {
		    // 一旦、別名に変更しないと、renameが失敗するため
            rename($pluginPath . 'Config', $pluginPath . 'configs');
            rename($pluginPath . 'configs', $pluginPath . 'config');
        }

		// View
		if (is_dir($pluginPath . 'View')) rename($pluginPath . 'View', $pluginPath . 'templates');

		// Helper
		if (is_dir($pluginPath . 'templates' . DS . 'Helper')) {
		    (new \BaserCore\Utility\BcFolder())->create($pluginPath . 'src' . DS . 'View');
			rename($pluginPath . 'templates' . DS . 'Helper', $pluginPath . 'src' . DS . 'View' . DS . 'Helper');
		}

		// Controller / Model / Event / Lib / Vendor
		foreach(['Controller', 'Model', 'Event', 'Lib', 'Vendor'] as $dir) {
			if (is_dir($pluginPath . $dir)) rename($pluginPath . $dir, $pluginPath . 'src' . DS . $dir);
		}

		// Table
		$modelPath = TMP_ADDON_MIGRATOR . $plugin . DS . 'src' . DS . 'Model' . DS;
		$tablePath = TMP_ADDON_MIGRATOR . $plugin . DS . 'src' . DS . 'Model' . DS . 'Table' . DS;
		if(is_dir($modelPath)) {
			$files = (new \BaserCore\Utility\BcFolder($modelPath))->read();
			if($files[1] && !is_dir($tablePath)) {
				(new \BaserCore\Utility\BcFolder())->create($tablePath);
			}
			foreach($files[1] as $file) {
				$className = \Cake\Utility\Inflector::pluralize(basename($file, '.php')) . 'Table.php';
				rename($modelPath . $file, $tablePath . $className);
			}
		}

		// move admin
		if (is_dir($pluginPath . 'templates')) {
			$files = (new \BaserCore\Utility\BcFolder($pluginPath . 'templates'))->read();
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
	 * テーマの構造変更を実行
	 *
	 * @param string $plugin プラグイン名
	 * @param string $php phpの実行ファイルのパス
	 */
	public function migrateThemeStructure(string $plugin, bool $is5)
	{
	    if($is5) return;
		$pluginPath = TMP_ADDON_MIGRATOR . $plugin . DS;

		// Config
		if (is_dir($pluginPath . 'Config')) rename($pluginPath . 'Config', $pluginPath . 'Config');

		// Helper
		if (is_dir($pluginPath . 'Helper')) {
			if(!is_dir($pluginPath . 'src' . DS . 'View')) {
				(new \BaserCore\Utility\BcFolder())->create($pluginPath . 'src' . DS . 'View');
			}
			rename($pluginPath . 'Helper', $pluginPath . 'src' . DS . 'View' . DS . 'Helper');
		}

		if(!is_dir($pluginPath . 'webroot')) {
			(new \BaserCore\Utility\BcFolder())->create($pluginPath . 'webroot');
		}

		if(!is_dir($pluginPath . 'templates')) {
			(new \BaserCore\Utility\BcFolder())->create($pluginPath . 'templates');
		}

		// templates
		$files = (new \BaserCore\Utility\BcFolder($pluginPath))->read();
		foreach($files[0] as $dir) {
			switch($dir) {
				case 'css':
				case 'js':
				case 'img':
					rename($pluginPath . $dir, $pluginPath . 'webroot' . DS . $dir);
					break;
				case 'Elements':
					rename($pluginPath . $dir, $pluginPath . 'templates' . DS . 'element');
					$this->moveAdminTemplates($plugin, 'element');
					break;
				case 'Layouts':
					rename($pluginPath . $dir, $pluginPath . 'templates' . DS . 'layout');
					$this->moveAdminTemplates($plugin, 'layout');
					break;
				case 'Emails':
					rename($pluginPath . $dir, $pluginPath . 'templates' . DS . 'email');
					$this->moveAdminTemplates($plugin, 'email');
					break;
				case 'templates':
				case 'webroot':
				case 'config':
				case 'src':
					break;
				default:
					rename($pluginPath . $dir, $pluginPath . 'templates' . DS . $dir);
					$this->moveAdminTemplates($plugin, $dir);
					break;
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
		$templatesPath = TMP_ADDON_MIGRATOR . $plugin . DS . 'templates' . DS;
		if (!is_dir($templatesPath . 'Admin')) {
			(new \BaserCore\Utility\BcFolder())->create($templatesPath . 'Admin');
		}
		$files = (new \BaserCore\Utility\BcFolder($templatesPath . $name))->read();
		foreach($files[0] as $dir) {
			if ($dir !== 'admin') continue;
			$adminPath = $templatesPath . $name . DS . $dir . DS;
			$files = (new \BaserCore\Utility\BcFolder($adminPath))->read();
			$files = $files[0] + $files[1];
			foreach($files as $file) {
				if (!is_dir($templatesPath . 'Admin' . DS . $name)) {
					(new \BaserCore\Utility\BcFolder())->create($templatesPath . 'Admin' . DS . $name);
				}
				rename($adminPath . $file, $templatesPath . 'Admin' . DS . $name . DS . $file);
			}
			(new \BaserCore\Utility\BcFolder($adminPath))->delete($adminPath);
		}
	}

	/**
	 * コントローラーファイルのマイグレーションを実行
	 *
	 * @param string $path コントローラーディレクトリへの実行パス
	 */
	public function migrateController(string $plugin, string $path, bool $is5)
	{
		if (!is_dir($path)) return;
		$files = (new \BaserCore\Utility\BcFolder($path))->read(true, true, true);
		foreach($files[1] as $file) {
			(new MigrateController5)->migrate($plugin, $file, $is5);
		}
	}

	/**
	 * イベントファイルのマイグレーションを実行
	 *
	 * @param string $path イベントディレクトリへの実行パス
	 */
	public function migrateEvent(string $plugin, string $path, bool $is5)
	{
	    if($is5) return;
		if (!is_dir($path)) return;
		$files = (new \BaserCore\Utility\BcFolder($path))->read(true, true, true);
		foreach($files[1] as $file) {
			(new MigrateEvent5)->migrate($plugin, $file, $is5);
		}
	}

	/**
	 * サブフォルダを取得する
	 * @param $plugin
	 * @param $path
	 * @return array|string|string[]|null
	 */
	public function getSubDir($plugin, $path)
	{
		$path = dirname($path);
		$subDir = str_replace(TMP_ADDON_MIGRATOR . $plugin . DS . 'src' . DS . 'Controller', '', $path);
		$subDir = preg_replace('/^\//', '', $subDir);
		return $subDir;
	}

	/**
	 * コンポーネントファイルのマイグレーションを実行
	 *
	 * @param string $path コンポーネントディレクトリのパス
	 */
	public function migrateComponent(string $plugin, string $path, bool $is5)
	{
	    if($is5) return;
		if (!is_dir($path)) return;
		$files = (new \BaserCore\Utility\BcFolder($path))->read(true, true, true);
		foreach($files[1] as $file) {
			(new MigrateComponent5())->migrate($plugin, $file, $is5);
		}
	}

	/**
	 * テーブルファイルのマイグレーションを実行
	 *
	 * @param string $path テーブルディレクトリのパス
	 */
	public function migrateTable(string $plugin, string $path, bool $is5)
	{
	    if($is5) return;
		if (!is_dir($path)) return;
		$files = (new \BaserCore\Utility\BcFolder($path))->read(true, true, true);
		foreach($files[1] as $file) {
			(new MigrateTable5())->migrate($plugin, $file, $is5);
		}
	}

	/**
	 * ビヘイビアファイルのマイグレーションを実行
	 *
	 * @param string $path ビヘイビアディレクトリのパス
	 */
	public function migrateBehavior(string $plugin, string $path, bool $is5)
	{
	    if($is5) return;
		if (!is_dir($path)) return;
		$files = (new \BaserCore\Utility\BcFolder($path))->read(true, true, true);
		foreach($files[1] as $file) {
			(new MigrateBehavior5())->migrate($plugin, $file, $is5);
		}
	}

	/**
	 * プラグイン設定ファイルのマイグレーションを実行
	 *
	 * @param string $path 設定ファイルのパス
	 * @param string $plugin 古いプラグイン名
	 * @param string $newPlugin 新しいプラグイン名
	 */
	public function migrateConfig($path, bool $is5)
	{
	    if($is5) return;
		if (!is_dir($path)) return;
		$Folder = new \BaserCore\Utility\BcFolder($path);
		$files = $Folder->read(true, true, true);
		foreach($files[0] as $dir) {
			$this->migrateTemplate($dir, $is5);
		}
		foreach($files[1] as $file) {
			(new MigrateConfig5())->migrate($file, $is5);
		}
	}

	/**
	 * ビューファイルのマイグレーションを実行
	 *
	 * @param string $path ビューディレクトリのパス
	 * @param string $plugin 古いプラグイン名
	 */
	public function migrateView(string $plugin, string $path, $is5)
	{
		if (!is_dir($path)) return;
		$files = (new \BaserCore\Utility\BcFolder($path))->read(true, true, true);
		foreach($files[1] as $file) {
			(new MigrateView5())->migrate($plugin, $file, $is5);
		}
	}

	/**
	 * ヘルパーファイルのマイグレーションを実行
	 *
	 * @param string $path ヘルパーディレクトリのパス
	 */
	public function migrateHelper(string $plugin, string $path, bool $is5)
	{
		if (!is_dir($path)) return;
		$files = (new \BaserCore\Utility\BcFolder($path))->read(true, true, true);
		foreach($files[1] as $file) {
			(new MigrateHelper5())->migrate($plugin, $file, $is5);
		}
	}

	/**
	 * ヘルパーファイルのマイグレーションを実行
	 *
	 * @param string $path ヘルパーディレクトリのパス
	 */
	public function migrateServiceProvider(string $path, bool $is5)
	{
		if (!is_dir($path)) return;
		$files = (new \BaserCore\Utility\BcFolder($path))->read(true, true, true);
		foreach($files[1] as $file) {
			(new MigrateServiceProvider5())->migrate($file, $is5);
		}
	}

	/**
	 * テンプレートファイルのマイグレーションを実行
	 * @param string $path
	 * @return void
	 */
	public function migrateTemplate(string $path, bool $is5)
	{
		if (!is_dir($path)) return;
		$Folder = new \BaserCore\Utility\BcFolder($path);
		$files = $Folder->read(true, true, true);
		foreach($files[0] as $dir) {
			$this->migrateTemplate($dir, $is5);
		}
		foreach($files[1] as $file) {
			(new MigrateTemplate5())->migrate($file, $is5);
		}
	}

}
