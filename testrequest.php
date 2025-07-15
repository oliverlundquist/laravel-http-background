<?php echo exec('(curl https://httpbin.org/delay/5 -s -o NUL &) | select Id');?>
