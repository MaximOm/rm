<?php
// Admin preferences management
class AdminPreferences {
    private $pdo;
    private $userId;
    private static $instance = null;
    private $preferences = null;
    
    private function __construct($pdo, $userId) {
        $this->pdo = $pdo;
        $this->userId = $userId;
    }
    
    public static function getInstance($pdo, $userId) {
        if (self::$instance === null) {
            self::$instance = new self($pdo, $userId);
        }
        return self::$instance;
    }
    
    private function initTable() {
        $sql = "CREATE TABLE IF NOT EXISTS admin_preferences (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            preference_key VARCHAR(50) NOT NULL,
            preference_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_preference (user_id, preference_key)
        )";
        $this->pdo->exec($sql);
    }
    
    private function loadPreferences() {
        if ($this->preferences === null) {
            $this->initTable();
            
            $stmt = $this->pdo->prepare("SELECT preference_key, preference_value FROM admin_preferences WHERE user_id = ?");
            $stmt->execute([$this->userId]);
            
            $this->preferences = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->preferences[$row['preference_key']] = json_decode($row['preference_value'], true);
            }
        }
    }
    
    public function get($key, $default = null) {
        $this->loadPreferences();
        return $this->preferences[$key] ?? $default;
    }
    
    public function set($key, $value) {
        $this->loadPreferences();
        
        $encoded = json_encode($value);
        $stmt = $this->pdo->prepare("INSERT INTO admin_preferences (user_id, preference_key, preference_value) 
                                   VALUES (?, ?, ?) 
                                   ON DUPLICATE KEY UPDATE preference_value = ?");
        $stmt->execute([$this->userId, $key, $encoded, $encoded]);
        
        $this->preferences[$key] = $value;
    }
    
    public function remove($key) {
        $this->loadPreferences();
        
        $stmt = $this->pdo->prepare("DELETE FROM admin_preferences WHERE user_id = ? AND preference_key = ?");
        $stmt->execute([$this->userId, $key]);
        
        unset($this->preferences[$key]);
    }
    
    public function getAll() {
        $this->loadPreferences();
        return $this->preferences;
    }
    
    // Utility method to store the last visited page
    public function setLastVisitedPage($page) {
        $this->set('last_visited_page', [
            'url' => $page,
            'timestamp' => time()
        ]);
    }
    
    // Utility method to get items per page preference
    public function getItemsPerPage($default = 10) {
        return $this->get('items_per_page', $default);
    }
    
    // Utility method to set items per page preference
    public function setItemsPerPage($count) {
        $this->set('items_per_page', max(1, min(100, (int)$count)));
    }
    
    // Utility method to store UI theme preference
    public function setTheme($theme) {
        $this->set('ui_theme', $theme);
    }
    
    // Utility method to get UI theme preference
    public function getTheme($default = 'light') {
        return $this->get('ui_theme', $default);
    }
    
    // Utility method to store grid/list view preference
    public function setViewMode($mode) {
        if (in_array($mode, ['grid', 'list'])) {
            $this->set('view_mode', $mode);
        }
    }
    
    // Utility method to get view mode preference
    public function getViewMode($default = 'grid') {
        return $this->get('view_mode', $default);
    }
}