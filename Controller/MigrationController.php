<?php
/**
 * MigrationController
 */
class MigrationController extends BcPluginAppController {
	
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
 * beforeFilter
 */
	public function beforeFilter() {
		parent::beforeFilter();
		$migrator = 'BcAddonMigrator' . $this->getMajorVersion();
		$this->{$migrator} = $this->Components->load('BcAddonMigrator.' . $migrator);
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
		
		$migrator = 'BcAddonMigrator' . $this->getMajorVersion();
		if($this->request->data) {
			$this->request->data['Migration']['php'] = '/Applications/MAMP/bin/php/php5.4.10/bin/php';
			$this->{$migrator}->migratePlugin($this->request->data['Migration']['name'], $this->request->data['Migration']['php']);
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
		$pluginMessage = $this->{$migrator}->getPluginMessage();
		$this->set('pluginMessage', $pluginMessage);
		$this->set('plugins', $plugins);
		
	}
	
/**
 * [ADMIN] テーマのマイグレーション
 */
	public function admin_theme() {
		
		$migrator = 'BcAddonMigrator' . $this->getMajorVersion();
		
		if($this->request->data) {
			$this->{$migrator}->migrateTheme($this->request->data['Migration']['name']);
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
		
		$themeMessage = $this->{$migrator}->getThemeMessage();
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