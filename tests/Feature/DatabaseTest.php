<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Feature tests — these hit the test database.
 * Requires Docker Compose test environment to be running.
 */
class DatabaseTest extends TestCase
{
    private static ?\PDO $pdo = null;

    // ── Setup / Teardown ─────────────────────────────────────────────────────

    public static function setUpBeforeClass(): void
    {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: '3306';
        $name = getenv('DB_NAME') ?: 'car_wash_test';
        $user = getenv('DB_USER') ?: 'test_user';
        $pass = getenv('DB_PASS') ?: 'test_pass';

        try {
            self::$pdo = new \PDO(
                "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
                $user,
                $pass,
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );

            // Create test tables fresh
            self::$pdo->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id         INT AUTO_INCREMENT PRIMARY KEY,
                    username   VARCHAR(100) NOT NULL UNIQUE,
                    password   VARCHAR(255) NOT NULL,
                    role       ENUM('admin','technician','customer') NOT NULL DEFAULT 'customer',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");

            self::$pdo->exec("
                CREATE TABLE IF NOT EXISTS service_requests (
                    id                  INT AUTO_INCREMENT PRIMARY KEY,
                    user_id             INT NOT NULL,
                    car_model           VARCHAR(255) NOT NULL DEFAULT '',
                    service_description TEXT,
                    status              ENUM('open','in_progress','closed') NOT NULL DEFAULT 'open',
                    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ");
        } catch (\PDOException $e) {
            self::fail("Cannot connect to test DB: " . $e->getMessage());
        }
    }

    protected function setUp(): void
    {
        // Clean slate before every test
        self::$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        self::$pdo->exec("TRUNCATE TABLE service_requests");
        self::$pdo->exec("TRUNCATE TABLE users");
        self::$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    }

    public static function tearDownAfterClass(): void
    {
        self::$pdo = null;
    }

    // ── User tests ───────────────────────────────────────────────────────────

    #[Test]
    public function can_insert_a_user(): void
    {
        $stmt = self::$pdo->prepare(
            "INSERT INTO users (username, password, role) VALUES (?, ?, ?)"
        );
        $stmt->execute(['test_admin', password_hash('secret', PASSWORD_BCRYPT), 'admin']);

        $id = (int) self::$pdo->lastInsertId();
        $this->assertGreaterThan(0, $id);
    }

    #[Test]
    public function duplicate_username_throws_exception(): void
    {
        $this->expectException(\PDOException::class);

        $stmt = self::$pdo->prepare(
            "INSERT INTO users (username, password, role) VALUES (?, ?, ?)"
        );
        $stmt->execute(['duplicate_user', 'pass1', 'customer']);
        $stmt->execute(['duplicate_user', 'pass2', 'customer']); // must throw
    }

    #[Test]
    public function can_fetch_user_by_username(): void
    {
        self::$pdo->prepare(
            "INSERT INTO users (username, password, role) VALUES (?, ?, ?)"
        )->execute(['john_doe', 'hashed_pw', 'customer']);

        $stmt = self::$pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute(['john_doe']);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertNotFalse($user);
        $this->assertEquals('john_doe', $user['username']);
        $this->assertEquals('customer', $user['role']);
    }

    // ── Service request tests ────────────────────────────────────────────────

    #[Test]
    public function can_create_service_request(): void
    {
        self::$pdo->prepare(
            "INSERT INTO users (username, password, role) VALUES (?, ?, ?)"
        )->execute(['cust1', 'pass', 'customer']);

        $userId = (int) self::$pdo->lastInsertId();

        self::$pdo->prepare(
            "INSERT INTO service_requests (user_id, car_model, service_description, status)
             VALUES (?, ?, ?, ?)"
        )->execute([$userId, 'Toyota Corolla 2018', 'Full wash', 'open']);

        $requestId = (int) self::$pdo->lastInsertId();
        $this->assertGreaterThan(0, $requestId);
    }

    #[Test]
    public function service_request_defaults_to_open_status(): void
    {
        self::$pdo->prepare(
            "INSERT INTO users (username, password, role) VALUES (?, ?, ?)"
        )->execute(['cust2', 'pass', 'customer']);

        $userId = (int) self::$pdo->lastInsertId();

        self::$pdo->prepare(
            "INSERT INTO service_requests (user_id, car_model) VALUES (?, ?)"
        )->execute([$userId, 'Honda Civic 2020']);

        $stmt = self::$pdo->prepare(
            "SELECT status FROM service_requests WHERE user_id = ?"
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertEquals('open', $row['status']);
    }

    #[Test]
    public function can_update_service_request_status(): void
    {
        self::$pdo->prepare(
            "INSERT INTO users (username, password, role) VALUES (?, ?, ?)"
        )->execute(['cust3', 'pass', 'customer']);

        $userId = (int) self::$pdo->lastInsertId();

        self::$pdo->prepare(
            "INSERT INTO service_requests (user_id, car_model, status) VALUES (?, ?, ?)"
        )->execute([$userId, 'BMW X5 2021', 'open']);

        $requestId = (int) self::$pdo->lastInsertId();

        self::$pdo->prepare(
            "UPDATE service_requests SET status = ? WHERE id = ?"
        )->execute(['in_progress', $requestId]);

        $stmt = self::$pdo->prepare(
            "SELECT status FROM service_requests WHERE id = ?"
        );
        $stmt->execute([$requestId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertEquals('in_progress', $row['status']);
    }

    #[Test]
    public function deleting_user_cascades_to_service_requests(): void
    {
        self::$pdo->prepare(
            "INSERT INTO users (username, password, role) VALUES (?, ?, ?)"
        )->execute(['cust_delete', 'pass', 'customer']);

        $userId = (int) self::$pdo->lastInsertId();

        self::$pdo->prepare(
            "INSERT INTO service_requests (user_id, car_model) VALUES (?, ?)"
        )->execute([$userId, 'Mazda CX-5 2021']);

        // Delete user — should cascade to service_requests
        self::$pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);

        $stmt = self::$pdo->prepare(
            "SELECT COUNT(*) FROM service_requests WHERE user_id = ?"
        );
        $stmt->execute([$userId]);

        $this->assertEquals(0, (int) $stmt->fetchColumn());
    }
}
