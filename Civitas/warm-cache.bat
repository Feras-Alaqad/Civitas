@echo off
cd /d "%~dp0"
echo ================================
echo   Civitas Cache Warming
echo ================================
echo.

echo [1/2] Warming Dashboard...
php artisan cache:warm-dashboard
echo.

echo [2/2] Warming Citizens (100 pages + common searches)...
php artisan cache:warm-citizens --pages=100 --search-pages=5
echo.

echo ================================
echo   All caches warmed!
echo ================================
pause
