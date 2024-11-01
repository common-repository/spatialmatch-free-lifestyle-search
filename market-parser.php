<?php
$srcFile = __DIR__ . '/config/market-list-for-parse.json';
$dstFile = __DIR__ . '/config/market-list-config.json';

$fileJson = file_get_contents($srcFile);

$marketsArray = json_decode($fileJson);

$marketsResult = [];

foreach ($marketsArray as $item) {
    echo $item;

    preg_match('/(.*) - (.*) \[(.*)\]/simu', $item, $outputArray);

    $market = [
            'state' => $outputArray[1],
            'id' => $outputArray[3],
            'name' => $outputArray[2],
    ];

    $marketsResult[] = $market;
}

file_put_contents($dstFile, json_encode($marketsResult));
