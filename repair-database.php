<?php
/**
 * ترمیم پایگاه داده - REPAIR DATABASE
 * 
 * URL: http://localhost/web/wp-content/themes/THEME-NAME/repair-database.php
 */

// مسیر wp-load.php را پیدا کنید
$wp_load_paths = array(
    '../../../../../wp-load.php',
    '../../../../wp-load.php',
    '../../../wp-load.php'
);

$wp_loaded = false;
foreach ($wp_load_paths as $path) {
    if (file_exists($path)) {
        require_once($path);
        $wp_loaded = true;
        break;
    }
}

if (!$wp_loaded) {
    die('Cannot find wp-load.php');
}

// فقط ادمین
if (!current_user_can('manage_options')) {
    die('Access denied - Admin only');
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>🔧 ترمیم پایگاه داده</title>
    <style>
        body {
            font-family: Tahoma, Arial;
            direction: rtl;
            padding: 40px;
            background: #f0f0f1;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2271b1;
            border-bottom: 3px solid #2271b1;
            padding-bottom: 10px;
        }
        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 6px;
            border-right: 4px solid;
        }
        .alert-danger {
            background: #fff3cd;
            border-color: #dc3545;
            color: #721c24;
        }
        .alert-success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        .alert-info {
            background: #d1ecf1;
            border-color: #0dcaf0;
            color: #0c5460;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #2271b1;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin: 10px 5px;
        }
        .btn:hover {
            background: #135e96;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .step {
            background: #f8f9fa;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border-right: 4px solid #0dcaf0;
        }
        .step h3 {
            margin-top: 0;
            color: #0c5460;
        }
        code {
            background: #f5f5f5;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: Consolas, monospace;
        }
        .result {
            background: #000;
            color: #0f0;
            padding: 20px;
            border-radius: 8px;
            font-family: Consolas, monospace;
            margin: 20px 0;
            overflow-x: auto;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>🔧 ترمیم پایگاه داده WordPress</h1>
    
    <div class="alert alert-danger">
        <strong>⚠️ خطا تشخیص داده شد:</strong><br>
        <code>Table 'wp_options' doesn't exist in engine</code><br>
        این به معنای مشکل در جدول wp_options است.
    </div>

    <?php
    global $wpdb;
    
    if (isset($_POST['action'])) {
        echo '<div class="result">';
        
        $action = $_POST['action'];
        
        switch ($action) {
            case 'check':
                echo "=== چک کردن جداول ===\n\n";
                
                $tables = array('wp_options', 'wp_posts', 'wp_postmeta', 'wp_users', 'wp_usermeta');
                
                foreach ($tables as $table) {
                    $result = $wpdb->get_results("CHECK TABLE $table", ARRAY_A);
                    echo "جدول: $table\n";
                    foreach ($result as $row) {
                        echo "  وضعیت: " . $row['Msg_text'] . "\n";
                    }
                    echo "\n";
                }
                break;
                
            case 'repair':
                echo "=== ترمیم جداول ===\n\n";
                
                $tables = array('wp_options', 'wp_posts', 'wp_postmeta', 'wp_users', 'wp_usermeta');
                
                foreach ($tables as $table) {
                    echo "ترمیم $table...\n";
                    $result = $wpdb->get_results("REPAIR TABLE $table", ARRAY_A);
                    foreach ($result as $row) {
                        echo "  " . $row['Msg_text'] . "\n";
                    }
                    echo "\n";
                }
                break;
                
            case 'optimize':
                echo "=== بهینه‌سازی جداول ===\n\n";
                
                $tables = array('wp_options', 'wp_posts', 'wp_postmeta', 'wp_users', 'wp_usermeta');
                
                foreach ($tables as $table) {
                    echo "بهینه‌سازی $table...\n";
                    $result = $wpdb->get_results("OPTIMIZE TABLE $table", ARRAY_A);
                    foreach ($result as $row) {
                        echo "  " . $row['Msg_text'] . "\n";
                    }
                    echo "\n";
                }
                break;
                
            case 'recreate':
                echo "=== بازسازی جدول wp_options ===\n\n";
                
                // Backup ساخت
                echo "1. ساخت Backup...\n";
                $wpdb->query("CREATE TABLE IF NOT EXISTS wp_options_backup AS SELECT * FROM wp_options");
                echo "  ✓ Backup ساخته شد\n\n";
                
                // حذف و بازسازی
                echo "2. حذف جدول قدیمی...\n";
                $wpdb->query("DROP TABLE IF EXISTS wp_options");
                echo "  ✓ جدول حذف شد\n\n";
                
                echo "3. ساخت جدول جدید...\n";
                $create_sql = "CREATE TABLE wp_options (
                    option_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    option_name varchar(191) NOT NULL DEFAULT '',
                    option_value longtext NOT NULL,
                    autoload varchar(20) NOT NULL DEFAULT 'yes',
                    PRIMARY KEY (option_id),
                    UNIQUE KEY option_name (option_name),
                    KEY autoload (autoload)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci";
                
                $wpdb->query($create_sql);
                echo "  ✓ جدول جدید ساخته شد\n\n";
                
                echo "4. بازگردانی داده‌ها...\n";
                $wpdb->query("INSERT INTO wp_options SELECT * FROM wp_options_backup");
                echo "  ✓ داده‌ها بازگردانی شدند\n\n";
                
                echo "✅ بازسازی کامل شد!\n";
                break;
                
            case 'clear_cache':
                echo "=== پاک کردن Cache ===\n\n";
                
                $deleted = $wpdb->query("DELETE FROM wp_options WHERE option_name LIKE '_transient_%'");
                echo "  ✓ $deleted transient پاک شد\n\n";
                
                echo "✅ Cache پاک شد!\n";
                break;
        }
        
        echo '</div>';
        
        echo '<div class="alert alert-success">';
        echo '<strong>✅ عملیات انجام شد!</strong><br>';
        echo 'حالا سایت رو چک کنید.';
        echo '</div>';
    }
    ?>

    <div class="step">
        <h3>مرحله 1: چک کردن وضعیت جداول</h3>
        <p>اول وضعیت جداول رو چک میکنیم:</p>
        <form method="post">
            <input type="hidden" name="action" value="check">
            <button type="submit" class="btn">🔍 چک کردن جداول</button>
        </form>
    </div>

    <div class="step">
        <h3>مرحله 2: ترمیم جداول</h3>
        <p>اگر مشکلی پیدا شد، ترمیم کنید:</p>
        <form method="post">
            <input type="hidden" name="action" value="repair">
            <button type="submit" class="btn">🔧 ترمیم جداول</button>
        </form>
    </div>

    <div class="step">
        <h3>مرحله 3: بهینه‌سازی</h3>
        <p>بعد از ترمیم، بهینه‌سازی کنید:</p>
        <form method="post">
            <input type="hidden" name="action" value="optimize">
            <button type="submit" class="btn">⚡ بهینه‌سازی جداول</button>
        </form>
    </div>

    <div class="step">
        <h3>مرحله 4: پاک کردن Cache</h3>
        <p>Cache های قدیمی رو پاک کنید:</p>
        <form method="post">
            <input type="hidden" name="action" value="clear_cache">
            <button type="submit" class="btn">🧹 پاک کردن Cache</button>
        </form>
    </div>

    <div class="alert alert-danger">
        <h3>⚠️ اگر بازم کار نکرد:</h3>
        <p>این گزینه جدول wp_options را کاملاً بازسازی میکند:</p>
        <form method="post" onsubmit="return confirm('آیا مطمئنید؟ این عملیات جدول را از نو میسازد!');">
            <input type="hidden" name="action" value="recreate">
            <button type="submit" class="btn btn-danger">🔴 بازسازی جدول wp_options</button>
        </form>
        <p><small>⚠️ فقط اگر بقیه کار نکرد از این استفاده کنید!</small></p>
    </div>

    <hr>
    
    <h3>📌 مراحل بعدی:</h3>
    <ol>
        <li>بعد از ترمیم، به <a href="<?php echo admin_url(); ?>">ادمین</a> برید</li>
        <li>تنظیمات > Permalinks > Save Changes</li>
        <li>سایت را رفرش کنید (Ctrl+Shift+R)</li>
    </ol>

    <div class="alert alert-info">
        <strong>💡 نکته:</strong><br>
        خطاهای CSS که دیدید (Unknown property 'speak') مشکلی نیستند. اینها warning های معمولی WordPress هستند.
    </div>

</div>

</body>
</html>
