<?php

const lineLimit = 80;
const chunkSize = 32768;
const chunkAmount = 65536;

$words = file("words.txt");

$content = "";
do {
    $line = "";
    do {
        $line .= trim($words[array_rand($words)]) . " ";
    } while(strlen($line) < lineLimit);
    $content .= trim($line) . "\n";
} while(strlen($content) < chunkSize);

$handle = fopen("text.txt", "w");
$chunkAmount = 0;
do {
    fwrite($handle, $content);
    $chunkAmount++;
    echo $chunkAmount . "\n";
} while($chunkAmount < chunkAmount);
fclose($handle);
