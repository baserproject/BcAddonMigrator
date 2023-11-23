<?php
/**
 * [ADMIN] インデックスページ
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
