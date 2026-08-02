@echo off
php -d upload_max_filesize=600M -d post_max_size=600M -d memory_limit=512M artisan serve
