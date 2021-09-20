<?php echo doctype() ?>
<html xmlns="http://www.w3.org/1999/xhtml" lang="ja" xml:lang="ja">
<?php echo $header ?>
<body>
<?php echo $content_header ?>
<div id="wrap">
	<div class="inner">
		<div id="content">
			<h2 class="archiveheader">アーカイブ</h2>
<?php echo $setting_bar ?>
            <br class="clear" />
			<div class="clearfix">
<?php if (!empty($results)): ?>
<?php $weekday = array('<span class="sunday">日</span>', '月', '火', '水', '木', '金', '<span class="saturday">土</span>') ?>
<?php foreach ($results as $ym => $data): ?>
<?php if (!isset($yyyy) || $yyyy != substr($ym, 0, 4)): ?>
<?php if (isset($yyyy)): ?>
                </ul>
<?php endif ?>
                <h3><?php echo $yyyy = substr($ym, 0, 4) ?>年</h3>
                <ul class="level1">
<?php endif ?>
                    <li class="clickable" onclick="$('#archive<?php echo $ym ?>').toggle(250)"><?php echo date('Y年n月', strtotime($ym . '01')) . " ({$data['total']}件)" ?></li>
                    <ul class="framed" id="archive<?php echo $ym ?>" style="display:<?php echo substr($str_date, 0, 6) == $ym ? 'block' : 'none';?>">
<?php foreach ($data['lists'] as $ymd => $cnt): ?>
                        <li><?php printf('%d日 (%s) [%d件]', substr($ymd, -2, 2), $weekday[date('w', strtotime($ymd))], $cnt) ?> <a href="<?php echo "/newlist/$ymd/?offset=$offset" ?>" title="新着順リスト <?php echo date('Y年n月j日', strtotime($ymd)) ?> - <?php echo $site_name ?>">新着順リスト</a> <a href="<?php echo "/hotlist/$ymd/?offset=$offset" ?>" title="人気順リスト <?php echo date('Y年n月j日', strtotime($ymd)) ?> - <?php echo $site_name ?>">人気順リスト</a></li>
<?php endforeach ?>
                    </ul>
<?php endforeach ?>
                </ul>
<?php endif ?>
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
