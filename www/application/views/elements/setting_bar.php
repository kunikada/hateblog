<?php if (strpos($base, 'newlist')): ?>
<span class="barsort barleft">新着順</span>
<a class="barsort barright rightspace" href="<?php echo $sort ?>">人気順</a>
<?php elseif (strpos($base, 'hotlist')): ?>
<a class="barsort barleft" href="<?php echo $sort ?>">新着順</a>
<span class="barsort barright rightspace">人気順</span>
<?php elseif (strpos($base, 'tag') || strpos($base, 'search') || strpos($base, 'twitter')): ?>
    <?php if (strpos($sort, 'new')): ?>
<a class="barsort barleft" href="<?php echo $sort ?>">新着順</a>
<span class="barsort barright rightspace">人気順</span>
    <?php else: ?>
<span class="barsort barleft">新着順</span>
<a class="barsort barright rightspace" href="<?php echo $sort ?>">人気順</a>
    <?php endif ?>
<?php endif ?>
<?php
foreach ($offsets as $key => $value) {
    if ($key == 0) {
        $class_position = 'barleft';
    } elseif ($key == count($offsets) - 1) {
        $class_position = 'barright';
    } else {
        $class_position = 'barmiddle';
    }
    if ($value == $offset) {
        echo "<span class=\"baroffset $class_position\">$value users</span>\n";
    } else {
        echo "<a class=\"baroffset $class_position\" href=\"$base?offset=$value\">$value users</a>\n";
    }
}
?>
