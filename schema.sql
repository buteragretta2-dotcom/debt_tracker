-- Small Business Debt and Payment Tracker: database schema (MySQL / MariaDB)
CREATE DATABASE IF NOT EXISTS debt_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE debt_tracker;

CREATE TABLE users (
  user_id       INT AUTO_INCREMENT PRIMARY KEY,
  full_name     VARCHAR(100) NOT NULL,
  username      VARCHAR(50)  NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('Administrator','Staff') NOT NULL DEFAULT 'Staff',
  status        ENUM('Active','Inactive') NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB;

CREATE TABLE customers (
  customer_id  INT AUTO_INCREMENT PRIMARY KEY,
  full_name    VARCHAR(100) NOT NULL,
  phone        VARCHAR(20)  NOT NULL UNIQUE,
  address      VARCHAR(150),
  credit_limit DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE debts (
  debt_id     INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  recorded_by INT NOT NULL,
  description VARCHAR(255),
  amount      DECIMAL(12,2) NOT NULL CHECK (amount > 0),
  date_issued DATE NOT NULL,
  due_date    DATE NOT NULL,
  status      ENUM('Pending','Partially Paid','Paid','Overdue') NOT NULL DEFAULT 'Pending',
  FOREIGN KEY (customer_id) REFERENCES customers(customer_id),
  FOREIGN KEY (recorded_by) REFERENCES users(user_id),
  INDEX idx_debt_customer (customer_id),
  INDEX idx_debt_due (due_date)
) ENGINE=InnoDB;

CREATE TABLE payments (
  payment_id   INT AUTO_INCREMENT PRIMARY KEY,
  debt_id      INT NOT NULL,
  received_by  INT NOT NULL,
  amount_paid  DECIMAL(12,2) NOT NULL CHECK (amount_paid > 0),
  method       ENUM('Cash','Mobile Money','Bank') NOT NULL,
  reference    VARCHAR(50),
  payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  receipt_no   VARCHAR(20) NOT NULL UNIQUE,
  FOREIGN KEY (debt_id) REFERENCES debts(debt_id),
  FOREIGN KEY (received_by) REFERENCES users(user_id),
  INDEX idx_pay_debt (debt_id)
) ENGINE=InnoDB;

CREATE TABLE reminders (
  reminder_id INT AUTO_INCREMENT PRIMARY KEY,
  debt_id     INT NOT NULL,
  channel     VARCHAR(20),
  message     TEXT,
  sent_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (debt_id) REFERENCES debts(debt_id),
  INDEX idx_rem_debt (debt_id)
) ENGINE=InnoDB;

-- Balance of every debt is calculated, never typed in.
CREATE VIEW v_debts AS
SELECT d.*,
       IFNULL((SELECT SUM(p.amount_paid) FROM payments p WHERE p.debt_id = d.debt_id), 0) AS paid,
       d.amount - IFNULL((SELECT SUM(p.amount_paid) FROM payments p WHERE p.debt_id = d.debt_id), 0) AS balance
FROM debts d;

-- First administrator. Username: admin   Password: admin123  (change it after first login)
INSERT INTO users (full_name, username, password_hash, role)
VALUES ('System Administrator', 'admin', '$2y$10$mlddOzLsNIylXvqDQSf5d.b1wuH4Jl16jdsrV4c6of83qeVm03Dgq', 'Administrator');
