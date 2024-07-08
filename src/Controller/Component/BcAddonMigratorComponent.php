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

use Cake\Controller\Component;
use Cake\Utility\Inflector;
use Laminas\Diactoros\UploadedFile;
use Psr\Log\LogLevel;

/**
 * Class BcAddonMigratorComponent
 */
class BcAddonMigratorComponent extends Component
{

    /**
     * セットアップ
     * @param array $data
     * @return bool
     */
    protected function setup(UploadedFile $data)
    {
		if(LOGS . 'migrate_addon.log') unlink(LOGS . 'migrate_addon.log');
		if ($data->getError() !== UPLOAD_ERR_OK) {
			return false;
		}
		// アップロードファイルを一時フォルダに解凍
		return $this->_unzipUploadFileToTmp($data);
    }

	/**
	 * アップロードしたファイルを一時フォルダに解凍する
	 *
	 * @param array $data リクエストデータ
	 * @return bool|string
	 */
	protected function _unzipUploadFileToTmp(UploadedFile $data)
	{
		$Folder = new \Cake\Filesystem\Folder();
		$Folder->delete(TMP_ADDON_MIGRATOR);
		$Folder->create(TMP_ADDON_MIGRATOR, 0777);
		$targetPath = TMP_ADDON_MIGRATOR . $data->getClientFilename();
		try {
		    $data->moveTo($targetPath);
        } catch(\Throwable) {
            return false;
        }
		// ZIPファイルを解凍する
		$BcZip = new \BaserCore\Utility\BcZip();
		if (!$BcZip->extract($targetPath, TMP_ADDON_MIGRATOR)) {
			return false;
		}
		$Folder = new \Cake\Filesystem\Folder(TMP_ADDON_MIGRATOR);
		$files = $Folder->read();
		if (empty($files[0])) {
			$this->log('バックアップファイルに問題があります。バージョンが違う可能性があります。', LogLevel::ERROR, 'migrate_addon');
			return false;
		}
		@unlink($targetPath);
		return basename($targetPath, '.zip');
	}

}
