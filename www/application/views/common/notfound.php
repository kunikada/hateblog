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
			<div class="clearfix">
                <p>お客様がお書きになったURL（アドレス）は、現在使われておりません。<br />内容をお確かめになって、もう一度お書き直しください。</p>
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
