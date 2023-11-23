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

/**
 * BcAddonMigratorInterface
 */
interface BcAddonMigratorInterface
{
	
	/**
	 * getPluginMessage
	 * @return mixed
	 */
	public function getPluginMessage(): array;
	
	/**
	 * getThemeMessage
	 * @return mixed
	 */
	public function getThemeMessage(): array;
	
	/**
	 * migratePlugin
	 * @param string $plugin
	 * @return mixed
	 */
	public function migratePlugin(string $plugin): void;
	
	/**
	 * migrateTheme
	 * @param string $theme
	 * @return mixed
	 */
	public function migrateTheme(string $theme): void;
	
}
