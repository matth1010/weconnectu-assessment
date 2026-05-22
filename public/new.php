<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

$values = ['name' => '', 'email' => '', 'phone' => '', 'message' => ''];
$errors = [];
$databaseError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$values, $errors] = EntryValidator::validate($_POST);

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors['form'] = 'Your session expired. Please submit the form again.';
    }

    if ($errors === []) {
        try {
            $repository = new EntryRepository(Database::connection());
            $repository->save($values);
            flash('success', 'Thanks, your message has been saved.');
            header('Location: index.php');
            exit;
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $databaseError = 'We could not save your message right now. Please try again shortly.';
        }
    }
}

$flash = consume_flash();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Entry | WeConnectU Assessment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/styles.css" rel="stylesheet">
</head>
<body class="dashboard-body">
    <main class="clean-shell">
        <header class="clean-topbar">
            <a href="index.php" class="clean-brand">
                <span class="brand-mark">W</span>
                <span>WeConnectU Assessment</span>
            </a>
            <a href="index.php" class="icon-button" aria-label="Dashboard" data-bs-toggle="tooltip" data-bs-title="Dashboard">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path d="M3 10.5 12 3l9 7.5-1.3 1.5L18 10.6V20h-5v-6h-2v6H6v-9.4L4.3 12 3 10.5Z"/>
                </svg>
            </a>
        </header>

        <section class="clean-main">
            <header class="clean-header compact-header">
                <div>
                    <p class="eyebrow text-primary">New contact record</p>
                    <h1>Add entry</h1>
                    <p class="dashboard-subtitle">Add the contact details below.</p>
                </div>
            </header>

            <section class="dashboard-panel clean-panel form-dashboard-panel" aria-labelledby="form-title">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow text-primary">Submission form</p>
                        <h2 id="form-title">Contact details</h2>
                    </div>
                    <span class="required-note">All fields required</span>
                </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
                    <?= e($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($databaseError): ?>
                <div class="alert alert-danger" role="alert"><?= e($databaseError) ?></div>
            <?php endif; ?>

            <?php if (isset($errors['form'])): ?>
                <div class="alert alert-danger" role="alert"><?= e($errors['form']) ?></div>
            <?php endif; ?>

            <form method="post" action="new.php" id="contact-form" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Name <span class="required-marker" aria-hidden="true">*</span></label>
                        <input
                            type="text"
                            class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                            id="name"
                            name="name"
                            value="<?= e($values['name']) ?>"
                            maxlength="120"
                            aria-describedby="name-help name-error"
                            required
                        >
                        <div id="name-help" class="form-text">Enter the contact's full name.</div>
                        <div id="name-error" class="invalid-feedback"><?= e($errors['name'] ?? 'Please enter your name.') ?></div>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">Email <span class="required-marker" aria-hidden="true">*</span></label>
                        <input
                            type="email"
                            class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                            id="email"
                            name="email"
                            value="<?= e($values['email']) ?>"
                            maxlength="190"
                            aria-describedby="email-help email-error"
                            required
                        >
                        <div id="email-help" class="form-text">Use a valid email address, for example name@example.co.za.</div>
                        <div id="email-error" class="invalid-feedback"><?= e($errors['email'] ?? 'Please enter a valid email address.') ?></div>
                    </div>

                    <div class="col-12">
                        <label for="phone" class="form-label">South African phone number <span class="required-marker" aria-hidden="true">*</span></label>
                        <input
                            type="tel"
                            class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>"
                            id="phone"
                            name="phone"
                            value="<?= e($values['phone']) ?>"
                            placeholder="021 123 4567"
                            aria-describedby="phone-help phone-error"
                            required
                        >
                        <div id="phone-help" class="form-text">Local 0-prefixed numbers and international +27 formats are accepted.</div>
                        <div id="phone-error" class="invalid-feedback"><?= e($errors['phone'] ?? 'Use a valid SA number, for example 021 123 4567 or +27 82 123 4567.') ?></div>
                    </div>

                    <div class="col-12">
                        <label for="message" class="form-label">Message <span class="required-marker" aria-hidden="true">*</span></label>
                        <textarea
                            class="form-control <?= isset($errors['message']) ? 'is-invalid' : '' ?>"
                            id="message"
                            name="message"
                            rows="6"
                            maxlength="2000"
                            aria-describedby="message-help message-error"
                            required
                        ><?= e($values['message']) ?></textarea>
                        <div id="message-help" class="form-text">Enter a message of 2,000 characters or fewer.</div>
                        <div id="message-error" class="invalid-feedback"><?= e($errors['message'] ?? 'Please enter a message.') ?></div>
                    </div>
                </div>

                <div class="actions">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="index.php" class="btn btn-outline-secondary">Close</a>
                </div>
            </form>
            </section>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/validation.js"></script>
    <script>
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((element) => {
            new bootstrap.Tooltip(element);
        });
    </script>
</body>
</html>
