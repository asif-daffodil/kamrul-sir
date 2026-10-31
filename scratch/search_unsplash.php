<?php

function searchUnsplash($term) {
    $url = "https://unsplash.com/napi/search/photos?query=" . urlencode($term) . "&per_page=8";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    $json = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($json, true);
    $res = [];
    if (!empty($data['results'])) {
        foreach ($data['results'] as $r) {
            $res[] = [
                'id' => $r['id'],
                'desc' => $r['alt_description'] ?? 'photo',
                'url' => $r['urls']['regular'] ?? '',
            ];
        }
    }
    return $res;
}

print_r(searchUnsplash('women shoes isolated'));
