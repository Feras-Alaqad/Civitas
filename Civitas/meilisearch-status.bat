@echo off
chcp 65001 >nul
echo ========================================
echo  Meilisearch Status
echo ========================================
echo.

echo [1] Indexes:
curl -s -H "Authorization: Bearer your-secret-master-key-here" http://127.0.0.1:7700/indexes

echo.
echo.
echo [2] Persons Index Stats:
curl -s -H "Authorization: Bearer your-secret-master-key-here" http://127.0.0.1:7700/indexes/persons_index/stats

echo.
echo.
echo [3] Sample Data (first 3):
curl -s -X POST http://127.0.0.1:7700/indexes/persons_index/search -H "Authorization: Bearer your-secret-master-key-here" -H "Content-Type: application/json" -d "{\"q\": \"\", \"limit\": 3}"

echo.
echo.
echo ========================================
pause
