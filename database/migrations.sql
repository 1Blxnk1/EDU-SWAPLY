-- Idempotent post-seed migrations. Runs on every container boot.
-- Both statements below are safe to run repeatedly on MariaDB / MySQL 8+.

ALTER TABLE orders ADD COLUMN IF NOT EXISTS transaction_ref VARCHAR(50) NULL;

CREATE TABLE IF NOT EXISTS disputes (
    dispute_id      INT AUTO_INCREMENT PRIMARY KEY,
    order_id        INT NOT NULL,
    buyer_id        INT NOT NULL,
    reason          TEXT NOT NULL,
    status          ENUM('open','resolved') DEFAULT 'open',
    admin_response  TEXT NULL,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    resolved_at     DATETIME NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;
