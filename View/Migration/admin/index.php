<?php
/**
 * [ADMIN] インデックスページ
 */
?>


<div class="panel-box">
	このマイグレーションツールは、あくまでプラグインやテーマについて、baserCMSの新バージョン対応を補助するものです。<br />
	マイグレーションを行なっても確実に動作するというわけではありません。必ず動作確認を行い、適宜対応を行ってください。
</div>

<ul>
	<li><?php $this->BcBaser->link('プラグインマイグレーション', array('action' => 'plugin')) ?></li>
	<li><?php $this->BcBaser->link('テーママイグレーション', array('action' => 'theme')) ?></li>
</ul>

