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

namespace BcAddonMigrator\Controller\Admin;

use BaserCore\Controller\Admin\BcAdminAppController;
use BaserCore\Service\PluginsServiceInterface;
use Cake\Utility\Hash;

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
	public function plugin(PluginsServiceInterface $pluginsService)
	{
		if ($this->getRequest()->is('post')) {
			$this->{$this->migrator}->migratePlugin($this->getRequest()->getData('name'));
			$this->BcMessage->setInfo('プラグイン： ' . $this->getRequest()->getData('name') . ' のマイグレーションが完了しました。');
			$this->redirect(['action' => 'plugin']);
		}
		
		$this->setTitle('baserCMS プラグインマイグレーション');
		$Folder = new \Cake\Filesystem\Folder(BASER_PLUGINS);
		$files = $Folder->read(true, true);
		$plugins = [];
		if (!empty($files[0])) {
			foreach($files[0] as $file) {
				if ($file === 'BcAddonMigrator') continue; 
				$config = include BASER_PLUGINS . $file . DS . 'config.php';
				if(isset($config['type']) && $config['type'] === 'Plugin') {
					$plugins[$file] = $file;
				}
			}
		}
		
		if(isset($this->{$this->migrator})) {
			$pluginMessage = $this->{$this->migrator}->getPluginMessage();
		} else {
			$pluginMessage = [];
		}
		$this->set('pluginMessage', $pluginMessage);
		$this->set('plugins', $plugins);
	}
	
	/**
	 * [ADMIN] テーマのマイグレーション
	 */
	public function theme()
	{
		if ($this->getRequest()->is('post')) {
			$this->{$this->migrator}->migrateTheme($this->getRequest()->getData('name'));
			$this->BcMessage->setInfo('テーマ： ' . $this->getRequest()->getData('name') . ' のマイグレーションが完了しました。');
			$this->redirect(['action' => 'theme']);
		}
		
		$this->setTitle('baserCMS テーママイグレーション');
		$Folder = new \Cake\Filesystem\Folder(BASER_PLUGINS);
		$files = $Folder->read(true, true);
		$themes = [];
		if (!empty($files[0])) {
			foreach($files[0] as $file) {
				if ($file === 'BcDbMigrator') continue; 
				$config = include BASER_PLUGINS . $file . DS . 'config.php';
				if(isset($config['type']) && $config['type'] === 'Theme') {
					$themes[$file] = $file;
				}
			}
		}
		
		if(isset($this->{$this->migrator})) {
			$themeMessage = $this->{$this->migrator}->getThemeMessage();
		} else {
			$themeMessage = [];
		}
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
