## Laravel HTTP Background

[![PHPUnit](https://github.com/oliverlundquist/laravel-http-background/actions/workflows/phpunit.yml/badge.svg?branch=master)](https://github.com/oliverlundquist/laravel-http-background/actions/workflows/phpunit.yml)
[![Coverage](https://raw.githubusercontent.com/oliverlundquist/laravel-http-background/refs/heads/image-data/coverage.svg)](https://github.com/oliverlundquist/laravel-http-background/actions/workflows/coverage.yml)
[![PHPStan](https://github.com/oliverlundquist/laravel-http-background/actions/workflows/phpstan.yml/badge.svg?branch=master)](https://github.com/oliverlundquist/laravel-http-background/actions/workflows/phpstan.yml)
[![PHP-CS-Fixer](https://github.com/oliverlundquist/laravel-http-background/actions/workflows/php-cs-fixer.yml/badge.svg?branch=master)](https://github.com/oliverlundquist/laravel-http-background/actions/workflows/php-cs-fixer.yml)

### Windows Users

PowerShell is currently not supported. However, adding support shouldn't be too difficult, since the cURL arguments are mostly the same - just replace -o /dev/null with -o NUL.

I'm not a PowerShell expert, but I believe something like (Start-Job { & command }).Id could be used to retrieve the job ID. You could then check the job status later using Get-Job -Id $id.

If you're interested in contributing PowerShell support, feel free to open a pull request!
