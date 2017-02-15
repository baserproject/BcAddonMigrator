<?php
/**
 * [ADMIN] プラグインマイグレーション
 */
?>


<div class="panel-box">
	<h2>マイグレーション実行</h2>
	<?php echo $this->BcForm->create('Migration') ?>
	<div class="submit" style="text-align:center">
	<?php if($useCakeMigrator): ?>
		<?php echo $this->BcForm->label('Migration.php', 'PHP実行ファイルのパス') ?>
			<?php echo $this->BcForm->input('Migration.php', array('type' => 'input')) ?><br />
			<small>プラグインのマイグレーションでは、内部的にコマンドライン版のPHPを実行します。<br />
				省略した場合は、パスの通っているPHPを実行します。<br />
				別のPHPを実行する場合は絶対パスで指定してください。（例）/usr/local/lib/php</small><br />
	<?php endif ?>
	<?php echo $this->BcForm->input('Migration.name', array('type' => 'select', 'options' => $plugins)) ?>
	<?php echo $this->BcForm->button('実行', array('class' => 'button')) ?>
	</div>
	<?php echo $this->BcForm->end() ?>
</div>

<?php if($pluginMessage): ?>
<div class="panel-box">
	<h2>手動で作業が必要な事項</h2>
	<ul>
		<li><?php echo implode('</li><li>', $pluginMessage) ?></li>
	</ul>
</div>
<?php endif ?>