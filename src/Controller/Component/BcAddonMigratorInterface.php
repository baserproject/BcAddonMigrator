<?php

namespace BcAddonMigrator\Controller\Component;

interface BcAddonMigratorInterface
{
	public function useCakeMigrator();
	
	public function getPluginMessage();
	
	public function getThemeMessage();
	
	public function migratePlugin(string $plugin);
	
	public function migrateTheme(string $theme);
}
