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
 * Class MigrateHelper5
 */
class MigrateHelper5
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
	    if(in_array(basename($path), \Cake\Core\Configure::read('BcAddonMigrator.ignoreFiles'))) {
            return;
        }
	    $code = file_get_contents($path);
	    if(!$is5) {
            $code = MigrateBasic5::addNameSpace($plugin, $path, 'View' . DS . 'Helper', $code);
            $code = preg_replace('/extends AppHelper/', 'extends \Cake\View\Helper', $code);
            $code = preg_replace('/__construct\(View/', '__construct(\Cake\View\View', $code);
            $code = preg_replace('/\$this->request/', '$this->getView()->getRequest()', $code);
            $code = preg_replace('/getRequest\(\)->here/', "getRequest()->getAttribute('here')", $code);
            $code = preg_replace('/getRequest\(\)->params/', "getRequest()->getAttribute('params')", $code);
        }
        $code = MigrateBasic5::replaceCode($code, $is5);
        $code = preg_replace('/public[^(\r\n)]+?\$helpers =/', 'protected array $helpers =', $code);
		file_put_contents($path, $code);
		$this->log('ヘルパ：' . $path . ' をマイグレーションしました。', LogLevel::INFO, 'migrate_addon');
	}

}
