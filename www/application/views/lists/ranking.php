<?php echo doctype() ?>
<html xmlns="http://www.w3.org/1999/xhtml" lang="ja" xml:lang="ja">
	<?php echo $header ?>
	<body>
		<?php echo $content_header ?>
		<div id="wrap">
			<div class="inner">
				<div id="content">
					<h2 class="archiveheader">ランキング</h2>
					<div class="clearfix">
						<?php if (!empty($results)): ?>
							<?php foreach ($results as $yyyy => $data1): ?>
								<h3><a class="crown1" href="/ranking/<?php echo $yyyy ?>/" title="はてブ・オブ・ザ・イヤー <?php echo $yyyy ?> - <?php echo $site_name ?>">はてブ・オブ・ザ・イヤー <?php echo $yyyy ?></a></h3>
								<div class="clearfix">
									<a class="barsolo" href="/ranking/monthlytop5/<?php echo $yyyy ?>">月間ランキング TOP 5</a>
									<a class="barsolo" href="/ranking/weeklytop3/<?php echo $yyyy ?>">週間ランキング TOP 3</a>
								</div>
								<ul class="level1">
									<?php foreach ($data1['lists1'] as $mm => $data2): ?>
										<li><a href="/ranking/<?php echo "{$yyyy}/{$mm}" ?>/" title="月間ランキング <?php echo "{$yyyy}年{$mm}月" ?> - <?php echo $site_name ?>"><?php echo $mm ?>月 月間ランキング</a></li>
										<ul class="level2">
											<?php foreach ($data2['lists2'] as $yw => $drange): ?>
												<li><a href="/ranking/<?php echo "{$yyyy}/{$mm}/{$yw}" ?>/" title="週間ランキング <?php echo "{$yyyy}年{$drange}" ?> - <?php echo $site_name ?>">週間ランキング (<?php echo $drange ?>)</a></li>
											<?php endforeach ?>
										</ul>
									<?php endforeach ?>
								</ul>
							<?php endforeach ?>
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
