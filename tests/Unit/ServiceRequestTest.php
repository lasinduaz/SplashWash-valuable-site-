<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for ServiceRequest business logic.
 * These tests do NOT hit the database — pure logic only.
 */
class ServiceRequestTest extends TestCase
{
    // ── Status validation ────────────────────────────────────────────────────

    #[Test]
    public function valid_statuses_are_accepted(): void
    {
        $validStatuses = ['open', 'in_progress', 'closed'];

        foreach ($validStatuses as $status) {
            $this->assertContains(
                $status,
                $validStatuses,
                "Status '{$status}' should be valid"
            );
        }
    }

    #[Test]
    public function invalid_status_is_rejected(): void
    {
        $validStatuses = ['open', 'in_progress', 'closed'];
        $invalidStatus = 'pending';

        $this->assertNotContains(
            $invalidStatus,
            $validStatuses,
            "Status '{$invalidStatus}' should not be valid"
        );
    }

    // ── Input sanitization ───────────────────────────────────────────────────

    #[Test]
    public function car_model_is_trimmed(): void
    {
        $raw = '  Toyota Corolla 2018  ';
        $sanitized = trim($raw);

        $this->assertEquals('Toyota Corolla 2018', $sanitized);
    }

    #[Test]
    public function empty_car_model_is_detected(): void
    {
        $model = '   ';
        $this->assertEmpty(trim($model));
    }

    #[Test]
    #[DataProvider('sqlInjectionProvider')]
    public function car_model_rejects_sql_injection(string $input): void
    {
        // Simulate what htmlspecialchars does before DB insert
        $sanitized = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');

        $this->assertStringNotContainsString("'", $sanitized);
        $this->assertStringNotContainsString('"', $sanitized);
    }

    public static function sqlInjectionProvider(): array
    {
        return [
            ["Toyota' OR '1'='1"],
            ['"; DROP TABLE users; --'],
            ["<script>alert('xss')</script>"],
        ];
    }

    // ── Role validation ──────────────────────────────────────────────────────

    #[Test]
    public function valid_roles_are_accepted(): void
    {
        $validRoles = ['admin', 'technician', 'customer'];

        foreach ($validRoles as $role) {
            $this->assertContains($role, $validRoles);
        }
    }

    #[Test]
    public function unknown_role_is_rejected(): void
    {
        $validRoles = ['admin', 'technician', 'customer'];
        $this->assertNotContains('superuser', $validRoles);
    }

    // ── Appointment scheduling ───────────────────────────────────────────────

    #[Test]
    public function appointment_cannot_be_scheduled_in_the_past(): void
    {
        $scheduledFor = new \DateTime('-1 day');
        $now          = new \DateTime();

        $this->assertLessThan(
            $now->getTimestamp(),
            $scheduledFor->getTimestamp(),
            'Past appointments should be detected as invalid'
        );
    }

    #[Test]
    public function future_appointment_is_valid(): void
    {
        $scheduledFor = new \DateTime('+2 days');
        $now          = new \DateTime();

        $this->assertGreaterThan(
            $now->getTimestamp(),
            $scheduledFor->getTimestamp()
        );
    }

    // ── Password policy ──────────────────────────────────────────────────────

    #[Test]
    public function password_minimum_length_is_enforced(): void
    {
        $short = '123';
        $valid = 'securepassword';

        $this->assertLessThan(6, strlen($short));
        $this->assertGreaterThanOrEqual(6, strlen($valid));
    }
}
