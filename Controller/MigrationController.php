<?php
/**
 * MigrationController
 */
class MigrationController extends AppController {
	
/**
 * コンポーネント
 * @var array
 */
	public $components = array('Cookie', 'BcAuth', 'BcAuthConfigure');
	
/**
 * モデル
 * @var array
 */
	public $uses = array();

/**
 * マイグレーター名
 * 
 * @var null
 */
	public $migrator = null;
	
/**
 * beforeFilter
 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->migrator = 'BcAddonMigrator' . $this->getMajorVersion();
		$migratorClass = $this->migrator . 'Component';
		App::uses($migratorClass, 'BcAddonMigrator.Controller/Component');
		if(class_exists($migratorClass)) {
			$this->{$this->migrator} = $this->Components->load('BcAddonMigrator.' . $this->migrator);	
		} else {
			$this->setMessage('このプラグインは、このバージョンのbaserCMSに対応していません。', true);
		}
	}
	
/**
 * [ADMIN] インデックスページ
 */
	public function admin_index() {
		$this->pageTitle = 'baserCMS アドオンマイグレーター';
	}
	
/**
 * [ADMIN] プラグインのマイグレーション
 */
	public function admin_plugin() {

		$useCakeMigrator = $this->{$this->migrator}->useCakeMigrator();
		if($this->request->data) {
			if($this->{$this->migrator}->useCakeMigrator()) {
				$this->{$this->migrator}->migratePluginByCake($this->request->data['Migration']['name'], $this->request->data['Migration']['php']);
			}
			$this->{$this->migrator}->migratePlugin($this->request->data['Migration']['name']);
			$this->setMessage('プラグイン： ' . $this->request->data['Migration']['name'] . ' のマイグレーションが完了しました。');
			$this->redirect('plugin');
		}
		
		$this->pageTitle = 'baserCMS プラグインマイグレーション';
		$Folder = new Folder(APP . 'Plugin');
		$files = $Folder->read(true, true, false);
		$plugins = array();
		if(!empty($files[0])) {
			foreach($files[0] as $file) {
				if($file != 'BcAddonMigrator') {
					$plugins[$file] = $file;
				}
			}
		}
		$pluginMessage = $this->{$this->migrator}->getPluginMessage();
		$this->set('pluginMessage', $pluginMessage);
		$this->set('plugins', $plugins);
		$this->set('useCakeMigrator', $useCakeMigrator);
		
	}
	
/**
 * [ADMIN] テーマのマイグレーション
 */
	public function admin_theme() {

		if($this->request->data) {
			$this->{$this->migrator}->migrateTheme($this->request->data['Migration']['name']);
			$this->setMessage('テーマ： ' . $this->request->data['Migration']['name'] . ' のマイグレーションが完了しました。');
			$this->redirect('theme');
		}
		
		$this->pageTitle = 'baserCMS テーママイグレーション';
		$Folder = new Folder(WWW_ROOT . 'theme');
		$files = $Folder->read(true, true, false);
		$themes = array();
		if(!empty($files[0])) {
			foreach($files[0] as $file) {
				$themes[$file] = $file;
			}
		}
		
		$themeMessage = $this->{$this->migrator}->getThemeMessage();
		$this->set('themeMessage', $themeMessage);
		$this->set('themes', $themes);
		
	}	

/**
 * baserCMSのメジャーバージョンを取得
 * 
 * @return string
 */
	public function getMajorVersion() {
		return preg_replace('/([0-9])\..+/', "$1", getVersion());
	}
	
}