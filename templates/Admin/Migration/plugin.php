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
 * @var string $log
 */
?>

<p>利用方法については、<a href="https://baserproject.github.io/5/plugin/migration_plugin_from_ver4" target="_blank">baserCMS４のプラグインを変換</a>をご覧ください。
<?php echo $this->BcAdminForm->create(null, ['type' => 'file']) ?>

<section class="bca-section">
	<table class="bca-form-table" id="ListTable">
		<tr>
			<th class="bca-form-table__label">
		  <?php echo $this->BcAdminForm->label('name', 'プラグインアップロード') ?>
			</th>
			<td class="bca-form-table__input">
		  <?php echo $this->BcAdminForm->control('name', ['type' => 'file']) ?>
		  <?php echo $this->BcAdminForm->error('name') ?>
			</td>
		</tr>
	</table>
</section>

<section class="bca-actions">
  <div class="bca-actions__main">
    <?php echo $this->BcHtml->link(__d('baser_core', '一覧に戻る'),
      ['action' => 'index'], [
        'class' => 'button bca-btn bca-actions__item',
        'data-bca-btn-type' => 'back-to-list'
      ]) ?>
    <?php echo $this->BcAdminForm->button('実行', [
      'class' => 'bca-btn bca-loading',
      'data-bca-btn-size' => 'lg',
      'data-bca-btn-width' => 'lg',
      'data-bca-btn-type' => 'save',
    ]) ?>
  </div>
	<div class="bca-actions__sub">
	  <?php if ($this->getRequest()->getSession()->read('BcAddonMigrator.file')): ?>
				　<?php $this->BcBaser->link('ダウンロード', ['action' => 'download'], ['class' => 'bca-btn']) ?>
	  <?php endif ?>
	</div>
</section>

<?php echo $this->BcAdminForm->end() ?>

<section class="bca-section">
  <h2 class="bca-main__heading" data-bca-heading-size="lg">マイグレーションログ</h2>
</section>

<section class="bca-section">
	<?php echo $this->BcAdminForm->control('log', [
		'type' => 'textarea',
		'rows' => 10,
		'value' => $log,
		'readonly' => 'readonly',
	]) ?>
</section>

<?php if ($pluginMessage): ?>
  <section class="bca-section">
    <h2 class="bca-main__heading" data-bca-heading-size="lg">手動で作業が必要な事項</h2>
    <div class="bca-update-log">
      <ul class="bca-update-log__list">
        <li class="bca-update-log__list-item"><?php echo implode('</li><li>', $pluginMessage) ?></li>
      </ul>
    </div>
  </section>
<?php endif ?>
