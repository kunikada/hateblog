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
                <span class="headerright"><?php echo $sub_title ?></span>
            </h2>
<?php echo $pagination ?>
			<div class="clearfix">
<?php foreach ($results as $row): ?>
<?php
    $row['rec'] = 1;
    $row['url'] = ($row['sslp'] ? 'https://' : 'http://') . $row['link'];
    $row['domain'] = array_shift(explode('/', $row['link']));
    $visited = $row['rec'] ? 'visited' : '';
    $onclick = $row['rec'] || is_null($row['rec']) ? '' : " onclick=\"$.get('/ajax/record/{$row['id']}')\"";
?>
                <h3 style="background-image:url(/favicons?domain=<?php echo $row['domain'] ?>)"><a class="<?php echo $visited ?>" href="<?php echo $row['url'] ?>" title="<?php echo htmlspecialchars($row['title']) ?>" target="_blank"<?php echo $onclick ?>><?php echo htmlspecialchars($row['title']) ?></a></h3>
                <p class="timestamp">
                    <span class="datetime"><?php echo $row['entried'] ?></span>
                    <a href="http://b.hatena.ne.jp/add?mode=confirm&amp;title=<?php echo urlencode('title') ?>&amp;url=<?php echo urlencode($row['url']) ?>" target="hatena"><img src="https://b.st-hatena.com/images/entry-button/button-only@2x.png" width="16" height="16" class="append" alt="このエントリーをはてなブックマークに追加" title="このエントリーをはてなブックマークに追加" /></a>
                    <a href="http://b.hatena.ne.jp/entry/<?php echo ($row['sslp'] ? 's/' : '') . str_replace('#', '%23', $row['link']) ?>" title="はてなブックマーク - <?php echo htmlspecialchars($row['title']) ?>" target="hatena"><span class="<?php echo $row['cnt'] < 10 ? 'under' : 'upper' ?>"><?php echo $row['cnt'] ?> users</span></a>
                    <a href="http://www.instapaper.com/hello2?url=<?php echo urlencode($row['url']) ?>&amp;title=<?php echo urlencode($row['title']) ?>" title="Save this for later with Instapaper" target="instapaper"><img src="/images/instapaper.gif" width="16" height="16" class="append" alt="Instapaper" title="Save this for later with Instapaper" /></a>
                    <a href="https://getpocket.com/save?url=<?php echo urlencode($row['url']) ?>&amp;title=<?php echo urlencode($row['title']) ?>" title="Save to Read It Later" target="pocket"><img src="/images/pocket.gif" width="16" height="16" class="append" alt="Pocket" title="Save to Read It Later" /></a>
                    <a href="https://twitter.com/share?url=<?php echo urlencode($row['url']) ?>&amp;text=<?php echo urlencode($row['title']) ?>" title="Tweet" target="twitter"><img src="/images/tweet.gif" width="16" height="16" class="append" alt="Tweet" title="Tweet" /></a>
                    <a href="http://www.facebook.com/sharer.php?u=<?php echo urlencode($row['url']) ?>&amp;t=<?php echo urlencode($row['title']) ?>" title="Facebook Share" target="facebook"><img src="/images/facebook.gif" width="16" height="16" class="append" alt="Facebook Share" /></a>
                    <a href="http://www.evernote.com/clip.action?url=<?php echo urlencode($row['url']) ?>&amp;title=<?php echo urlencode($row['title']) ?>" title="Evernote Clip" target="evernote"><img src="/images/evernote.png" width="16" height="16" class="append" alt="Evernote Clip" /></a>
<?php foreach ($keyphrases[$row['id']] as $word): ?>
                    <a href="/tag/<?php echo rawurlencode($word) ?>/" title="<?php echo "タグ「{$word}」 - {$site_name}" ?>" class="tagword"><?php echo $word ?></a>
<?php endforeach ?>
                </p>
<?php if ($row['screenshot']): ?>
                <a href="<?php echo $row['url'] ?>"><img class="screenshot" src="<?php echo $row['screenshot'] ?>" width=100" height="75" alt=""></a>
<?php endif ?>
                <p class="framed<?php if ($row['screenshot']) echo ' withscreenshot' ?>"><?php echo htmlspecialchars($row['description']) ?> <a href="<?php echo $row['url'] ?>" title="<?php echo htmlspecialchars($row['title']) ?>" class="continued <?php echo $visited ?>"<?php echo $onclick ?>>続きを読む</a></p>
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
