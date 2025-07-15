<?php


var_dump(exec('which powershell'));
var_dump(exec('which powershell.exe'));
var_dump(exec('powershell Get-Job'));

// $id = exec('curl https://httpbin.org/delay/5 &).id');
// var_dump($id);

// for ($i = 0; $i < 4; $i++) {
//     $status = exec('Get-Job -Id ' . $id . '');
//     var_dump($status);
//     sleep(1);
// }
