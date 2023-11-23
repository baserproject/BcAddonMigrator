<?php
/**
 * baserCMS :  Based Website Development Project <https://basercms.net>
 * Copyright (c) NPO baser foundation <https://baserfoundation.org/>
 *
 * @copyright     Copyright (c) NPO baser foundation
 * @link          https://basercms.net baserCMS Project
 * @since         5.0.0
 * @license       https://basercms.net/license/index.html MIT License
 */

return [
    'type' => 'Plugin',
    'title' => __d('baser_core', 'baserCMSアドオンマイグレーター'),
    'description' => __d('baser_core', 'baserCMSのテーマやプラグインを新しいバージョンに対応させる為の補助を行う為のプラグイン'),
    'author' => 'baserCMS User Community',
    'url' => 'https://basercms.net',
    'adminLink' => ['plugin' => 'BcAddonMigrator', 'controller' => 'Migration', 'action' => 'index']
];
