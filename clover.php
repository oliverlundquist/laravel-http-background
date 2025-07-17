<?php

$cloverXmlFile  = simplexml_load_file(__DIR__.'/clover.xml');
$fileBlocks     = $cloverXmlFile->xpath('/coverage/project/package/file');
$uncoveredLines = 0;

foreach ($fileBlocks as $fileBlock) {
    $file  = strval($fileBlock->class->attributes()->name);
    $lines = $fileBlock->xpath('line');
    foreach ($lines as $line) {
        if (intval(strval($line->attributes()->count)) === 0) {
            echo 'Uncovered Line in ' . $file . ' on Line: ' . strval($line->attributes()->num) . PHP_EOL;
            $uncoveredLines = $uncoveredLines + 1;
        }
    }
}

if ($uncoveredLines === 0) {
    echo 'All Lines of Code are Covered, Great Job!' . PHP_EOL;
}
