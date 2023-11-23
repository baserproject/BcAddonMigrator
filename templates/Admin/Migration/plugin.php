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
 * @var array $plugins
 * @var array $pluginMessage
 */
?>


<section class="bca-panel">
	<div class="bca-panel-box">
		<h2 class="bca-main__heading" data-bca-heading-size="lg">プラグインマイグレーション実行</h2>
	  <?php echo $this->BcAdminForm->create() ?>
		<div class="submit" style="text-align:center">
		<?php echo $this->BcAdminForm->control('name', ['type' => 'select', 'options' => $plugins]) ?>
		<?php echo $this->BcAdminForm->button('実行', [
				'class' => 'bca-btn bca-loading',
				'data-bca-btn-size' => 'lg',
				'data-bca-btn-width' => 'lg',
				'data-bca-btn-type' => 'save',
		]) ?>
		</div>
	  <?php echo $this->BcAdminForm->end() ?>
	</div>
	
	<?php if ($pluginMessage): ?>
			<div class="bca-panel-box">
				<h2 class="bca-main__heading" data-bca-heading-size="lg">手動で作業が必要な事項</h2>
				<div class="bca-update-log">
				<ul class="bca-update-log__list">
					<li class="bca-update-log__list-item"><?php echo implode('</li><li>', $pluginMessage) ?></li>
				</ul>
				</div>
			</div>
	<?php endif ?>
</section>
