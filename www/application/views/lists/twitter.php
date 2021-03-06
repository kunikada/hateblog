<?php echo doctype() ?>
<html xmlns="http://www.w3.org/1999/xhtml" lang="ja" xml:lang="ja">
<?php echo $header ?>
<body>
<?php echo $content_header ?>
<div id="wrap">
	<div class="inner">
		<div id="content">
			<h2 class="archiveheader">
                <span class="headerleft"><?php echo $main_title; ?></span>
                <span class="headerright"><?php echo $sub_title; ?></span>
            </h2>
<?php echo $setting_bar ?>
<?php echo $pagination ?>
			<div class="clearfix">
<?php foreach ($results as $row): ?>
<?php
    $row['url'] = ($row['sslp'] ? 'https://' : 'http://') . $row['link'];
    $row['domain'] = array_shift(explode('/', $row['link']));
    $visited = $row['rec'] ? 'visited' : '';
    $onclick = $row['rec'] || is_null($row['rec']) ? '' : " onclick=\"$.get('/ajax/record/{$row['id']}')\"";
?>
                <h3 style="background-image:url(/favicons?domain=<?php echo $row['domain'] ?>)"><a class="<?php echo $visited ?>" href="<?php echo $row['url'] ?>" title="<?php echo htmlspecialchars_decode($row['title']) ?>" target="_blank"<?php echo $onclick ?>><?php echo htmlspecialchars_decode($row['title']) ?></a></h3>
                <p class="timestamp">
                    <span class="datetime"><?php echo $row['entried'] ?></span>
                    <a href="https://twitter.com/share?url=<?php echo urlencode($row['url']) ?>&amp;text=<?php echo urlencode($row['title']) ?>" title="Tweet" target="twitter"><img src="/images/tweet.gif" width="16" height="16" class="append" alt="Tweet" title="Tweet" /></a>
                    <a href="http://twitter.com/search?q=<?php echo urlencode($row['url']) ?>" title="Twitter / 検索 - <?php echo $row['url'] ?>" target="tsearch"><span class="tweets"><?php echo $row['cnt'] ?> users</span></a>
                    <a href="http://www.instapaper.com/hello2?url=<?php echo urlencode($row['url']) ?>&amp;title=<?php echo urlencode($row['title']) ?>" title="Save this for later with Instapaper" target="instapaper"><img src="/images/instapaper.gif" width="16" height="16" class="append" alt="Instapaper" title="Save this for later with Instapaper" /></a>
                    <a href="https://getpocket.com/save?url=<?php echo urlencode($row['url']) ?>&amp;title=<?php echo urlencode($row['title']) ?>" title="Save to Read It Later" target="pocket"><img src="/images/pocket.gif" width="16" height="16" class="append" alt="Pocket" title="Save to Read It Later" /></a>
                    <a href="http://b.hatena.ne.jp/add?mode=confirm&amp;title=<?php echo urlencode($row['title']) ?>&amp;url=<?php echo urlencode($row['url']) ?>" target="hatena"><img src="/images/append.gif" width="16" height="12" class="append" alt="このエントリーをはてなブックマークに追加" title="このエントリーをはてなブックマークに追加" /></a>
                    <a href="http://www.facebook.com/sharer.php?u=<?php echo urlencode($row['url']) ?>&amp;t=<?php echo urlencode($row['title']) ?>" title="Facebook Share" target="facebook"><img src="/images/facebook.gif" width="16" height="16" class="append" alt="Facebook Share" /></a>
                    <a href="http://www.evernote.com/clip.action?url=<?php echo urlencode($row['url']) ?>&amp;title=<?php echo urlencode($row['title']) ?>" title="Evernote Clip" target="evernote"><img src="/images/evernote.png" width="16" height="16" class="append" alt="Evernote Clip" /></a>
                </p>
                <p class="framed"><?php echo htmlspecialchars($row['description']) ?> <a href="<?php echo $row['url'] ?>" title="<?php echo htmlspecialchars($row['title']) ?>" class="continued <?php echo $visited ?>"<?php echo $onclick ?>>続きを読む</a></p>
<?php endforeach ?>
			</div><!-- /.clearfix -->
<?php echo $pagination ?>
		</div><!-- /#content -->
        <div id="sidebar">
<?php echo $sidebar ?>
        </div><!-- /#sidebar -->
        <br class="clear" />
<?php echo $page_top_link ?>
	</div><!-- /#inner -->
</div><!-- /#wrap -->
<?php echo $content_footer ?>
</body>
</html>
