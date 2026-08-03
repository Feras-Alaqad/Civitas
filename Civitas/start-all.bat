@echo off
setlocal

echo ============================================
echo  Civitas - Public Server Mode
echo ============================================

REM Production settings for public server (override .env)
set APP_ENV=production
set APP_DEBUG=false
REM set APP_URL=http://YOUR_PUBLIC_IP_OR_DOMAIN

cd /d "%~dp0"

REM Generate APP_KEY on first run
if not exist .env (
    copy .env.example .env >nul
    php artisan key:generate --force
)

echo Starting Meilisearch (internal only - not exposed publicly)...
start "Meilisearch" /D "%~dp0" cmd /c ".\meilisearch\meilisearch-windows-amd64.exe --master-key="your-secret-master-key-here" --http-addr="127.0.0.1:7700" --db-path="./data.ms" --env="production""

timeout /t 3 >nul

echo Starting Laravel Queue Worker...
start "Queue Worker" /D "%~dp0" cmd /c "php artisan queue:work"

echo Starting Laravel Scheduler...
start "Scheduler" /D "%~dp0" cmd /c "php artisan schedule:work"

echo Starting Laravel Server on 0.0.0.0:8000 (public access)...
php -d upload_max_filesize=600M -d post_max_size=600M -d memory_limit=512M artisan serve --host=0.0.0.0 --port=8000

endlocal
