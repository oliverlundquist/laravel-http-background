## Laravel HTTP Background

### Windows Users

PowerShell is currently not supported. However, adding support shouldn't be too difficult, since the cURL arguments are mostly the same - just replace -o /dev/null with -o NUL.

I'm not a PowerShell expert, but I believe something like (Start-Job { & command }).Id could be used to retrieve the job ID. You could then check the job status later using Get-Job -Id $id.

If you're interested in contributing PowerShell support, feel free to open a pull request!
