<?php
// ===== DATABASE CONNECTION CLASS =====

class Database {
    private $connection;
    private static $instance = null;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please try again later.");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup() {}
}

// ===== DATABASE HELPER FUNCTIONS =====

function getDB() {
    return Database::getInstance()->getConnection();
}

function executeQuery($sql, $params = []) {
    try {
        $db = getDB();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Query execution failed: " . $e->getMessage() . " | SQL: " . $sql);
        throw new Exception("Database operation failed");
    }
}

function fetchOne($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetch();
}

function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetchAll();
}

function insertRecord($table, $data) {
    $columns = implode(', ', array_keys($data));
    $placeholders = ':' . implode(', :', array_keys($data));
    
    $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
    $stmt = executeQuery($sql, $data);
    
    return getDB()->lastInsertId();
}

function updateRecord($table, $data, $where, $whereParams = []) {
    $setPairs = [];
    foreach (array_keys($data) as $column) {
        $setPairs[] = "{$column} = :{$column}";
    }
    $setClause = implode(', ', $setPairs);
    
    $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
    $params = array_merge($data, $whereParams);
    
    $stmt = executeQuery($sql, $params);
    return $stmt->rowCount();
}

function deleteRecord($table, $where, $params = []) {
    $sql = "DELETE FROM {$table} WHERE {$where}";
    $stmt = executeQuery($sql, $params);
    return $stmt->rowCount();
}

function recordExists($table, $where, $params = []) {
    $sql = "SELECT 1 FROM {$table} WHERE {$where} LIMIT 1";
    $stmt = executeQuery($sql, $params);
    return $stmt->fetch() !== false;
}

function getRecordCount($table, $where = '1=1', $params = []) {
    $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$where}";
    $result = fetchOne($sql, $params);
    return (int) $result['count'];
}

function beginTransaction() {
    return getDB()->beginTransaction();
}

function commit() {
    return getDB()->commit();
}

function rollback() {
    return getDB()->rollBack();
}

// ===== ADMIN SETTINGS HELPER =====

function getAdminSetting($key, $default = null) {
    static $settingsCache = [];
    
    if (!isset($settingsCache[$key])) {
        $setting = fetchOne(
            "SELECT setting_value, setting_type FROM admin_settings WHERE setting_key = ?",
            [$key]
        );
        
        if ($setting) {
            $value = $setting['setting_value'];
            
            // Convert based on type
            switch ($setting['setting_type']) {
                case 'integer':
                    $value = (int) $value;
                    break;
                case 'decimal':
                    $value = (float) $value;
                    break;
                case 'boolean':
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    break;
                case 'json':
                    $value = json_decode($value, true);
                    break;
            }
            
            $settingsCache[$key] = $value;
        } else {
            $settingsCache[$key] = $default;
        }
    }
    
    return $settingsCache[$key];
}

function setAdminSetting($key, $value, $type = 'string', $updatedBy = null) {
    // Convert value based on type
    switch ($type) {
        case 'boolean':
            $value = $value ? 'true' : 'false';
            break;
        case 'json':
            $value = json_encode($value);
            break;
        default:
            $value = (string) $value;
    }
    
    $existing = fetchOne("SELECT id FROM admin_settings WHERE setting_key = ?", [$key]);
    
    if ($existing) {
        updateRecord(
            'admin_settings',
            ['setting_value' => $value, 'setting_type' => $type, 'updated_by' => $updatedBy],
            'setting_key = ?',
            [$key]
        );
    } else {
        insertRecord('admin_settings', [
            'setting_key' => $key,
            'setting_value' => $value,
            'setting_type' => $type,
            'updated_by' => $updatedBy
        ]);
    }
    
    // Clear cache
    static $settingsCache = [];
    unset($settingsCache[$key]);
}

?>