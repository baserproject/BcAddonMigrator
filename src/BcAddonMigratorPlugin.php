<?php
declare(strict_types=1);
/**
 * baserCMS :  Based Website Development Project <https://basercms.net>
 * Copyright (c) NPO baser foundation <https://baserfoundation.org/>
 *
 * @copyright     Copyright (c) NPO baser foundation
 * @link          https://basercms.net baserCMS Project
 * @since         5.0.7
 * @license       https://basercms.net/license/index.html MIT License
 */

namespace BcAddonMigrator;

use BaserCore\BcPlugin;

/**
 * plugin for BcAddonMigrator
 */
class BcAddonMigratorPlugin extends BcPlugin
{
    /**
     * console
     *
     * @param \Cake\Console\CommandCollection $commands
     * @return \Cake\Console\CommandCollection
     */
    public function console(\Cake\Console\CommandCollection $commands): \Cake\Console\CommandCollection
    {
        $commands->add('bc_addon_migrator', \BcAddonMigrator\Command\BcAddonMigratorCommand::class);
        return parent::console($commands);
    }
}

