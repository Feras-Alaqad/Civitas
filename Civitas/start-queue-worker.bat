@echo off
echo ========================================
echo  Starting Queue Worker for Meilisearch
echo ========================================
echo.
echo This will start the Laravel queue worker.
echo Keep this window open while testing.
echo.
php artisan queue:work
