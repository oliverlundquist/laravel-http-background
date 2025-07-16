<?php

$cloverXmlFile  = simplexml_load_file(__DIR__.'/clover.xml');
$fileBlocks     = $cloverXmlFile->xpath('/coverage/project/package/file');
$uncoveredLines = 0;

foreach ($fileBlocks as $fileBlock) {
    $file  = (string) $fileBlock->class->attributes()->name;
    $lines = $fileBlock->xpath('line');
    foreach ($lines as $line) {
        if (intval(strval($line->attributes()->count)) === 0) {
            echo 'Uncovered Line in ' . $file . ' on line: ' . strval($line->attributes()->num) . PHP_EOL;
            $uncoveredLines = $uncoveredLines + 1;
        }
    }
}

if ($uncoveredLines > 0) {
    exit(1);
}
exit(0);
