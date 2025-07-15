<?php


$id = exec('curl https://httpbin.org/delay/5 &).id');
var_dump($id);

for ($i = 0; $i < 4; $i++) {
    $status = exec('Get-Job -Id ' . $id . '');
    var_dump($status);
    sleep(1);
}
