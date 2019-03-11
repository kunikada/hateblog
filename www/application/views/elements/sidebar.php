<?php if (!empty($newlist)): ?>
<div class="rounded">
<h2>本日の新着エントリー</h2>
<ol>
<?php foreach ($newlist as $i => $row): ?>
    <li class="digit<?php echo ++$i ?>"><a <?php echo in_array($row['id'], $tmpid) ? 'class="visited" ' : '' ?>href="<?php echo $row['url'] ?>" title="<?php echo htmlspecialchars($row['title']) ?>" target="_blank"<?php echo in_array($row['id'], $tmpid) || is_null($user_id) ? '' : " onclick=\"$.get('/ajax/record/{$row['id']}')\"" ?>><?php echo htmlspecialchars($row['title']) ?></a></li>
<?php endforeach ?>
</ol>
<p class="continued"><a href="/newlist/?offset=5" title="新着順リスト - はてブログ">もっと見る &raquo;</a></p>
</div>
<?php endif ?>
<?php if (!empty($hotlist)): ?>
<div class="rounded">
<h2>本日の人気エントリー</h2>
<ol>
<?php foreach ($hotlist as $i => $row): ?>
    <li class="crown<?php echo ++$i ?>digit"><a <?php echo in_array($row['id'], $tmpid) ? 'class="visited" ' : '' ?>href="<?php echo $row['url'] ?>" title="<?php echo htmlspecialchars($row['title']) ?>" target="_blank"<?php echo in_array($row['id'], $tmpid) || is_null($user_id) ? '' : " onclick=\"$.get('/ajax/record/{$row['id']}')\"" ?>><?php echo htmlspecialchars($row['title']) ?></a></li>
<?php endforeach ?>
</ol>
<p class="continued"><a href="/hotlist/?offset=5" title="人気順リスト - はてブログ">もっと見る &raquo;</a></p>
</div>
<?php endif ?>
<?php if (!empty($hotlast)): ?>
<div class="rounded">
<h2>1年前の人気エントリー</h2>
<ol>
<?php foreach ($hotlast as $i => $row): ?>
    <li class="crown<?php echo ++$i ?>digit"><a <?php echo in_array($row['id'], $tmpid) ? 'class="visited" ' : '' ?>href="<?php echo $row['url'] ?>" title="<?php echo htmlspecialchars($row['title']) ?>" target="_blank"<?php echo in_array($row['id'], $tmpid) || is_null($user_id) ? '' : " onclick=\"$.get('/ajax/record/{$row['id']}')\"" ?>><?php echo htmlspecialchars($row['title']) ?></a></li>
<?php endforeach ?>
</ol>
<p class="continued"><a href="/hotlist/<?php echo $lytd ?>/?offset=5" title="人気順リスト <?php echo date('Y年n月j日', strtotime($lytd)) ?> - はてブログ">もっと見る &raquo;</a></p>
</div>
<?php endif ?>
<?php if (!empty($lastweek)): ?>
<div class="rounded">
<h2>先週のランキング</h2>
<ol>
<?php foreach ($lastweek as $i => $row): ?>
    <li class="crown<?php echo ++$i ?>digit"><a <?php echo in_array($row['id'], $tmpid) ? 'class="visited" ' : '' ?>href="<?php echo $row['url'] ?>" title="<?php echo htmlspecialchars($row['title']) ?>" target="_blank"<?php echo in_array($row['id'], $tmpid) || is_null($user_id) ? '' : " onclick=\"$.get('/ajax/record/{$row['id']}')\"" ?>><?php echo htmlspecialchars($row['title']) ?></a></li>
<?php endforeach ?>
</ol>
<p class="continued"><a href="/ranking/<?php echo $lw_href ?>/" title="週間ランキング <?php echo $lw_title ?> - はてブログ">もっと見る &raquo;</a></p>
</div>
<?php endif ?>
<?php if (!empty($kwdlist)): ?>
<div class="rounded">
<h2>人気エントリーにあるタグ</h2>
<ul>
<?php foreach ($kwdlist as $row): ?>
    <li><a class="tagword" href="/tag/<?php echo rawurlencode($row['keyword']) ?>/?offset=5" title="タグ「<?php echo $row['keyword'] ?>」 - はてブログ"><?php echo $row['keyword'] ?></a><?php if (!empty($row['bookmark_cnt'])) echo " ({$row['bookmark_cnt']}件)" ?></li>
<?php endforeach ?>
</ul>
</div>
<?php endif ?>
<?php if (!empty($hothist)): ?>
<div class="rounded">
<h2>最近の注目エントリー</h2>
<ol>
<?php foreach ($hothist as $i => $row): ?>
    <li class="digit<?php echo ++$i ?>"><a <?php echo in_array($row['id'], $tmpid) ? 'class="visited" ' : '' ?>href="<?php echo $row['url'] ?>" title="<?php echo htmlspecialchars($row['title']) ?>" target="_blank"<?php echo in_array($row['id'], $tmpid) || is_null($user_id) ? '' : " onclick=\"$.get('/ajax/record/{$row['id']}')\"" ?>><?php echo htmlspecialchars($row['title']) ?></a></li>
<?php endforeach ?>
</ol>
<p class="continued"><a href="/poplist/" title="最近の注目エントリー - はてブログ">もっと見る &raquo;</a></p>
</div>
<?php endif ?>
<?php if (!empty($taglist)): ?>
<div class="rounded">
<h2>最近の注目タグ</h2>
<ul>
<?php foreach ($taglist as $row): ?>
    <li><a class="tagword" href="/tag/<?php echo rawurlencode($row['keyword']) ?>/?offset=5" title="タグ「<?php echo $row['keyword'] ?>」 - はてブログ"><?php echo $row['keyword'] ?></a><?php if (!empty($row['bookmark_cnt'])) echo " ({$row['bookmark_cnt']}件)" ?></li>
<?php endforeach ?>
</ul>
</div>
<?php endif ?>
