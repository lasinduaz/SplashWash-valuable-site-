-- ============================================================
-- car_wash_db_optimized.sql
-- Optimized schema: deduped inserts, indexes, FK cascade rules,
-- password hashing notes, and clean structure.
-- ============================================================

CREATE DATABASE IF NOT EXISTS car_wash_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE car_wash_db;

-- ============================================================
-- TABLES
-- ============================================================

CREATE TABLE IF NOT EXISTS users (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    username     VARCHAR(100)                        NOT NULL UNIQUE,
    -- NOTE: Store only hashed passwords in production.
    -- Use bcrypt/argon2: password = '$2y$...' (60+ chars).
    -- VARCHAR(255) is kept to accommodate future hashed values.
    password     VARCHAR(255)                        NOT NULL,
    role         ENUM('admin','technician','customer') NOT NULL DEFAULT 'customer',
    created_at   TIMESTAMP                           NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_users_role (role)   -- speeds up filtering technicians / customers
);
CREATE TABLE IF NOT EXISTS service_requests (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    user_id             INT          NOT NULL,
    car_model           VARCHAR(255) NOT NULL DEFAULT '',
    service_description TEXT,
    status              ENUM('open','in_progress','closed') NOT NULL DEFAULT 'open',
    created_at          TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_sr_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE          -- removing a user removes their requests
        ON UPDATE CASCADE,

    INDEX idx_sr_user_id (user_id),  -- speeds up "requests by user" queries
    INDEX idx_sr_status  (status)    -- speeds up status-filtered dashboards
);

CREATE TABLE IF NOT EXISTS appointments (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    request_id     INT      NOT NULL,
    scheduled_for  DATETIME NOT NULL,
    assigned_to    INT      DEFAULT NULL,  -- NULL = unassigned
    notes          TEXT,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_apt_request
        FOREIGN KEY (request_id) REFERENCES service_requests(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_apt_technician
        FOREIGN KEY (assigned_to) REFERENCES users(id)
        ON DELETE SET NULL   -- unassign if technician is removed
        ON UPDATE CASCADE,

    INDEX idx_apt_request_id   (request_id),
    INDEX idx_apt_assigned_to  (assigned_to),
    INDEX idx_apt_scheduled_for(scheduled_for)  -- speeds up calendar/date-range queries
);

-- ============================================================
-- SEED DATA  (each user/request inserted exactly once)
-- ============================================================

-- Users
-- Passwords shown in plaintext for demo only.
-- In production hash with bcrypt before INSERT.
INSERT INTO users (username, password, role) VALUES
('admin',          'admin123', 'admin'),
('tech1',          'tech123',  'technician'),
('tech2',          'tech456',  'technician'),
('john_doe',       'pass123',  'customer'),
('jane_smith',     'pass456',  'customer'),
('mike_johnson',   'pass789',  'customer'),
('sarah_williams', 'secure',   'customer'),
('david_brown',    'mypass',   'customer');

-- Service requests
-- user_id references: john_doe=4, jane_smith=5, mike_johnson=6,
--                     sarah_williams=7, david_brown=8
INSERT INTO service_requests (user_id, car_model, service_description, status) VALUES
(4, 'Honda Civic 2020',      'Exterior wash + wax',                   'open'),
(4, 'Ford Mustang 2019',     'Full wash + tire shine',                 'in_progress'),
(5, 'Toyota Corolla 2018',   'Full wash + interior vacuum',            'closed'),
(6, 'BMW X5 2021',           'Engine cleaning + full wash',            'open'),
(6, 'Audi A4 2017',          'Interior vacuum + leather treatment',    'open'),
(7, 'Hyundai Tucson 2019',   'Full wash + polish',                     'in_progress'),
(7, 'Mercedes C-Class 2020', 'Exterior wash + wheel detailing',        'closed'),
(8, 'Tesla Model 3 2021',    'Full wash + interior vacuum',            'open'),
(4, 'Kia Sportage 2018',     'Exterior wash + wax',                    'in_progress'),
(5, 'Volkswagen Golf 2019',  'Full wash + tire shine',                 'open'),
(8, 'Nissan Altima 2020',    'Interior vacuum + full wash',            'closed'),
(6, 'Mazda CX-5 2021',       'Exterior wash + polish',                 'open'),
(7, 'Chevrolet Malibu 2017', 'Full wash + engine clean',               'in_progress'),
(5, 'Subaru Outback 2018',   'Interior vacuum + wax',                  'open'),
(4, 'Jeep Wrangler 2020',    'Full wash + underbody clean',            'closed');

-- Appointments
-- request_id: 2=Mustang(in_progress), 5=Audi, 6=Tucson, 9=Kia, 13=Malibu
-- assigned_to: tech1=2, tech2=3
INSERT INTO appointments (request_id, scheduled_for, assigned_to, notes) VALUES
(2,  '2026-06-05 10:00:00', 2, 'High priority - client requested morning slot'),
(5,  '2026-06-06 14:00:00', 3, 'Leather treatment requires extra care'),
(6,  '2026-06-07 09:00:00', 2, 'Premium service - includes detailing'),
(9,  '2026-06-08 11:00:00', 3, 'Standard wash, client has weekend plans'),
(13, '2026-06-09 13:00:00', 2, 'Engine cleaning may take 2-3 hours');