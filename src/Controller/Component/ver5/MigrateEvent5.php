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
 * Class MigrateEvent5
 */
class MigrateEvent5
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
	public function migrate(string $plugin, string $path, bool $is5): void
	{
		$code = file_get_contents($path);
		if(!$is5) {
            $code = MigrateBasic5::addNameSpace($plugin, $path, 'Event', $code);
            $code = preg_replace('/extends BcViewEventListener/', 'extends \BaserCore\Event\BcViewEventListener', $code);
            $code = preg_replace('/extends BcControllerEventListener/', 'extends \BaserCore\Event\BcControllerEventListener', $code);
            $code = preg_replace('/extends BcHelperEventListener/', 'extends \BaserCore\Event\BcHelperEventListener', $code);
            $code = preg_replace('/extends BcModelEventListener/', 'extends \BaserCore\Event\BcModelEventListener', $code);
            $code = preg_replace('/CakeEvent /', '\Cake\Event\Event ', $code);
            $code = preg_replace('/->subject\(\)/', '->getSubject()', $code);
            $code = preg_replace('/->helpers\[] = (.+?);/', "->viewBuilder()->addHelpers([$1]);", $code);
        }
        $code = MigrateBasic5::replaceCode($code, $is5);
		file_put_contents($path, $code);
		$this->log('イベント：' . $path . ' をマイグレーションしました。', LogLevel::INFO, 'migrate_addon');
	}

}
