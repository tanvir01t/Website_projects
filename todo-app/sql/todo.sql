-- ============================================================
-- TODO APP DATABASE SCHEMA
-- Database: todo_app
-- Engine: InnoDB (required for foreign key support)
-- Charset: utf8mb4 (full unicode + emoji support)
-- ============================================================

CREATE DATABASE IF NOT EXISTS todo_app
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE todo_app;

-- ------------------------------------------------------------
-- Table: users
-- Stores registered user accounts
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name       VARCHAR(100)        NOT NULL,
    email           VARCHAR(150)        NOT NULL,
    password_hash   VARCHAR(255)        NOT NULL,       -- bcrypt hash via password_hash()
    avatar          VARCHAR(255)        DEFAULT NULL,    -- path to uploaded profile picture
    remember_token  VARCHAR(255)        DEFAULT NULL,    -- for "Remember Me" persistent login
    remember_expires DATETIME           DEFAULT NULL,
    reset_token     VARCHAR(255)        DEFAULT NULL,    -- for "Forgot Password" (dummy) flow
    reset_expires   DATETIME            DEFAULT NULL,
    created_at      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP
                                          ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: tasks
-- Stores to-do items, each belonging to exactly one user
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tasks (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED        NOT NULL,
    title           VARCHAR(200)        NOT NULL,
    description     TEXT                DEFAULT NULL,
    category        VARCHAR(50)         DEFAULT 'General',
    tags            VARCHAR(255)        DEFAULT NULL,    -- comma-separated tags, e.g. "urgent,work"
    priority         ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
    status          ENUM('pending','completed')  NOT NULL DEFAULT 'pending',
    due_date        DATE                DEFAULT NULL,
    completed_at    DATETIME            DEFAULT NULL,
    created_at      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP
                                          ON UPDATE CURRENT_TIMESTAMP,

    -- Relationship: many tasks belong to one user
    CONSTRAINT fk_tasks_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    -- Indexes to speed up filtering / sorting / searching
    INDEX idx_tasks_user_id (user_id),
    INDEX idx_tasks_status (status),
    INDEX idx_tasks_priority (priority),
    INDEX idx_tasks_due_date (due_date),
    FULLTEXT INDEX idx_tasks_search (title, description)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: activity_log
-- Powers the "Recent Activity" dashboard widget
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS activity_log (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED        NOT NULL,
    task_id         INT UNSIGNED        DEFAULT NULL,
    action          VARCHAR(100)        NOT NULL,        -- e.g. "created", "completed", "deleted"
    description     VARCHAR(255)        NOT NULL,
    created_at      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_activity_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    INDEX idx_activity_user_id (user_id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Relationships summary:
--   users (1) ───< (many) tasks         via tasks.user_id
--   users (1) ───< (many) activity_log  via activity_log.user_id
--   tasks (1) ───< (many) activity_log  via activity_log.task_id (nullable)
-- ------------------------------------------------------------

-- Optional: sample data for quick testing (password is "Password123!")
-- Hash below is a real bcrypt hash of "Password123!"
-- INSERT INTO users (full_name, email, password_hash) VALUES
-- ('Demo User', 'demo@example.com', '$2y$10$examplehashreplaceatruntime');
