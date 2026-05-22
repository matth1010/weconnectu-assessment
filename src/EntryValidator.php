<?php

declare(strict_types=1);

class ContactSubmissionValidator
{
    public static function validate(array $input): array
    {
        $values = [
            'name' => trim((string) ($input['name'] ?? '')),
            'email' => strtolower(trim((string) ($input['email'] ?? ''))),
            'phone' => trim((string) ($input['phone'] ?? '')),
            'message' => trim((string) ($input['message'] ?? '')),
        ];

        $errors = [];

        if ($values['name'] === '') {
            $errors['name'] = 'Please enter your name.';
        } elseif (self::textLength($values['name']) > 120) {
            $errors['name'] = 'Name must be 120 characters or fewer.';
        }

        if ($values['email'] === '') {
            $errors['email'] = 'Please enter your email address.';
        } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        } elseif (self::textLength($values['email']) > 190) {
            $errors['email'] = 'Email must be 190 characters or fewer.';
        }

        if ($values['phone'] === '') {
            $errors['phone'] = 'Please enter your South African phone number.';
        } elseif (!self::isValidSouthAfricanPhone($values['phone'])) {
            $errors['phone'] = 'Use a valid SA number, for example 021 123 4567 or +27 82 123 4567.';
        }

        if ($values['message'] === '') {
            $errors['message'] = 'Please enter a message.';
        } elseif (self::textLength($values['message']) > 2000) {
            $errors['message'] = 'Message must be 2,000 characters or fewer.';
        }

        return [$values, $errors];
    }

    public static function normalizePhone(string $phone): string
    {
        return preg_replace('/[\s().-]+/', '', trim($phone)) ?? '';
    }

    public static function isValidSouthAfricanPhone(string $phone): bool
    {
        // The assessment only asks for SA contact details, so I keep the phone check local.
        $normalized = self::normalizePhone($phone);

        return (bool) preg_match('/^(?:\+27|27|0)[1-8][0-9]{8}$/', $normalized);
    }

    private static function textLength(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    }
}

final class EntryValidator extends ContactSubmissionValidator
{
}
