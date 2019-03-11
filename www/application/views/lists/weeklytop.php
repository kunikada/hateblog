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
			<div class="clearfix">
<?php $data1 = $results[$year] ?>
                <h3><a class="crown1" href="/ranking/<?php echo $year ?>/" title="はてブ・オブ・ザ・イヤー <?php echo $year ?> - <?php echo $site_name ?>">はてブ・オブ・ザ・イヤー <?php echo $year ?></a></h3>
			    <div class="clearfix">
<?php foreach (array(3, 5, 10) as $i): ?>
<?php if ($i == $top_count): ?>
                    <span class="barsolo">週間ランキング TOP <?php echo $i ?></span>
<?php else: ?>
                    <a class="barsolo" href="/ranking/weeklytop<?php echo $i ?>/<?php echo $year ?>">週間ランキング TOP <?php echo $i ?></a>
<?php endif ?>
<?php endforeach ?>
                </div>
                <ul class="level1">
<?php foreach ($data1['lists1'] as $mm => $data2): ?>
<?php foreach ($data2['lists2'] as $yw => $drange): ?>
                    <li><a href="/ranking/<?php echo "{$year}/{$mm}/{$yw}" ?>/" title="週間ランキング <?php echo "{$year}年{$drange}" ?> - <?php echo $site_name ?>">週間ランキング (<?php echo $drange ?>)</a></li>
                    <ul class="level2">
<?php foreach ($bookmarks[$yw] as $row): ?>
                        <li>
                            <a href="<?php echo ($row['sslp']?'https://':'http://').$row['link'] ?>" title="<?php echo htmlspecialchars($row['title']) ?>" target="_blank" onclick="$.get('/ajax/record/<?php echo $row['id'] ?>')"><?php echo htmlspecialchars(mb_strimwidth($row['title'], 0, 78, '...')) ?></a>
                            <a href="http://b.hatena.ne.jp/entry/<?php echo ($row['sslp'] ? 's/' : '') . str_replace('#', '%23', $row['link']) ?>" title="はてなブックマーク - <?php echo htmlspecialchars($row['title']) ?>" target="hatena"><span class="upper"><?php echo $row['cnt'] ?> users</span></a>
                        </li>
<?php endforeach ?>
                    </ul>
<?php endforeach ?>
<?php endforeach ?>
            </div><!-- /.clearfix -->
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
