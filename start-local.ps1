$ErrorActionPreference = 'Stop'
Set-Location -LiteralPath $PSScriptRoot
Write-Host 'Сайт: http://127.0.0.1:8085'
Write-Host 'Админка: http://127.0.0.1:8085/wp-admin/'
Write-Host 'Для остановки нажмите Ctrl+C. MySQL в XAMPP должен быть запущен.'
& 'C:\xampp\php\php.exe' -d display_errors=0 -S 127.0.0.1:8085 -t $PSScriptRoot "$PSScriptRoot\router.php"
