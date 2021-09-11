<div id="footer">
	<div class="inner">
		<p><span class="credits"><a href="<?php echo base_url() ?>" title="<?php echo $site_name ?>">はてブログ</a> | <a href="<?php echo base_url('newlist') ?>/" title="新着順リスト - <?php echo $site_name ?>">新着順リスト</a> | <a href="<?php echo base_url('hotlist') ?>/" title="人気順リスト - <?php echo $site_name ?>">人気順リスト</a> | <a href="<?php echo base_url('archive') ?>/" title="アーカイブ - <?php echo $site_name ?>">アーカイブ</a> | <a href="<?php echo base_url('ranking') ?>/" title="ランキング - <?php echo $site_name ?>">ランキング</a> | <a href="https://twitter.com/intent/tweet?screen_name=_hateblog" title="Tweet to @_hateblog">問い合わせ</a></span></p>
			<div class="fleft"><p>&nbsp;</p></div>
            <div class="fright"><p>&nbsp;</p></div>
			<div class="fcenter"><p>&copy; 2011 <a href="https://twitter.com/_hateblog/" title="はてブログ on Twitter">@_hateblog</a> Powered by <a href="http://b.hatena.ne.jp/" title="はてなブックマーク">はてなブックマーク</a> Analytical Web Services by <a href="http://developer.yahoo.co.jp/about" title="Web Services by Yahoo! JAPAN">Yahoo! JAPAN</a></p></div>
	</div>
</div>
<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<?php if ($this->agent->is_browser('Internet Explorer') && $this->agent->version() < 9): ?>
<script type="text/javascript" src="/js/curvycorners.js"></script>
<?php endif ?>
<?php foreach ($js as $name): ?>
    <?php if (!preg_match('/^https?:/i', $name)) $name = "/js/$name.js" ?>
<script type="text/javascript" src="<?php echo $name ?>"></script>
<?php endforeach ?>
