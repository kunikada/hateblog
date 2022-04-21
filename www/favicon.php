<?php
$domain = $_GET['domain'];
if ($domain) {
    $data = apc_fetch($domain);
} else {
    $data = 0;
}
if ($data === false) {
    $ch = curl_init('https://www.google.com/s2/favicons?domain=' . $domain);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    $data = curl_exec($ch);
    curl_close($ch);
    
    if ($data === false) {
	$data = 0;
    } elseif (md5($data) == '3ca64f83fdcf25135d87e08af65e68c9') {
        $data = 0;
    }
    apc_store($domain, $data);
}
if ($data === 0) {
    $data = get_default(); 
}
header('Content-type: image/png');
echo $data;
exit;

function get_default($imagefile = 'default.png') {
    $data = apc_fetch($imagefile);
    if ($data === false) {
        $data = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/images/' . $imagefile);
        apc_store($imagefile, $data);
    }

    return $data;
}
