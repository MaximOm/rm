<?php
require_once __DIR__ . '/admin_init.php';

class AdminMessages {
    private $pdo;
    private $logger;
    
    public function __construct($pdo, $logger) {
        $this->pdo = $pdo;
        $this->logger = $logger;
        
        // Create messages table if it doesn't exist
        $this->initializeTable();
    }
    
    private function initializeTable() {
        $sql = "
            CREATE TABLE IF NOT EXISTS admin_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                type VARCHAR(50) NOT NULL,
                message TEXT NOT NULL,
                is_read BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ";
        $this->pdo->exec($sql);
    }
    
    public function addMessage($userId, $type, $message, $expiresAt = null) {
        $sql = "
            INSERT INTO admin_messages (user_id, type, message, expires_at)
            VALUES (:user_id, :type, :message, :expires_at)
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':type' => $type,
            ':message' => $message,
            ':expires_at' => $expiresAt
        ]);
        
        $this->logger->log('system', 'message', $this->pdo->lastInsertId(), "Added new {$type} message");
        return $this->pdo->lastInsertId();
    }
    
    public function addSystemMessage($message, $expiresAt = null) {
        return $this->addMessage(null, 'system', $message, $expiresAt);
    }
    
    public function getUnreadCount($userId) {
        $sql = "
            SELECT COUNT(*) as count
            FROM admin_messages
            WHERE (user_id = :user_id OR user_id IS NULL)
            AND is_read = FALSE
            AND (expires_at IS NULL OR expires_at > CURRENT_TIMESTAMP)
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }
    
    public function getMessages($userId, $limit = 10, $offset = 0, $includeRead = false) {
        $sql = "
            SELECT *
            FROM admin_messages
            WHERE (user_id = :user_id OR user_id IS NULL)
            AND (expires_at IS NULL OR expires_at > CURRENT_TIMESTAMP)
        ";
        
        if (!$includeRead) {
            $sql .= " AND is_read = FALSE";
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function markAsRead($messageId, $userId) {
        $sql = "
            UPDATE admin_messages
            SET is_read = TRUE
            WHERE id = :id
            AND (user_id = :user_id OR user_id IS NULL)
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $messageId,
            ':user_id' => $userId
        ]);
        
        return $stmt->rowCount() > 0;
    }
    
    public function markAllAsRead($userId) {
        $sql = "
            UPDATE admin_messages
            SET is_read = TRUE
            WHERE (user_id = :user_id OR user_id IS NULL)
            AND is_read = FALSE
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        
        return $stmt->rowCount();
    }
    
    public function deleteExpiredMessages() {
        $sql = "DELETE FROM admin_messages WHERE expires_at < CURRENT_TIMESTAMP";
        return $this->pdo->exec($sql);
    }
    
    public function formatMessage($message) {
        $typeClasses = [
            'system' => 'bg-info',
            'error' => 'bg-danger',
            'warning' => 'bg-warning',
            'success' => 'bg-success'
        ];
        
        $class = $typeClasses[$message['type']] ?? 'bg-secondary';
        $date = date('M j, Y g:i A', strtotime($message['created_at']));
        $readStatus = $message['is_read'] ? 'Read' : 'Unread';
        
        return "
            <div class='message-card {$class}' data-id='{$message['id']}'>
                <div class='message-header'>
                    <span class='message-type'>{$message['type']}</span>
                    <span class='message-date'>{$date}</span>
                </div>
                <div class='message-body'>
                    {$message['message']}
                </div>
                <div class='message-footer'>
                    <span class='message-status'>{$readStatus}</span>
                    " . (!$message['is_read'] ? "<button class='mark-read-btn'>Mark as Read</button>" : "") . "
                </div>
            </div>
        ";
    }
}

// Initialize the messages system
$messages = new AdminMessages($pdo, $logger);

// Clean up expired messages
$messages->deleteExpiredMessages();

// Add message styles
if (!isset($noStyles)): ?>
<style>
.message-card {
    margin-bottom: 15px;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    color: white;
}

.bg-info { background: #0078d4; }
.bg-danger { background: #d32f2f; }
.bg-warning { background: #ffa000; }
.bg-success { background: #388e3c; }
.bg-secondary { background: #616161; }

.message-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 0.9em;
    opacity: 0.9;
}

.message-body {
    margin-bottom: 10px;
    line-height: 1.4;
}

.message-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.9em;
}

.mark-read-btn {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9em;
}

.mark-read-btn:hover {
    background: rgba(255,255,255,0.3);
}

.message-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #d32f2f;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 12px;
    margin-left: 5px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.mark-read-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const messageId = this.closest('.message-card').dataset.id;
            fetch('mark_message_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ message_id: messageId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.closest('.message-card').querySelector('.message-status').textContent = 'Read';
                    this.remove();
                    
                    // Update unread count in nav if it exists
                    const countEl = document.querySelector('.message-count');
                    if (countEl) {
                        const currentCount = parseInt(countEl.textContent);
                        if (currentCount > 1) {
                            countEl.textContent = currentCount - 1;
                        } else {
                            countEl.remove();
                        }
                    }
                }
            });
        });
    });
});
</script>
<?php endif; ?>