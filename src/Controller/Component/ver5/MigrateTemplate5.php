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

namespace BcAddonMigrator\Controller\Component\ver5;

use Psr\Log\LogLevel;
use Cake\Log\LogTrait;

/**
 * Class MigrateTemplate5
 */
class MigrateTemplate5
{

	/**
	 * Trait
	 */
	use LogTrait;

	/**
	 * マイグレーション
	 * @param string $plugin
	 * @param string $prefix
	 * @param string $path
	 * @return void
	 */
	public function migrate(string $path, bool $is5): void
	{
	    if(strpos($path, '/Admin/') !== false) {
            $isAdmin = true;
        } else {
            $isAdmin = false;
        }
		$code = file_get_contents($path, $isAdmin);
		$code = MigrateBasic5::replaceCode($code, $is5);
		if(!$is5) {
            $code = $this->replace($code);
        }
		file_put_contents($path, $code);
		$this->log('テンプレート：' . $path . ' をマイグレーションしました。', LogLevel::INFO, 'migrate_addon');
	}

    /**
     * 置換処理
     * @param string $code
     * @return string
     */
	public function replace(string $code, $isAdmin = false)
	{
	    if($isAdmin) {
            $code = preg_replace('/\$this->BcForm->/', '$this->BcAdminForm->', $code);
        }
		$code = preg_replace('/\$this->BcAdminForm->input\(/', '$this->BcAdminForm->control(', $code);
		$code = preg_replace('/\$post\[\'BlogPost\'\]\[\'(.+?)\'\]/', '\$post->$1', $code);
		$code = preg_replace('/\$post->posts_date/', '\$post->posted', $code);
		$code = preg_replace('/\$post->name/', '\$post->title', $code);
		$code = preg_replace('/<\?php\s+?\$this->BcBaser->docType\(.+?\)\s+?\?>/', '<!DOCTYPE html>', $code);
		$code = preg_replace('/\$this->BcTime->format\((.+?),\s*(.+?)\)/', '\$this->BcTime->format($2, $1)', $code);
		return $code;
	}

}
