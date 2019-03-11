<?php echo doctype() ?>
<html xmlns="http://www.w3.org/1999/xhtml" lang="ja" xml:lang="ja">
<?php echo $header ?>
<body>
<?php echo $content_header ?>
<div id="wrap">
	<div class="inner">
		<div id="content">
			<h2 class="archiveheader">
                <span class="headerleft"><?php echo $main_title ?></span>
                <span class="headerright"><?php echo mb_strimwidth($sub_title, 0, 36, '...') ?></span>
            </h2>
<?php if (isset($setting_bar)) echo $setting_bar ?>
            <br class="clear" />
			<div class="clearfix">
                <p>お探しのデータは見つかりませんでした。</p>
			</div><!-- /.clearfix -->
                </div><!-- /#content -->
        <div id="sidebar">
<?php echo $sidebar ?>
        </div><!-- /#sidebar -->
    </div><!-- /#inner -->
<br class="clear" />
</div>
<?php echo $content_footer ?>
</body>
</html>
