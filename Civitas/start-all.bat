@echo off
echo Starting Meilisearch...
start "Meilisearch" cmd /c "cd /d E:\Fears\myProjects\php\Civitas\Civitas && .\meilisearch\meilisearch-windows-amd64.exe --master-key="your-secret-master-key-here" --http-addr="127.0.0.1:7700" --db-path="./data.ms" --env="development""

timeout /t 3 >nul

echo Starting Laravel Queue Worker...
start "Queue Worker" cmd /c "cd /d E:\Fears\myProjects\php\Civitas\Civitas && php artisan queue:work"

echo Starting Laravel Scheduler...
start "Scheduler" cmd /c "cd /d E:\Fears\myProjects\php\Civitas\Civitas && php artisan schedule:work"

echo Starting Laravel Server...
cd /d E:\Fears\myProjects\php\Civitas\Civitas
php artisan serve
