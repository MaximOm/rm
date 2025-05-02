<?php
class AdminLogger {
    private $pdo;
    private static $instance = null;
    
    private function __construct($pdo) {
        $this->pdo = $pdo;
        $this->initTable();
    }
    
    public static function getInstance($pdo) {
        if (self::$instance === null) {
            self::$instance = new self($pdo);
        }
        return self::$instance;
    }
    
    private function initTable() {
        $sql = "CREATE TABLE IF NOT EXISTS admin_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            action VARCHAR(50) NOT NULL,
            entity_type VARCHAR(50) NOT NULL,
            entity_id VARCHAR(50),
            details TEXT,
            ip_address VARCHAR(45),
            user_agent VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_action (action),
            INDEX idx_entity (entity_type, entity_id),
            INDEX idx_created (created_at)
        )";
        $this->pdo->exec($sql);
    }
    
    public function log($action, $entityType, $entityId = null, $details = null) {
        $stmt = $this->pdo->prepare("INSERT INTO admin_logs 
            (user_id, action, entity_type, entity_id, details, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
            
        $userId = $_SESSION['admin_id'] ?? 0;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        if (is_array($details)) {
            $details = json_encode($details);
        }
        
        $stmt->execute([
            $userId,
            $action,
            $entityType,
            $entityId,
            $details,
            $ipAddress,
            $userAgent
        ]);
    }
    
    public function getRecentLogs($limit = 50) {
        $stmt = $this->pdo->prepare("
            SELECT l.*, CONCAT(u.username) as username
            FROM admin_logs l
            LEFT JOIN users u ON l.user_id = u.id
            ORDER BY l.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function getLogsByUser($userId, $limit = 50) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM admin_logs 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
    
    public function getLogsByEntityType($entityType, $limit = 50) {
        $stmt = $this->pdo->prepare("
            SELECT l.*, CONCAT(u.username) as username
            FROM admin_logs l
            LEFT JOIN users u ON l.user_id = u.id
            WHERE l.entity_type = ?
            ORDER BY l.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$entityType, $limit]);
        return $stmt->fetchAll();
    }
    
    public function getLogsByEntity($entityType, $entityId) {
        $stmt = $this->pdo->prepare("
            SELECT l.*, CONCAT(u.username) as username
            FROM admin_logs l
            LEFT JOIN users u ON l.user_id = u.id
            WHERE l.entity_type = ? AND l.entity_id = ?
            ORDER BY l.created_at DESC
        ");
        $stmt->execute([$entityType, $entityId]);
        return $stmt->fetchAll();
    }
    
    public function searchLogs($query, $limit = 50) {
        $stmt = $this->pdo->prepare("
            SELECT l.*, CONCAT(u.username) as username
            FROM admin_logs l
            LEFT JOIN users u ON l.user_id = u.id
            WHERE l.action LIKE ? 
            OR l.entity_type LIKE ? 
            OR l.entity_id LIKE ?
            OR l.details LIKE ?
            ORDER BY l.created_at DESC
            LIMIT ?
        ");
        
        $searchTerm = "%$query%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit]);
        return $stmt->fetchAll();
    }
    
    // Helper method to format log entry for display
    public function formatLogEntry($log) {
        $action = htmlspecialchars($log['action']);
        $entityType = htmlspecialchars($log['entity_type']);
        $entityId = $log['entity_id'] ? '#' . htmlspecialchars($log['entity_id']) : '';
        $username = htmlspecialchars($log['username'] ?? 'System');
        $date = date('M j, Y H:i:s', strtotime($log['created_at']));
        
        return "<div class='log-entry'>
            <div class='log-header'>
                <span class='log-action'>$action</span>
                <span class='log-entity'>$entityType$entityId</span>
                <span class='log-user'>by $username</span>
                <span class='log-date'>$date</span>
            </div>
            " . ($log['details'] ? "<div class='log-details'>" . htmlspecialchars($log['details']) . "</div>" : "") . "
        </div>";
    }
    
    // Helper method to get CSS styles for log display
    public function getLogStyles() {
        return <<<CSS
        <style>
            .log-entry { 
                background: white;
                padding: 15px;
                margin-bottom: 10px;
                border-radius: 4px;
                border: 1px solid #eee;
            }
            .log-header {
                display: flex;
                gap: 15px;
                align-items: center;
                flex-wrap: wrap;
            }
            .log-action {
                font-weight: 500;
                color: #0078d4;
            }
            .log-entity {
                color: #666;
            }
            .log-user {
                color: #333;
            }
            .log-date {
                color: #666;
                margin-left: auto;
            }
            .log-details {
                margin-top: 10px;
                padding-top: 10px;
                border-top: 1px solid #eee;
                font-size: 0.9em;
                color: #666;
            }
            @media (max-width: 768px) {
                .log-header {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 5px;
                }
                .log-date {
                    margin-left: 0;
                }
            }
        </style>
        CSS;
    }
}