-- Workflow Dashboard schema (MySQL 5.7+ / MariaDB 10.3+)
-- Import in phpMyAdmin, or let install.php run it.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS settings (
  k VARCHAR(64) NOT NULL PRIMARY KEY,
  v TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin (
  id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
  username VARCHAR(60) NOT NULL,
  name VARCHAR(120) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT one_admin CHECK (id = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_attempts (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  ip VARCHAR(45) NOT NULL,
  attempted_at DATETIME NOT NULL,
  KEY ip_time (ip, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL,
  color ENUM('blue','green','purple','pink','orange','yellow') NOT NULL DEFAULT 'blue',
  icon VARCHAR(40) NOT NULL DEFAULT 'tag',
  position INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS figma_projects (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  client VARCHAR(120) NOT NULL DEFAULT '',
  kind ENUM('client','portfolio') NOT NULL DEFAULT 'client',
  figma_url VARCHAR(500) NOT NULL DEFAULT '',
  due_on DATE NULL,
  source ENUM('upwork','direct','self') NOT NULL DEFAULT 'upwork',
  note VARCHAR(255) NOT NULL DEFAULT '',
  status ENUM('active','review','revisions','done','paused') NOT NULL DEFAULT 'active',
  last_opened_at DATETIME NULL,
  position INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS goals (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  parent_id INT UNSIGNED NULL,
  title VARCHAR(160) NOT NULL,
  unit VARCHAR(20) NOT NULL DEFAULT 'h',
  target DECIMAL(10,2) NOT NULL DEFAULT 0,
  done DECIMAL(10,2) NOT NULL DEFAULT 0,
  start_on DATE NULL,
  due_on DATE NULL,
  daily_minutes INT NULL,
  rest_days TINYINT NOT NULL DEFAULT 0,
  category_id INT UNSIGNED NULL,
  status ENUM('active','done','paused') NOT NULL DEFAULT 'active',
  position INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  KEY parent (parent_id),
  CONSTRAINT goals_parent FOREIGN KEY (parent_id) REFERENCES goals(id) ON DELETE CASCADE,
  CONSTRAINT goals_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS goal_logs (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  goal_id INT UNSIGNED NOT NULL,
  logged_on DATE NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  note VARCHAR(255) NOT NULL DEFAULT '',
  KEY goal_day (goal_id, logged_on),
  CONSTRAINT logs_goal FOREIGN KEY (goal_id) REFERENCES goals(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tasks (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  notes TEXT NULL,
  category_id INT UNSIGNED NULL,
  status ENUM('todo','doing','review','done','later') NOT NULL DEFAULT 'todo',
  priority ENUM('urgent','moderate','low') NOT NULL DEFAULT 'moderate',
  important TINYINT(1) NOT NULL DEFAULT 0,
  urgent TINYINT(1) NOT NULL DEFAULT 0,
  start_on DATE NULL,
  due_on DATE NULL,
  due_time TIME NULL,
  est_minutes INT NULL,
  position INT NOT NULL DEFAULT 0,
  project_id INT UNSIGNED NULL,
  goal_id INT UNSIGNED NULL,
  completed_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  KEY status_pos (status, position),
  KEY due (due_on),
  CONSTRAINT tasks_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  CONSTRAINT tasks_project FOREIGN KEY (project_id) REFERENCES figma_projects(id) ON DELETE SET NULL,
  CONSTRAINT tasks_goal FOREIGN KEY (goal_id) REFERENCES goals(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS task_checklist (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  task_id INT UNSIGNED NOT NULL,
  text VARCHAR(200) NOT NULL,
  done TINYINT(1) NOT NULL DEFAULT 0,
  position INT NOT NULL DEFAULT 0,
  KEY task (task_id),
  CONSTRAINT checklist_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS expenses (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  spent_on DATE NOT NULL,
  kind ENUM('daily','connects','tools','other') NOT NULL DEFAULT 'daily',
  title VARCHAR(160) NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  currency ENUM('USD','PKR') NOT NULL DEFAULT 'USD',
  rate DECIMAL(10,2) NOT NULL,
  quantity INT NULL,
  paid_with VARCHAR(40) NOT NULL DEFAULT '',
  renews_on DATE NULL,
  note VARCHAR(255) NOT NULL DEFAULT '',
  KEY spent (spent_on)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS income (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  received_on DATE NOT NULL,
  client VARCHAR(120) NOT NULL,
  title VARCHAR(160) NOT NULL DEFAULT '',
  source ENUM('upwork','direct') NOT NULL DEFAULT 'upwork',
  amount DECIMAL(12,2) NOT NULL,
  currency ENUM('USD','PKR') NOT NULL DEFAULT 'USD',
  rate DECIMAL(10,2) NOT NULL,
  status ENUM('paid','pending','escrow') NOT NULL DEFAULT 'paid',
  due_on DATE NULL,
  KEY received (received_on)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS buy_items (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  note VARCHAR(255) NOT NULL DEFAULT '',
  est_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
  currency ENUM('USD','PKR') NOT NULL DEFAULT 'PKR',
  important TINYINT(1) NOT NULL DEFAULT 0,
  urgent TINYINT(1) NOT NULL DEFAULT 0,
  position INT NOT NULL DEFAULT 0,
  bought_at DATETIME NULL,
  expense_id INT UNSIGNED NULL,
  CONSTRAINT buy_expense FOREIGN KEY (expense_id) REFERENCES expenses(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS li_prospects (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  company VARCHAR(160) NOT NULL DEFAULT '',
  market ENUM('US','UK','Other') NOT NULL DEFAULT 'US',
  industry VARCHAR(60) NOT NULL DEFAULT '',
  why_neglected VARCHAR(255) NOT NULL DEFAULT '',
  stage ENUM('shortlisted','connected','teardown','replied','audit','pitch','won','lost') NOT NULL DEFAULT 'shortlisted',
  value DECIMAL(12,2) NOT NULL DEFAULT 0,
  next_followup DATE NULL,
  profile_url VARCHAR(500) NOT NULL DEFAULT '',
  position INT NOT NULL DEFAULT 0,
  updated_at DATETIME NOT NULL,
  KEY stage_pos (stage, position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS li_posts (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  topic VARCHAR(200) NOT NULL,
  status ENUM('draft','scheduled','posted') NOT NULL DEFAULT 'draft',
  post_on DATE NULL,
  url VARCHAR(500) NOT NULL DEFAULT '',
  impressions INT NOT NULL DEFAULT 0,
  reactions INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS li_daily (
  day DATE NOT NULL,
  metric ENUM('reachouts','followups','teardowns') NOT NULL,
  count INT NOT NULL DEFAULT 0,
  PRIMARY KEY (day, metric)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS schedule_blocks (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  starts_at DATETIME NOT NULL,
  ends_at DATETIME NOT NULL,
  title VARCHAR(160) NOT NULL,
  note VARCHAR(255) NOT NULL DEFAULT '',
  category_id INT UNSIGNED NULL,
  task_id INT UNSIGNED NULL,
  goal_id INT UNSIGNED NULL,
  source ENUM('manual','maker','goal') NOT NULL DEFAULT 'manual',
  suggested TINYINT(1) NOT NULL DEFAULT 0,
  done TINYINT(1) NOT NULL DEFAULT 0,
  KEY starts (starts_at),
  CONSTRAINT blocks_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  CONSTRAINT blocks_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE SET NULL,
  CONSTRAINT blocks_goal FOREIGN KEY (goal_id) REFERENCES goals(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO categories (id, name, color, icon, position) VALUES
  (1, 'LinkedIn', 'blue', 'megaphone', 1),
  (2, 'Upwork', 'green', 'briefcase-business', 2),
  (3, 'Self study', 'purple', 'graduation-cap', 3),
  (4, 'Figma', 'pink', 'figma', 4);

INSERT IGNORE INTO settings (k, v) VALUES
  ('timezone', 'America/Los_Angeles'),
  ('second_timezone', 'Asia/Karachi'),
  ('pkr_rate', '282'),
  ('income_goal', '10000'),
  ('work_start', '18:00'),
  ('work_end', '02:00'),
  ('li_targets', '{"reachouts":10,"followups":4,"teardowns":1,"posts":1}'),
  ('connects_left', '0'),
  ('anthropic_key', '');
