<?php
/**
 * include files
 */
App::uses('Component', 'Controller');

/**
 * BcAddonMigrator3Component
 */
class BcAddonMigrator3Component extends Component {

/**
 * プラグイン用メッセージ
 * @var array
 */
	public $__pluginMessage = array(
		'プラグインのフォルダ名はキャメルケースになりました。（bc_addon_migrator → BcAddonMigrator）',
		'データベースデータテーブル初期化用関数の引数の内容が変わりました。（$this->Plugin->initDb(\'plugin\', \'プラグイン名\');）',
		'プラグイン設定ファイルの読込関数が変わりました。（Configure::load(\'プラグイン名.設定ファイル名\');）',
		'Router::connect() など、Routerの設定は、Config/routes.php に移動してください。自動的に読み込まれます。',
		'Modelのdelメソッドを利用している場合は、deleteメソッドに書き換えてください。',
		'プラグインフックは、イベントの仕組みに変更となりましたので適宜書き換えてください。',
		'クラス内の関数や変数においてアクセス修飾子のないものは public が自動的に付与されますのでマイグレーション後、各メソッドについて見なおしてください。',
	);
	
/**
 * テーマ用メッセージ
 * 
 * @var array
 */
	public $__themeMessage = array(
		'テーマのフォルダ名について、CakePHP２系以降キャメルケースとなりましたが、baserCMSではアンダースコア区切りとなりますのでご注意ください。',
		'PaginatorHelperを利用している場合、第１引数と第２引数を入れ替えてください。',
		'管理画面のアセットファイルは全て admin フォルダに移動になりましたので参照している場合はURLを書き換えてください。（/img/ajax-loader.gif → /img/admin/ajax-loader.gif）',
		'ヘルパの参照方法が変わりました。独自ヘルパを利用されている場合は、次のように書き換えてください。（$uploader → $this->Uploader）',
		'BcAuthComponent::user() で取得できる配列の階層が変更となりました。モデル名のキーはなくなっています。（$data[\'User\'][\'name\'] → $data[\'name\']）'
	);
	
/**
 * プラグイン用メッセージを取得する
 * 
 * @return array
 */
	public function getPluginMessage() {
		return array_merge($this->__pluginMessage, $this->__themeMessage);
	}

/**
 * テーマ用メッセージを取得する
 * 
 * @return array
 */
	public function getThemeMessage() {
		return $this->__themeMessage;
	}

/**
 * プラグインのマイグレーションを実行
 * 
 * @param string $plugin プラグイン名
 * @param string $php phpの実行ファイルのパス
 */
	public function migratePlugin($plugin, $php = 'php') {
		
		$this->migratePluginStructure($plugin, $php);
		
		$newPlugin = Inflector::camelize($plugin);
		$pluginPath = APP . 'Plugin' . DS . $plugin . DS;		
		
		$this->migrateController($pluginPath . 'Controller');
		$this->migrateComponent($pluginPath . 'Controller' . DS . 'Component');
		$this->migrateModel($pluginPath . 'Model');
		$this->migrateBehavior($pluginPath . 'Model' . DS . 'Behavior');
		$this->migratePluginConfig($pluginPath . 'Config', $plugin, $newPlugin);
		$this->migrateHelper($pluginPath . 'View' . DS . 'Helper');
		$this->migrateView($pluginPath . 'View', $plugin, $newPlugin);
		
	}
	
/**
 * テーマのマイグレーションを実行する
 * 
 * @param string $theme テーマ名
 */
	public function migrateTheme($theme) {
		
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
	public function migrateThemeStructure($theme) {
		
		$themePath = WWW_ROOT . 'theme' . DS . $theme;
		$Folder = new Folder($themePath);
		$files = $Folder->read(true, true, true);
		if($files[0]) {
			foreach($files[0] as $file) {
				switch (basename($file)) {
					case 'helpers':
						rename($file, $themePath . DS . 'Helper');
						break;
					case 'elements':
						rename($file, $themePath . DS . 'Elements');
						break;
					case 'webroot':
					case 'js':
					case 'css':
					case 'img':
						break;
					default:
						rename($file, $themePath . DS . Inflector::camelize(basename($file)));
				}
			}
		}
		
		$Folder = new Folder($themePath . DS . 'Helper');
		$files = $Folder->read(true, true, true);
		if($files[1]) {
			foreach($files[1] as $file) {
				rename($file, $themePath . DS . 'Helper' . DS . Inflector::camelize(basename($file, '.php')) . 'Helper.php');
			}
		}
		
	}
	
/**
 * プラグインの構造変更を実行
 * 
 * @param string $plugin プラグイン名
 * @param string $php phpの実行ファイルのパス
 */
	public function migratePluginStructure($plugin, $php = 'php') {
		
		if(!$php) {
			$php = 'php';
		}

		$cake = ROOT . DS . 'lib' . DS . 'Cake' . DS . 'Console' . DS . 'cake.php';
		$command = 'upgrade all --plugin';
		$newPlugin = Inflector::camelize($plugin);
		$pluginPath = APP . 'Plugin' . DS . $plugin . DS;
		
		// CakePHP UpgradeShell 実行
		ob_start();
		passthru($php . ' ' . $cake . ' ' . $command . ' ' . $plugin);
		$this->log(ob_get_clean(), 'migration');
		
		// Mac対策
		rename($pluginPath . 'config', $pluginPath . 'Config');
		rename($pluginPath . 'View' . DS . 'elements', $pluginPath . 'View' . DS . 'Elements');
		if(is_dir($pluginPath . 'controllers' . DS)) {
			$Folder = new Folder($pluginPath . 'controllers' . DS);
			$Folder->delete();
		}
		
		// vendorsを移動
		if(is_dir($pluginPath . 'vendors' . DS . 'css') || is_dir($pluginPath . 'vendors' . DS . 'js') || is_dir($pluginPath . 'vendors' . DS . 'img')) {
			if(!is_dir($pluginPath . 'webroot')) {
				mkdir($pluginPath . 'webroot');
			}
		}
		if(is_dir($pluginPath . 'webroot')) {
			if(is_dir($pluginPath . 'vendors' . DS . 'css')) {
				rename($pluginPath . 'vendors' . DS . 'css', $pluginPath . 'webroot' . DS . 'css');
			}
			if(is_dir($pluginPath . 'vendors' . DS . 'js')) {
				rename($pluginPath . 'vendors' . DS . 'js', $pluginPath . 'webroot' . DS . 'js');
			}
			if(is_dir($pluginPath . 'vendors' . DS . 'img')) {
				rename($pluginPath . 'vendors' . DS . 'img', $pluginPath . 'webroot' . DS . 'img');
			}
			$Folder = new Folder($pluginPath . 'vendors');
			$files = $Folder->read();
			if(!empty($files[0]) && !empty($files[1])) {
				rmdir($pluginPath . 'vendors');
			}
			$this->log('vendors フォルダ内の css・js・img フォルダ を View/webroot/ 内に移動しました。', 'migration');
		}
		if(is_dir($pluginPath . 'vendors')) {
			rename($pluginPath . 'vendors', $pluginPath . 'Vendor');
		}
		
		// プラグイン名をキャメルケースに
		rename(APP . 'Plugin' . DS . $plugin, APP . 'Plugin' . DS . $newPlugin);
		
	}
	
/**
 * コントローラーファイルのマイグレーションを実行
 * 
 * @param string $path コントローラーディレクトリへの実行パス
 */
	public function migrateController($path) {
		
		// コントローラー書き換え
		if(is_dir($path)) {
			$Folder = new Folder($path);
			$files = $Folder->read(true, true, true);
			if(!empty($files[1])) {
				foreach($files[1] as $file) {
					$File = new File($file);
					$data = $File->read();
					$data = preg_replace('/extends\s+[a-zA-Z0-9]+/', 'extends BcPluginAppController', $data);
					$data = str_replace('BC_TEXT_HELPER', '\'BcText\'', $data);
					$data = str_replace('BC_TIME_HELPER', '\'BcTime\'', $data);
					$data = str_replace('BC_FORM_HELPER', '\'BcForm\'', $data);
					$data = str_replace('BC_BASER_HELPER', '\'BcBaser\'', $data);
					$data = str_replace('BC_BASER_ADMIN_HELPER', '\'BcBaserAdmin\'', $data);
					$data = str_replace('BC_ARRAY_HELPER', '\'BcArray\'', $data);
					$data = str_replace('BC_CKEDITOR_HELPER', '\'BcCkeditor\'', $data);
					$data = str_replace('BC_CSV_HELPER', '\'BcCsv\'', $data);
					$data = str_replace('BC_FREEZE_HELPER', '\'BcFreeze\'', $data);
					$data = str_replace('BC_GOOGLEMAPS_HELPER', '\'BcGooglemaps\'', $data);
					$data = str_replace('BC_HTML_HELPER', '\'BcHtml\'', $data);
					$data = str_replace('BC_MOBILE_HELPER', '\'BcMobile\'', $data);
					$data = str_replace('BC_SMARTPHONE_HELPER', '\'BcSmartphone\'', $data);
					$data = str_replace('BC_UPLOAD_HELPER', '\'BcUpload\'', $data);
					$data = str_replace('BC_XML_HELPER', '\'BcXml\'', $data);
					$data = str_replace('$this->data', '$this->request->data', $data);
					$data = str_replace('Routing.admin', 'Routing.prefixes.0', $data);
					$data = preg_replace('/\n[\t\s]*function[\t\s]+([0-9a-zA-Z_]+)/', "\n	public function $1", $data);
					$data = preg_replace('/\n[\t\s]*var[\t\s]+(\$[0-9a-zA-Z_]+)/', "\n	public $1", $data);
					$data = preg_replace('/\?>[\n\t\s]*$/', "\n", $data);
					$File->write($data, 'w+', true);
					$File->close();
					$this->log('コントローラーファイル：' . basename($file) . 'を マイグレーションしました。' , 'migration');
				}
			}
		}
		
	}
	
/**
 * コンポーネントファイルのマイグレーションを実行
 * 
 * @param string $path コンポーネントディレクトリのパス
 */
	public function migrateComponent($path) {
		
		// コンポーネント書き換え
		if(is_dir($path)) {
			$Folder = new Folder($path);
			$files = $Folder->read(true, true, true);
			if(!empty($files[1])) {
				foreach($files[1] as $file) {
					$File = new File($file);
					$data = $File->read();
					$data = str_replace('BC_TEXT_HELPER', '\'BcText\'', $data);
					$data = str_replace('BC_TIME_HELPER', '\'BcTime\'', $data);
					$data = str_replace('BC_FORM_HELPER', '\'BcForm\'', $data);
					$data = str_replace('BC_BASER_HELPER', '\'BcBaser\'', $data);
					$data = str_replace('BC_BASER_ADMIN_HELPER', '\'BcBaserAdmin\'', $data);
					$data = str_replace('BC_ARRAY_HELPER', '\'BcArray\'', $data);
					$data = str_replace('BC_CKEDITOR_HELPER', '\'BcCkeditor\'', $data);
					$data = str_replace('BC_CSV_HELPER', '\'BcCsv\'', $data);
					$data = str_replace('BC_FREEZE_HELPER', '\'BcFreeze\'', $data);
					$data = str_replace('BC_GOOGLEMAPS_HELPER', '\'BcGooglemaps\'', $data);
					$data = str_replace('BC_HTML_HELPER', '\'BcHtml\'', $data);
					$data = str_replace('BC_MOBILE_HELPER', '\'BcMobile\'', $data);
					$data = str_replace('BC_SMARTPHONE_HELPER', '\'BcSmartphone\'', $data);
					$data = str_replace('BC_UPLOAD_HELPER', '\'BcUpload\'', $data);
					$data = str_replace('BC_XML_HELPER', '\'BcXml\'', $data);
					$data = str_replace('Routing.admin', 'Routing.prefixes.0', $data);
					$data = preg_replace('/\n[\t\s]*function[\t\s]+([0-9a-zA-Z_]+)/', "\n	public function $1", $data);
					$data = preg_replace('/\n[\t\s]*var[\t\s]+(\$[0-9a-zA-Z_]+)/', "\n	public $1", $data);
					$data = preg_replace('/\?>[\n\t\s]*$/', "\n", $data);
					$File->write($data, 'w+', true);
					$File->close();
					$this->log('コンポーネントファイル：' . basename($file) . 'を マイグレーションしました。' , 'migration');
				}
			}
		}
		
	}
	
/**
 * モデルファイルのマイグレーションを実行
 * 
 * @param string $path モデルディレクトリのパス
 */
	public function migrateModel($path) {
		
		// モデル書き換え
		if(is_dir($path)) {
			$Folder = new Folder($path);
			$files = $Folder->read(true, true, true);
			if(!empty($files[1])) {
				foreach($files[1] as $file) {
					$File = new File($file);
					$data = $File->read();
					$data = preg_replace('/extends\s+[a-zA-Z0-9]+/', 'extends BcPluginAppModel', $data);
					$data = str_replace('Routing.admin', 'Routing.prefixes.0', $data);
					$data = preg_replace('/\n[\t\s]*function[\t\s]+([0-9a-zA-Z_]+)/', "\n	public function $1", $data);
					$data = preg_replace('/\n[\t\s]*var[\t\s]+(\$[0-9a-zA-Z_]+)/', "\n	public $1", $data);
					$data = preg_replace('/\?>[\n\t\s]*$/', "\n", $data);
					$File->write($data, 'w+', true);
					$File->close();
					$this->log('モデルファイル：' . basename($file) . 'を マイグレーションしました。' , 'migration');
				}
			}
		}
		
	}
	
/**
 * ビヘイビアファイルのマイグレーションを実行
 * 
 * @param string $path ビヘイビアディレクトリのパス
 */
	public function migrateBehavior($path) {
		
		// ビヘイビア書き換え
		if(is_dir($path)) {
			$Folder = new Folder($path);
			$files = $Folder->read(true, true, true);
			if(!empty($files[1])) {
				foreach($files[1] as $file) {
					$File = new File($file);
					$data = $File->read();
					$data = str_replace('Routing.admin', 'Routing.prefixes.0', $data);
					$data = preg_replace('/\n[\t\s]*function[\t\s]+([0-9a-zA-Z_]+)/', "\n	public function $1", $data);
					$data = preg_replace('/\n[\t\s]*var[\t\s]+(\$[0-9a-zA-Z_]+)/', "\n	public $1", $data);
					$data = preg_replace('/\?>[\n\t\s]*$/', "\n", $data);
					$File->write($data, 'w+', true);
					$File->close();
					$this->log('ビヘイビアファイル：' . basename($file) . 'を マイグレーションしました。' , 'migration');
				}
			}
		}
		
	}
	
/**
 * プラグイン設定ファイルのマイグレーションを実行
 * 
 * @param string $path 設定ファイルのパス
 * @param string $plugin 古いプラグイン名
 * @param string $newPlugin 新しいプラグイン名
 */
	public function migratePluginConfig($path, $plugin, $newPlugin) {
		
		// インストーラー書き換え
		if(file_exists($path . DS . 'init.php')) {
			$File = new File($path . DS . 'init.php');
			$data = $File->read();
			$data = preg_replace('/\$this->Plugin->initDb\(\'' . $plugin . '\'\);/', '$this->Plugin->initDb(\'plugin\', \'' . $newPlugin . '\');', $data);
			$File->write($data, 'w+', true);
			$File->close();
			$this->log('init.php を マイグレーションしました。' , 'migration');
		}
		
		// bootstrap 書き換え
		if(file_exists($path . DS . 'bootstrap.php')) {
			$File = new File($path . DS . 'bootstrap.php');
			$data = $File->read();
			$data = preg_replace('/loadPluginConfig\(\'' . $plugin . '\.([a-zA-Z0-9_]+)\'\);/', 'loadPluginConfig(\'' . $newPlugin . '.$1\');', $data);
			$File->write($data, 'w+', true);
			$File->close();
			$this->log('bootstrap.php を マイグレーションしました。' , 'migration');
			
		}
	}
	
/**
 * ヘルパーファイルのマイグレーションを実行
 * 
 * @param string $path ヘルパーディレクトリのパス
 */
	public function migrateHelper($path) {
		
		// ヘルパ書き換え
		if(is_dir($path)) {
			$Folder = new Folder($path);
			$files = $Folder->read(true, true, true);
			if(!empty($files[1])) {
				foreach($files[1] as $file) {
					$File = new File($file);
					$data = $File->read();
					$data = str_replace('Routing.admin', 'Routing.prefixes.0', $data);
					$data = str_replace('BcText->mbTruncate(', 'BcText->truncate(', $data);
					$data = str_replace('Javascript->object', 'Js->object', $data);
					$data = str_replace('Javascript->codeBlock', 'BcHtml->scriptBlock', $data);
					$data = str_replace('BC_TEXT_HELPER', '\'BcText\'', $data);
					$data = str_replace('BC_TIME_HELPER', '\'BcTime\'', $data);
					$data = str_replace('BC_FORM_HELPER', '\'BcForm\'', $data);
					$data = str_replace('BC_BASER_HELPER', '\'BcBaser\'', $data);
					$data = str_replace('BC_BASER_ADMIN_HELPER', '\'BcBaserAdmin\'', $data);
					$data = str_replace('BC_ARRAY_HELPER', '\'BcArray\'', $data);
					$data = str_replace('BC_CKEDITOR_HELPER', '\'BcCkeditor\'', $data);
					$data = str_replace('BC_CSV_HELPER', '\'BcCsv\'', $data);
					$data = str_replace('BC_FREEZE_HELPER', '\'BcFreeze\'', $data);
					$data = str_replace('BC_GOOGLEMAPS_HELPER', '\'BcGooglemaps\'', $data);
					$data = str_replace('BC_HTML_HELPER', '\'BcHtml\'', $data);
					$data = str_replace('BC_MOBILE_HELPER', '\'BcMobile\'', $data);
					$data = str_replace('BC_SMARTPHONE_HELPER', '\'BcSmartphone\'', $data);
					$data = str_replace('BC_UPLOAD_HELPER', '\'BcUpload\'', $data);
					$data = str_replace('BC_XML_HELPER', '\'BcXml\'', $data);
					$data = preg_replace('/\n[\t\s]*function[\t\s]+([0-9a-zA-Z_]+)/', "\n	public function $1", $data);
					$data = preg_replace('/\n[\t\s]*var[\t\s]+(\$[0-9a-zA-Z_]+)/', "\n	public $1", $data);
					$data = preg_replace('/\?>[\n\t\s]*$/', "\n", $data);
					$File->write($data, 'w+', true);
					$File->close();
					$this->log('ヘルパファイル：' . basename($file) . 'を マイグレーションしました。' , 'migration');
				}
			}
		}
		
	}
	
/**
 * ビューファイルのマイグレーションを実行
 * 
 * @param string $path ビューディレクトリのパス
 * @param string $plugin 古いプラグイン名
 * @param string $newPlugin 新しいプラグイン名
 */
	public function migrateView($path, $plugin = null, $newPlugin = null) {
		
		$Folder = new Folder($path);
		$files = $Folder->read(true, true, true);
		if(!empty($files[1])) {
			foreach($files[1] as $file) {
				$File = new File($file);
				$data = $File->read();
				$data = preg_replace('/\$bcBaser->/', '$this->BcBaser->', $data);
				$data = preg_replace('/\$bcTime->/', '$this->BcTime->', $data);
				$data = preg_replace('/\$bcText->/', '$this->BcText->', $data);
				$data = preg_replace('/\$bcUpload->/', '$this->BcUpload->', $data);
				$data = preg_replace('/\$bcXml->/', '$this->BcXml->', $data);
				$data = preg_replace('/\$bcSmartphone->/', '$this->BcSmartphone->', $data);
				$data = preg_replace('/\$bcPage->/', '$this->BcPage->', $data);
				$data = preg_replace('/\$bcMobile->/', '$this->BcMobile->', $data);
				$data = preg_replace('/\$bcHtml->/', '$this->BcHtml->', $data);
				$data = preg_replace('/\$html->/', '$this->Html->', $data);
				$data = preg_replace('/\$bcGooglemaps->/', '$this->BcGooglemaps->', $data);
				$data = preg_replace('/\$bcFreeze->/', '$this->BcFreeze->', $data);
				$data = preg_replace('/\$bcForm->/', '$this->BcForm->', $data);
				$data = preg_replace('/\$form->/', '$this->Form->', $data);
				$data = preg_replace('/\$bcCsv->/', '$this->BcCsv->', $data);
				$data = preg_replace('/\$bcCkeditor->/', '$this->BcCkeditor->', $data);
				$data = preg_replace('/\$bcArray->/', '$this->BcArray->', $data);
				$data = preg_replace('/\$bcAdmin->/', '$this->BcAdmin->', $data);
				$data = preg_replace('/\$paginator->/', '$this->Paginator->', $data);
				$data = preg_replace('/\$paginator->/', '$this->Paginator->', $data);
				$data = preg_replace('/\$blog->/', '$this->Blog->', $data);
				$data = preg_replace('/\$feed->/', '$this->Feed->', $data);
				$data = preg_replace('/\$mailform->/', '$this->Mailform->', $data);
				$data = preg_replace('/\$mailfield->/', '$this->Mailfield->', $data);
				$data = preg_replace('/\$maildata->/', '$this->Maildata->', $data);
				$data = preg_replace('/\$mail->/', '$this->Mail->', $data);
				$data = str_replace('$this->data', '$this->request->data', $data);
				$data = str_replace('Routing.admin', 'Routing.prefixes.0', $data);
				$data = str_replace('BcText->mbTruncate(', 'BcText->truncate(', $data);
				$data = str_replace('$javascript->object', '$this->Js->object', $data);
				$data = str_replace('$javascript->codeBlock', '$this->BcHtml->scriptBlock', $data);

				if($plugin) {
					$data = preg_replace('/\'\/' . $plugin . '\/css\//', '\'' . $newPlugin . '.', $data);
					$data = preg_replace('/\'\/' . $plugin . '\/js\//', '\'' . $newPlugin . '.', $data);
					$data = preg_replace('/\'\/' . $plugin . '\/img\//', '\'' . $newPlugin . '.', $data);
				}

				$File->write($data, 'w+', true);
				$File->close();

				if($plugin) {
					$this->log('ビューファイル：' . preg_replace('/^' . preg_quote(APP . 'Plugin' . DS . $newPlugin . DS . 'View' . DS, DS) . '/', '', $file) . 'を マイグレーションしました。' , 'migration');
				} elseif(preg_match('/^' . preg_quote(WWW_ROOT, DS) . '/', $file)) {
					$this->log('ビューファイル：' . preg_replace('/^' . preg_quote(WWW_ROOT, DS) . '/', '', $file) . 'を マイグレーションしました。' , 'migration');
				} else {
					$this->log('ビューファイル：' . preg_replace('/^' . preg_quote(ROOT, DS) . '/', '', $file)  . 'を マイグレーションしました。', 'migration');
				}
			}
			
		}
		
		if(!empty($files[0])) {
			foreach($files[0] as $file) {
				$this->migrateView($file, $plugin, $newPlugin);
			}
		}
		
	}
	
}