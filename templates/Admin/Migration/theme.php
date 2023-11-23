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


<div class="panel-box">
	<h2>マイグレーション実行</h2>
	<?php echo $this->BcForm->create('Migration') ?>

	<div class="submit" style="text-align:center">
		<?php echo $this->BcForm->input('Migration.name', array('type' => 'select', 'options' => $themes)) ?><?php echo $this->BcForm->button('実行', array('class' => 'button')) ?>
	</div>

	<?php echo $this->BcForm->end() ?>
</div>

<?php if($themeMessage): ?>
<div class="panel-box">
	<h2>手動で作業が必要な事項</h2>
	<ul>
		<li><?php echo implode('</li><li>', $themeMessage) ?></li>
	</ul>
</div>
<?php endif ?>
