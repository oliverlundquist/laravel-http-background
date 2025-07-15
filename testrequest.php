<?php


$output = [];
exec('powershell (curl https://httpbin.org/delay/5 -s &).id)', $output);
var_dump($output);
var_dump(exec('which pwsh'));
var_dump(exec('which pwsh.exe'));

// $id = exec('curl https://httpbin.org/delay/5 &).id');
// var_dump($id);

// for ($i = 0; $i < 4; $i++) {
//     $status = exec('Get-Job -Id ' . $id . '');
//     var_dump($status);
//     sleep(1);
// }
