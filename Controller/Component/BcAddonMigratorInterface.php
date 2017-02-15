<?php
interface BcAddonMigratorInterface {
	public function useCakeMigrator();
	public function getPluginMessage();
	public function getThemeMessage();
	public function migratePlugin($plugin);
	public function migrateTheme($theme);
}