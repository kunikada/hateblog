<div id="top">
	<div class="inner">
		<div id="topleft">
			<h1><a href="/" title="<?php echo $site_name ?>"><img src="/images/hateblog.gif" alt="<?php echo $site_name ?>" width="154" height="24" /></a></h1>
			<p>はてなブックマーク新着エントリーの過去ログサイトです。</p>
		</div>
        <div id="topright">
            <form action="/search/" method="post">
                <input type="text" id="searchtext" name="searchtext" placeholder="検索" <?php if (!empty($search_value)) echo 'value="'.htmlspecialchars($search_value).'" ' ?>/><button type="submit" id="searchbutton"><img src="/images/search.png" width="16" height="16" alt="検索" title="検索" /></button>
            </form>
        </div>
		<br class="clear" />
		<div id="mainmenu">
			<ul>
				<li<?php echo $active == 1 ? ' class="current_page_item"' : '' ?>><a href="/newlist/<?php echo empty($date) ? '' : "{$date}/" ?>" title="新着順リスト - <?php echo $site_name ?>">新着順リスト</a></li>
				<li<?php echo $active == 2 ? ' class="current_page_item"' : '' ?>><a href="/hotlist/<?php echo empty($date) ? '' : "{$date}/" ?>" title="人気順リスト - <?php echo $site_name ?>">人気順リスト</a></li>
				<li<?php echo $active == 3 ? ' class="current_page_item"' : '' ?>><a href="/archive/<?php echo empty($date) ? '' : "{$date}/" ?>" title="アーカイブ - <?php echo $site_name ?>">アーカイブ</a></li>
				<li<?php echo $active == 4 ? ' class="current_page_item"' : '' ?>><a href="/ranking/" title="ランキング - <?php echo $site_name ?>">ランキング</a></li>
				<li<?php echo $active == 5 ? ' class="current_page_item"' : '' ?>><a href="/history/" title="閲覧履歴 - <?php echo $site_name ?>">閲覧履歴</a></li>
<?php if (empty($user_name)): ?>
				<li class="login"><a href="/user/" title="ログイン">ログイン</a></li>
<?php else: ?>
				<li class="logout"><a href="/user/logout/" title="ログアウト (<?php echo $user_name ?>)">ログアウト</a></li>
<?php endif ?>
			</ul>
		</div>
		<br class="clear" />
	</div>
</div>
