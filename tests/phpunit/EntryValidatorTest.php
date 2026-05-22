<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class EntryValidatorTest extends TestCase
{
    public function testValidPayloadPassesValidation(): void
    {
        [$values, $errors] = EntryValidator::validate([
            'name' => 'Lerato Mokoena',
            'email' => 'lerato@example.co.za',
            'phone' => '082 123 4567',
            'message' => 'Please contact me about your services.',
        ]);

        self::assertSame([], $errors);
        self::assertSame('Lerato Mokoena', $values['name']);
    }

    public function testInvalidPhoneFailsValidation(): void
    {
        [, $errors] = EntryValidator::validate([
            'name' => 'Lerato Mokoena',
            'email' => 'lerato@example.co.za',
            'phone' => '123',
            'message' => 'Please contact me about your services.',
        ]);

        self::assertArrayHasKey('phone', $errors);
    }
}
