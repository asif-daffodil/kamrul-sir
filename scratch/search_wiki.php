<?php
$url = "https://commons.wikimedia.org/w/api.php?action=query&generator=search&gsrnamespace=6&gsrsearch=stiletto+heels&gsrlimit=5&prop=imageinfo&iiprop=url&format=json";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'HeelBariEcommerce/1.0 (contact@heelbari.com)');
$res = curl_exec($ch);
curl_close($ch);
echo substr($res, 0, 500);
