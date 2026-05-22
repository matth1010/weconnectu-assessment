<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/EntryValidator.php';
require_once __DIR__ . '/../src/EntryRepository.php';

$passed = 0;
$failed = 0;

function assert_true(bool $condition, string $message): void
{
    global $passed, $failed;

    if ($condition) {
        $passed++;
        echo "[PASS] {$message}" . PHP_EOL;
        return;
    }

    $failed++;
    echo "[FAIL] {$message}" . PHP_EOL;
}

function assert_same(mixed $expected, mixed $actual, string $message): void
{
    assert_true($expected === $actual, $message);
}

function valid_payload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Lerato Mokoena',
        'email' => 'Lerato@Example.co.za',
        'phone' => '082 123 4567',
        'message' => 'Please contact me about your services.',
    ], $overrides);
}

[$values, $errors] = ContactSubmissionValidator::validate(valid_payload());
assert_same([], $errors, 'valid form passes');
assert_same('Lerato Mokoena', $values['name'], 'name is kept');
assert_same('lerato@example.co.za', $values['email'], 'email is stored lowercase');

[$values, $errors] = ContactSubmissionValidator::validate(valid_payload([
    'name' => '   ',
    'email' => '',
    'phone' => '',
    'message' => '',
]));
assert_true(isset($errors['name']), 'missing name fails');
assert_true(isset($errors['email']), 'missing email fails');
assert_true(isset($errors['phone']), 'missing phone fails');
assert_true(isset($errors['message']), 'missing message fails');

[$values, $errors] = ContactSubmissionValidator::validate(valid_payload(['email' => 'not-an-email']));
assert_true(isset($errors['email']), 'bad email fails');

foreach (['021 123 4567', '0821234567', '+27 82 123 4567', '27821234567'] as $phone) {
    assert_true(ContactSubmissionValidator::isValidSouthAfricanPhone($phone), "valid SA phone passes: {$phone}");
}

foreach (['0912345678', '123', '+1 555 123 4567', '082 123 456'] as $phone) {
    assert_true(!ContactSubmissionValidator::isValidSouthAfricanPhone($phone), "bad SA phone fails: {$phone}");
}

assert_same('+27821234567', ContactSubmissionValidator::normalizePhone('+27 82 123 4567'), 'phone spaces are removed');

[$values, $errors] = ContactSubmissionValidator::validate(valid_payload(['name' => str_repeat('A', 121)]));
assert_true(isset($errors['name']), 'long name fails');

[$values, $errors] = ContactSubmissionValidator::validate(valid_payload(['message' => str_repeat('A', 2001)]));
assert_true(isset($errors['message']), 'long message fails');

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$pdo->exec(
    'CREATE TABLE contact_submissions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT NOT NULL,
        message TEXT NOT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )'
);

$repository = new ContactSubmissionRepository($pdo);
$repository->saveSubmission(valid_payload([
    'phone' => '+27 82 123 4567',
    'message' => 'Repository create test.',
]));
$repository->saveSubmission(valid_payload([
    'name' => 'Second User',
    'email' => 'second@example.co.za',
    'phone' => '0211234567',
    'message' => 'Another repository test.',
]));

$submissions = $repository->allSubmissions();
assert_same(2, count($submissions), 'submissions are saved');
assert_same('+27821234567', $submissions[1]['phone'], 'saved phone is normalized');
assert_same(2, $repository->countSubmissions(), 'all submissions are counted');
assert_same(1, $repository->countSubmissions('Second'), 'search count works');
assert_same(1, count($repository->searchSubmissions('Second', 10, 0)), 'search returns one result');
assert_same(1, count($repository->searchSubmissions('', 1, 0)), 'page limit works');
assert_true($repository->latestSubmittedAt() !== null, 'latest date is returned');
assert_same(2, $repository->countUniqueContactEmails(), 'unique emails are counted');

$found = $repository->findSubmission((int) $submissions[1]['id']);
assert_true($found !== null, 'saved submission can be found');
assert_same('Repository create test.', $found['message'], 'saved message is returned');

$repository->updateSubmission((int) $submissions[1]['id'], valid_payload([
    'name' => 'Updated User',
    'phone' => '071 234 5678',
    'message' => 'Repository update test.',
]));

$updated = $repository->findSubmission((int) $submissions[1]['id']);
assert_same('Updated User', $updated['name'] ?? '', 'name updates');
assert_same('0712345678', $updated['phone'] ?? '', 'updated phone is normalized');
assert_same('Repository update test.', $updated['message'] ?? '', 'message updates');

assert_same(null, $repository->findSubmission(999), 'missing submission returns null');

$repository->deleteSubmission((int) $submissions[1]['id']);
assert_same(null, $repository->findSubmission((int) $submissions[1]['id']), 'submission is deleted');

echo PHP_EOL . "Tests complete: {$passed} passed, {$failed} failed." . PHP_EOL;
exit($failed > 0 ? 1 : 0);
