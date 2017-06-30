<?php $o = $this ?>

<div class="wrap">

	<?php if ( $o->message ) { ?>
	<div class="updated fade">
		<p><strong><?php esc_html_e( $o->message ); ?></strong></p>
	</div>
	<?php } ?>

	<div id="icon-options-general" class="icon32"><br /></div>

	<h2>基本設定</h2>

	<form action="" method="post">
		<?php wp_nonce_field( 'mj_cwc_setting_check' ) ?>

		<h3>基本設定</h3>
		<table class="form-table">
			<tr valign="top">
				<th class="indent">キャッシュ避けクエリ自動挿入</th>
				<td>下記コードを挿入<br><?php echo esc_html( "<?php echo file_date( get_template_directory() . '/style.css' ); ?>", ENT_QUOTES, 'UTF-8' ) ?></td>
			</tr>
			<tr valign="top">
				<th class="indent">投稿者別アーカイブの出力停止</th>
				<td><?php $o->the_yes_no_html( 'no_author_archive', '1', 'selected' ) ?></td>
			</tr>
			<tr valign="top">
				<th class="indent">投稿画面の自動Pタグ出力停止</th>
				<td><?php $o->the_yes_no_html( 'no_editor_ptag', '1', 'selected' ) ?></td>
			</tr>
		</table>

		<h3>メディア設定</h3>
		<table class="form-table">
			<tr valign="top">
				<th class="indent">width768pxのサムネイル出力停止</th>
				<td><?php $o->the_yes_no_html( 'no_768Images', '1', 'selected' ) ?></td>
			</tr>
		</table>

		<h3>Google Analytics設定</h3>
		<table class="form-table">
			<tr valign="top">
				<th class="indent">Google Analytics ID</th>
				<td><?php $o->the_input_html( 'ga_id' ) ?></td>
			</tr>
		</table>

		<h3>管理画面設定</h3>
		<table class="form-table">
			<tr valign="top">
				<th class="indent">FooterのWordPressリンクを削除</th>
				<td><?php $o->the_yes_no_html( 'no_footer_text', '1', 'selected' ) ?></td>
			</tr>
		</table>

		<p class="submit"><input type="submit" class="button-primary" value="変更を保存" /></p>
	</form>
</div>
