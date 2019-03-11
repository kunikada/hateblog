<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php echo meta($meta) ?>
<?php
$title = isset($page_title) ? $page_title : $site_name;
if (!empty($page_title_add)) {
    $title = $page_title_add . ' - ' . $title;
}
?>
<title><?php echo $title ?></title>
<link rel="shortcut icon" href="/images/favicon.ico" />
<?php if ($this->agent->is_smartphone()): ?>
<meta name="viewport" content="width=640px">
<link rel="stylesheet" type="text/css" href="/css/mstyle.css" />
<?php else: ?>
<link rel="stylesheet" type="text/css" href="/css/style.css" />
<?php endif ?>
<?php foreach ($css as $name): ?>
    <?php if (!preg_match('/^https?:/i', $name)) $name = "/css/$name.css" ?>
<link rel="stylesheet" type="text/css" href="<?php echo $name ?>" />
<?php endforeach ?>
<?php if (ENVIRONMENT == 'production'): ?>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-123726537-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-123726537-1');
</script>
<?php endif ?>
</head>
