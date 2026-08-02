@echo off
chcp 65001 >nul
echo ========================================
echo  Test Meilisearch Auto Sync
echo ========================================
echo.

echo [1] Adding test person...
php artisan tinker --execute="App\Models\Person::unguard(); App\Models\Person::create(['PersonID' => 'test-sync-001', 'FullName' => 'Test User', 'Phone' => '0555123456', 'Email' => 'test@example.com']); echo 'Person created!';"

echo.
echo [2] Waiting for queue worker...
timeout /t 3 >nul

echo.
echo [3] Searching in Meilisearch...
curl -s -X POST http://127.0.0.1:7700/indexes/persons_index/search -H "Authorization: Bearer your-secret-master-key-here" -H "Content-Type: application/json" -d "{\"q\": \"Test User\", \"limit\": 5}"

echo.
echo.
echo [4] Updating test person...
php artisan tinker --execute="$p = App\Models\Person::find('test-sync-001'); if($p) { $p->update(['FullName' => 'Test User Updated']); echo 'Person updated!'; } else { echo 'Person not found!'; }"

echo.
echo [5] Waiting for queue worker...
timeout /t 3 >nul

echo.
echo [6] Searching updated person...
curl -s -X POST http://127.0.0.1:7700/indexes/persons_index/search -H "Authorization: Bearer your-secret-master-key-here" -H "Content-Type: application/json" -d "{\"q\": \"Test User Updated\", \"limit\": 5}"

echo.
echo.
echo [7] Deleting test person...
php artisan tinker --execute="$p = App\Models\Person::find('test-sync-001'); if($p) { $p->delete(); echo 'Person deleted!'; } else { echo 'Person not found!'; }"

echo.
echo [8] Waiting for queue worker...
timeout /t 3 >nul

echo.
echo [9] Searching deleted person (should not find)...
curl -s -X POST http://127.0.0.1:7700/indexes/persons_index/search -H "Authorization: Bearer your-secret-master-key-here" -H "Content-Type: application/json" -d "{\"q\": \"Test User Updated\", \"limit\": 5}"

echo.
echo.
echo ========================================
echo  Test Complete!
echo ========================================
pause
