<?php
switch ($class) {
    case 'newlist': $subtitle = '新着順リスト'; break;
    case 'hotlist': $subtitle = '人気順リスト'; break;
    case 'history': $subtitle = '閲覧履歴'; break;
    case 'twitter': $subtitle = 'Twitterで話題のエントリー'; break;
    case 'tag': $subtitle = 'タグ'; break;
    case 'search': $subtitle = '検索'; break;
}
// prev
if (!isset($prev) || !$prev) {
    $back = '&nbsp;';
} elseif ($class == 'ranking') {
    if ($type == 3) {
        $back = sprintf('<a href="/ranking/%s/%s/" title="週間ランキング %s - はてブログ">&laquo; 前週ランキング</a>', $prev[0], $prev[1], $prev_title);
    } else {
        if (!$prev[1]) {
            $back = sprintf('<a href="/ranking/%s/" title="はてブ・オブ・ザ・イヤー %d - はてブログ">&laquo; %d年 年間ランキング</a>', $prev[0], $prev[0], $prev[0]);
        } else {
            $back = sprintf('<a href="/ranking/%s/%s/" title="月間ランキング %d年%d月 - はてブログ">&laquo; %d月 月間ランキング</a>', $prev[0], $prev[1], $prev[0], $prev[1], $prev[1]);
        }
    }
} elseif ($class == 'search') {
    $prev = str_replace('/', '/search/?page=', $prev);
    $back = sprintf('<a href="%s" title="%s - はてブログ">&laquo; 前の%d件</a>', $prev, $subtitle, $per_page);
} elseif (strpos($prev, '/')) {
    $prev = str_replace('/', '/?page=', $prev);
    if ($class == 'history') {
        $back = sprintf('<a href="/%s" title="%s - はてブログ">&laquo; 前の%d件</a>', $prev, $subtitle, $per_page);
    } elseif ($class == 'tag') {
        $back = sprintf('<a href="/%s/%s" title="%s「%s」 - はてブログ">&laquo; 前の%d件</a>', $class, $prev, $subtitle, urldecode($word), $per_page);
    } else {
        $back = sprintf('<a href="/%s/%s" title="%s %d年%d月%d日 - はてブログ">&laquo; 前の%d件</a>', $class, $prev, $subtitle, $year, $month, $day, $per_page);
    }
} else {
    $back = sprintf('<a href="/%s/%s/" title="%s %s - はてブログ">&laquo; %s</a>', $class, $prev, $subtitle, date('Y年n月j日', strtotime($prev)), date('Y年n月j日', strtotime($prev)));
}
// next
if (!isset($next) || !$next) {
    $forward = '&nbsp;';
} elseif ($class == 'ranking') {
    if ($type == 3) {
        $forward = sprintf('<a href="/ranking/%s/%s/" title="週間ランキング %s - はてブログ">次週ランキング &raquo;</a>', $next[0], $next[1], $next_title);
    } else {
        if (!$next[1]) {
            $forward = sprintf('<a href="/ranking/%s/" title="はてブ・オブ・ザ・イヤー %d - はてブログ">%d年 年間ランキング &raquo;</a>', $next[0], $next[0], $next[0]);
        } else {
            $forward = sprintf('<a href="/ranking/%s/%s/" title="月間ランキング %d年%d月 - はてブログ">%d月 月間ランキング &raquo;</a>', $next[0], $next[1], $next[0], $next[1], $next[1]);
        }
    }
} elseif ($class == 'search') {
    $next = str_replace('/', '/search/?page=', $next);
    $forward = sprintf('<a href="%s" title="%s - はてブログ">次の%d件 &raquo;</a>', $next, $subtitle, $per_page);
} elseif (strpos($next, '/')) {
    $next = str_replace('/', '/?page=', $next);
    if ($class == 'history') {
        $forward = sprintf('<a href="/%s" title="%s - はてブログ">次の%d件 &raquo;</a>', $next, $subtitle, $per_page);
    } elseif ($class == 'tag') {
        $forward = sprintf('<a href="/%s/%s" title="%s「%s」 - はてブログ">次の%d件 &raquo;</a>', $class, $next, $subtitle, urldecode($word), $per_page);
    } else {
        $forward = sprintf('<a href="/%s/%s" title="%s %d年%d月%d日 - はてブログ">次の%d件 &raquo;</a>', $class, $next, $subtitle, $year, $month, $day, $per_page);
    }
} else {
    $forward = sprintf('<a href="/%s/%s/" title="%s %s - はてブログ">%s &raquo;</a>', $class, $next, $subtitle, date('Y年n月j日', strtotime($next)), date('Y年n月j日', strtotime($next)));
}
$center = isset($center) ? $center : sprintf("%d - %d / %d件", $start, $end, $total);
?>
<div class="postpagesnav">
   	<div class="back"><?php echo $back ?></div>
   	<div class="center">(<?php echo $center ?>)</div>
   	<div class="forward"><?php echo $forward ?></div>
    <br class="clear" />
</div>
