<?php
/**
 * include files
 */

namespace BcAddonMigrator\Controller\Component;

/**
 * BcAddonMigrator4Component
 */
class BcAddonMigrator4Component extends BcAddonMigratorComponent implements BcAddonMigratorInterface
{
	
	/**
	 * Cake Migrator を利用するかどうか
	 * @return bool
	 */
	public function useCakeMigrator()
	{
		return false;
	}
	
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
	public function getPluginMessage()
	{
		return array_merge($this->__pluginMessage, $this->__themeMessage);
	}
	
	/**
	 * テーマ用メッセージを取得する
	 *
	 * @return array
	 */
	public function getThemeMessage()
	{
		return $this->__themeMessage;
	}
	
	/**
	 * プラグインのマイグレーションを実行
	 *
	 * @param string $plugin プラグイン名
	 * @param string $php phpの実行ファイルのパス
	 */
	public function migratePlugin($plugin, $php = 'php')
	{
		
		$this->migratePluginStructure($plugin, $php);
		
		$pluginPath = APP . 'Plugin' . DS . $plugin . DS;
		
		$this->migrateController($pluginPath . 'Controller');
		$this->migrateComponent($pluginPath . 'Controller' . DS . 'Component');
		$this->migrateModel($pluginPath . 'Model');
		$this->migrateBehavior($pluginPath . 'Model' . DS . 'Behavior');
		$this->migratePluginConfig($pluginPath . 'Config');
		$this->migrateHelper($pluginPath . 'View' . DS . 'Helper');
		$this->migrateView($pluginPath . 'View');
		
	}
	
	/**
	 * テーマのマイグレーションを実行する
	 *
	 * @param string $theme テーマ名
	 */
	public function migrateTheme($theme)
	{
		
		$this->migrateThemeStructure($theme);
		$themePath = WWW_ROOT . 'theme' . DS . $theme;
		$this->migrateHelper($themePath . DS . 'Helper');
		$this->migrateView($themePath);
		
	}
	
	/**
	 * テーマフォルダの構造変更を実行
	 *
	 * @param string $theme テーマ名
	 */
	public function migrateThemeStructure($theme)
	{
		
	}
	
	/**
	 * プラグインの構造変更を実行
	 *
	 * @param string $plugin プラグイン名
	 * @param string $php phpの実行ファイルのパス
	 */
	public function migratePluginStructure($plugin, $php = 'php')
	{
		
	}
	
	/**
	 * コントローラーファイルのマイグレーションを実行
	 *
	 * @param string $path コントローラーディレクトリへの実行パス
	 */
	public function migrateController($path)
	{
		// コントローラー書き換え
		if (is_dir($path)) {
			$Folder = new Folder($path);
			$files = $Folder->read(true, true, true);
			if (!empty($files[1])) {
				foreach($files[1] as $file) {
					$File = new File($file);
					$data = $File->read();
					$data = preg_replace('/extends\s+BcPluginAppController/', 'extends AppController', $data);
					$File->write($data, 'w+', true);
					$File->close();
					$this->log('コントローラーファイル：' . basename($file) . 'を マイグレーションしました。', 'migration');
				}
			}
		}
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
