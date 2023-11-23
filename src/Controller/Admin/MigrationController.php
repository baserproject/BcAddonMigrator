<?php

namespace BcAddonMigrator\Controller\Admin;
 
use BaserCore\Controller\Admin\BcAdminAppController;

/**
 * MigrationController
 */
class MigrationController extends BcAdminAppController
{
	
	/**
	 * マイグレーター名
	 *
	 * @var null
	 */
	public $migrator = null;
	
	/**
	 * beforeFilter
	 */
	public function beforeFilter(\Cake\Event\EventInterface $event)
	{
		parent::beforeFilter($event);
		$this->migrator = 'BcAddonMigrator' . $this->getMajorVersion();
		$migratorClass = '\\BcAddonMigrator\\Controller\\Component\\' . $this->migrator . 'Component';
		if (class_exists($migratorClass)) {
			$this->loadComponent('BcAddonMigrator.' . $this->migrator);
		} else {
			$this->BcMessage->setWarning('このプラグインは、このバージョンのbaserCMSに対応していません。');
		}
	}
	
	/**
	 * [ADMIN] インデックスページ
	 */
	public function index()
	{
		$this->setTitle('baserCMS アドオンマイグレーター');
	}
	
	/**
	 * [ADMIN] プラグインのマイグレーション
	 */
	public function plugin()
	{
		
		$useCakeMigrator = $this->{$this->migrator}->useCakeMigrator();
		if ($this->request->data) {
			if ($this->{$this->migrator}->useCakeMigrator()) {
				$this->{$this->migrator}->migratePluginByCake($this->request->data['Migration']['name'], $this->request->data['Migration']['php']);
			}
			$this->{$this->migrator}->migratePlugin($this->request->data['Migration']['name']);
			$this->setMessage('プラグイン： ' . $this->request->data['Migration']['name'] . ' のマイグレーションが完了しました。');
			$this->redirect('plugin');
		}
		
		$this->pageTitle = 'baserCMS プラグインマイグレーション';
		$Folder = new \BcAddonMigrator\Controller\Folder(APP . 'Plugin');
		$files = $Folder->read(true, true, false);
		$plugins = [];
		if (!empty($files[0])) {
			foreach($files[0] as $file) {
				if ($file != 'BcAddonMigrator') {
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
	public function theme()
	{
		
		if ($this->request->data) {
			$this->{$this->migrator}->migrateTheme($this->request->data['Migration']['name']);
			$this->setMessage('テーマ： ' . $this->request->data['Migration']['name'] . ' のマイグレーションが完了しました。');
			$this->redirect('theme');
		}
		
		$this->pageTitle = 'baserCMS テーママイグレーション';
		$Folder = new \BcAddonMigrator\Controller\Folder(WWW_ROOT . 'theme');
		$files = $Folder->read(true, true, false);
		$themes = [];
		if (!empty($files[0])) {
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
	public function getMajorVersion()
	{
		return preg_replace('/([0-9])\..+/', "$1", \BaserCore\Utility\BcUtil::getVersion());
	}
	
}
