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
use BaserCore\Utility\BcZip;
use Cake\Filesystem\File;

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
            $this->BcMessage->setError('このプラグインは、このバージョンのbaserCMSに対応していません。');
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
            $plugin = $this->{$this->migrator}->migratePlugin($this->getRequest()->getData('name'));
            if ($plugin) {
                $this->getRequest()->getSession()->write('BcAddonMigrator.file', $plugin);
                $this->getRequest()->getSession()->delete('BcAddonMigrator.downloaded');
                $this->BcMessage->setInfo('プラグイン： ' . $plugin . ' のマイグレーションが完了しました。');
            } else {
                $this->BcMessage->setError('プラグインのマイグレーションが失敗しました。');
            }
            $this->redirect(['action' => 'plugin']);
        }

        if ($this->getRequest()->getSession()->read('BcAddonMigrator.downloaded')) {
            $this->getRequest()->getSession()->delete('BcAddonMigrator.file');
            $this->getRequest()->getSession()->delete('BcAddonMigrator.downloaded');
            $Folder = new \Cake\Filesystem\Folder(TMP_ADDON_MIGRATOR);
            $Folder->delete();
        }

        $this->setTitle('baserCMS プラグインマイグレーション');
        if (isset($this->{$this->migrator})) {
            $pluginMessage = $this->{$this->migrator}->getPluginMessage();
        } else {
            $pluginMessage = [];
        }
        $this->set('pluginMessage', $pluginMessage);
        $file = new \BaserCore\Utility\BcFile(LOGS . 'migrate_addon.log', true);
        $this->set('log', $file->read());
    }

    /**
     * [ADMIN] テーマのマイグレーション
     */
    public function theme()
    {
        if ($this->getRequest()->is('post')) {
            $theme = $this->{$this->migrator}->migrateTheme($this->getRequest()->getData('name'));
            if ($theme) {
                $this->getRequest()->getSession()->write('BcAddonMigrator.file', $theme);
                $this->getRequest()->getSession()->delete('BcAddonMigrator.downloaded');
                $this->BcMessage->setInfo('テーマ： ' . $theme . ' のマイグレーションが完了しました。');
            } else {
                $this->BcMessage->setError('テーマのマイグレーションが失敗しました。');
            }
            $this->redirect(['action' => 'theme']);
        }

        if ($this->getRequest()->getSession()->read('BcAddonMigrator.downloaded')) {
            $this->getRequest()->getSession()->delete('BcAddonMigrator.file');
            $this->getRequest()->getSession()->delete('BcAddonMigrator.downloaded');
            $Folder = new \BaserCore\Utility\BcFolder(TMP_ADDON_MIGRATOR);
            $Folder->delete();
        }

        $this->setTitle('baserCMS テーママイグレーション');
        if (isset($this->{$this->migrator})) {
            $themeMessage = $this->{$this->migrator}->getThemeMessage();
        } else {
            $themeMessage = [];
        }
        $this->set('themeMessage', $themeMessage);
        $file = new \BaserCore\Utility\BcFile(LOGS . 'migrate_addon.log', true);
        $this->set('log', $file->read());
    }

    /**
     * ダウンロード
     */
    public function download()
    {
        $this->autoRender = false;
        $fileName = $this->getRequest()->getSession()->read('BcAddonMigrator.file');
        if (!$fileName || !is_dir(TMP_ADDON_MIGRATOR)) {
            $this->notFound();
        }
        // ZIP圧縮
        $distPath = TMP . $fileName . '.zip';

        $bcZip = new BcZip();
        $bcZip->create(TMP_ADDON_MIGRATOR . $fileName, $distPath);
        header("Cache-Control: no-store");
        header("Content-Type: application/zip");
        header("Content-Disposition: attachment; filename=" . basename($distPath) . ";");
        header("Content-Length: " . filesize($distPath));
        while(ob_get_level()) {
            ob_end_clean();
        }
        echo readfile($distPath);

        // ダウンロード
        $Folder = new \Cake\Filesystem\Folder();
        $Folder->delete(TMP_ADDON_MIGRATOR);
        $this->getRequest()->getSession()->write('BcAddonMigrator.downloaded', true);
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
