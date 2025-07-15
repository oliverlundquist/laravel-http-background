<?php

/*

apt-get update -y \
&& apt-get install -y wget \
&& wget https://github.com/PowerShell/PowerShell/releases/download/v7.4.11/powershell_7.4.11-1.deb_amd64.deb \
&& dpkg -i powershell_7.4.11-1.deb_amd64.deb \
&& apt-get install -f \
&& rm powershell_7.4.11-1.deb_amd64.deb \
&& pwsh

shell: C:\Program Files\PowerShell\7\pwsh.EXE -command ". '{0}'"
  env:
    OPENSSL_CONF: C:\Program Files\Common Files\SSL\openssl.cnf
    COMPOSER_PROCESS_TIMEOUT: 0
    COMPOSER_NO_INTERACTION: 1
    COMPOSER_NO_AUDIT: 1
    COMPOSER_AUTH: {"github-oauth": {"github.com": "***"}}


$id = exec('pwsh --command "(curl https://httpbin.org/delay/3 -o NUL -s &).id"');


*/
$output = [];
var_dump(exec('powershell ((curl https://httpbin.org/delay/5 -s &).id)', $output));
var_dump($output);
var_dump(exec('pwsh ((curl https://httpbin.org/delay/5 -s &).id)', $output));
var_dump($output);
var_dump(exec('pwsh.exe ((curl https://httpbin.org/delay/5 -s &).id)', $output));
var_dump($output);


// $id = exec('curl https://httpbin.org/delay/5 &).id');
// var_dump($id);

// for ($i = 0; $i < 4; $i++) {
//     $status = exec('Get-Job -Id ' . $id . '');
//     var_dump($status);
//     sleep(1);
// }
