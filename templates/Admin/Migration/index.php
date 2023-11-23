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

/**
 * @var \BaserCore\View\BcAdminAppView $this
 */
?>


<section class="bca-section">
	<h2 class="bca-main__heading" data-bca-heading-size="lg">マイグレーションの選択</h2>
</section>

<section class="bca-section">
	<div class="panel-box">
		このマイグレーションツールは、あくまでプラグインやテーマについて、baserCMSの新バージョン対応を補助するものです。<br/>
		マイグレーションを行なっても確実に動作するというわけではありません。必ず動作確認を行い、適宜対応を行ってください。
	</div>
	
	<ul>
		<li><?php $this->BcBaser->link('プラグインマイグレーション', ['action' => 'plugin']) ?></li>
		<li><?php $this->BcBaser->link('テーママイグレーション', ['action' => 'theme']) ?></li>
	</ul>
</section>
