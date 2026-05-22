<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

$entries = [];
$search = trim((string) ($_GET['search'] ?? ''));
$page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
$perPage = 10;
$offset = ($page - 1) * $perPage;
$totalEntries = 0;
$filteredEntries = 0;
$uniqueEmails = 0;
$latestEntry = null;
$databaseError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        flash('danger', 'Your session expired. Please try again.');
    } elseif ($id <= 0) {
        flash('danger', 'The selected entry could not be deleted.');
    } else {
        try {
            $repository = new EntryRepository(Database::connection());
            $repository->delete($id);
            flash('success', 'Entry deleted successfully.');
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            flash('danger', 'We could not delete this entry right now.');
        }
    }

    header('Location: index.php');
    exit;
}

try {
    $repository = new EntryRepository(Database::connection());
    $totalEntries = $repository->countAll();
    $filteredEntries = $repository->countAll($search);
    $pageCount = max(1, (int) ceil($filteredEntries / $perPage));
    $page = min($page, $pageCount);
    $offset = ($page - 1) * $perPage;
    $entries = $repository->paginated($search, $perPage, $offset);
    $latestEntry = $repository->latestCreatedAt();
    $uniqueEmails = $repository->countUniqueEmails();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $databaseError = 'Saved entries could not be loaded right now. Please check the database connection.';
    $pageCount = 1;
}

$flash = consume_flash();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | WeConnectU Assessment</title>
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
        </header>

        <section class="clean-main">
            <header class="clean-header">
                <div>
                    <p class="eyebrow text-primary">Contact operations</p>
                    <h1>Entries</h1>
                </div>
            </header>

            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
                    <?= e($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($databaseError): ?>
                <div class="alert alert-danger" role="alert"><?= e($databaseError) ?></div>
            <?php endif; ?>

            <section class="metrics-grid" aria-label="Entry summary">
                <article class="metric-tile">
                    <span>Total entries</span>
                    <strong><?= e((string) $totalEntries) ?></strong>
                    <p>Saved contact submissions</p>
                </article>
                <article class="metric-tile">
                    <span>Unique emails</span>
                    <strong><?= e((string) $uniqueEmails) ?></strong>
                    <p>Distinct contacts captured</p>
                </article>
                <article class="metric-tile">
                    <span>Latest submission</span>
                    <strong><?= $latestEntry ? e(date('d M', strtotime($latestEntry))) : 'None' ?></strong>
                    <p><?= $latestEntry ? e(date('H:i', strtotime($latestEntry))) : 'No records yet' ?></p>
                </article>
            </section>

            <section class="dashboard-panel clean-panel">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow text-primary">Saved entries</p>
                        <h2>All saved entries</h2>
                    </div>
                    <div class="panel-tools">
                        <form method="get" action="index.php" class="search-form" role="search">
                            <label for="search" class="visually-hidden">Search entries</label>
                            <input type="search" class="form-control form-control-sm" id="search" name="search" value="<?= e($search) ?>" placeholder="Search entries">
                            <button type="submit" class="icon-button" aria-label="Search" data-bs-toggle="tooltip" data-bs-title="Search">
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                    <path d="M10.5 4a6.5 6.5 0 0 1 5.2 10.4l4 4-1.4 1.4-4-4A6.5 6.5 0 1 1 10.5 4Zm0 2a4.5 4.5 0 1 0 0 9 4.5 4.5 0 0 0 0-9Z"/>
                                </svg>
                            </button>
                            <?php if ($search !== ''): ?>
                                <a href="index.php" class="icon-button" aria-label="Clear search" data-bs-toggle="tooltip" data-bs-title="Clear search">
                                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                        <path d="m6.4 5 5.6 5.6L17.6 5 19 6.4 13.4 12l5.6 5.6-1.4 1.4-5.6-5.6L6.4 19 5 17.6l5.6-5.6L5 6.4 6.4 5Z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                        </form>
                        <a href="new.php" class="icon-button primary" aria-label="Add new entry" data-bs-toggle="tooltip" data-bs-title="Add new entry">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M11 5h2v6h6v2h-6v6h-2v-6H5v-2h6V5Z"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <?php if (!$databaseError && $entries === []): ?>
                    <div class="empty-state">
                        <h3><?= $search === '' ? 'No entries yet' : 'No matching entries' ?></h3>
                        <p><?= $search === '' ? 'Use the add button to create the first contact submission.' : 'Try a different search term.' ?></p>
                        <?php if ($search === ''): ?>
                            <a href="new.php" class="btn btn-primary">Add new entry</a>
                        <?php else: ?>
                            <a href="index.php" class="btn btn-outline-primary">Clear search</a>
                        <?php endif; ?>
                    </div>
                <?php elseif ($entries !== []): ?>
                    <div class="table-responsive">
                        <table class="table align-middle entries-table dashboard-table">
                            <thead>
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Phone</th>
                                    <th scope="col">Message</th>
                                    <th scope="col">Submitted</th>
                                    <th scope="col" class="text-end action-column">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($entries as $entry): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= e($entry['name']) ?></td>
                                        <td><a href="mailto:<?= e($entry['email']) ?>"><?= e($entry['email']) ?></a></td>
                                        <td class="text-nowrap"><?= e($entry['phone']) ?></td>
                                        <td class="message-cell"><?= nl2br(e($entry['message'])) ?></td>
                                        <td class="text-nowrap"><?= e(date('d M Y, H:i', strtotime($entry['created_at']))) ?></td>
                                        <td class="text-end">
                                            <div class="icon-actions" aria-label="Actions for <?= e($entry['name']) ?>">
                                                <a href="edit.php?id=<?= e((string) $entry['id']) ?>" class="icon-button" aria-label="Edit <?= e($entry['name']) ?>" data-bs-toggle="tooltip" data-bs-title="Edit entry">
                                                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                        <path d="M4 20h4.6L19.4 9.2a2.1 2.1 0 0 0 0-3L17.8 4.6a2.1 2.1 0 0 0-3 0L4 15.4V20Zm2-2v-1.8l8.6-8.6 1.8 1.8L7.8 18H6Zm11.8-10L16 6.2l.2-.2 1.8 1.8-.2.2Z"/>
                                                    </svg>
                                                </a>
                                                <form method="post" action="index.php" class="delete-form">
                                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= e((string) $entry['id']) ?>">
                                                    <button type="submit" class="icon-button danger" aria-label="Delete <?= e($entry['name']) ?>" data-bs-toggle="tooltip" data-bs-title="Delete entry" onclick="return confirm('Delete this entry?');">
                                                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                            <path d="M7 21a2 2 0 0 1-2-2V8h14v11a2 2 0 0 1-2 2H7ZM8 6V4h8v2h5v2H3V6h5Zm2 12h2v-8h-2v8Zm4 0h2v-8h-2v8Z"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-footer">
                        <p>
                            Showing <?= e((string) ($offset + 1)) ?>-<?= e((string) min($offset + count($entries), $filteredEntries)) ?>
                            of <?= e((string) $filteredEntries) ?><?= $search !== '' ? ' matching' : '' ?> entries
                        </p>
                        <?php if ($pageCount > 1): ?>
                            <nav aria-label="Entries pagination">
                                <ul class="pagination pagination-sm mb-0">
                                    <li class="page-item <?= $page === 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="index.php?page=<?= e((string) max(1, $page - 1)) ?>&search=<?= e(urlencode($search)) ?>">Previous</a>
                                    </li>
                                    <?php for ($i = 1; $i <= $pageCount; $i++): ?>
                                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                            <a class="page-link" href="index.php?page=<?= e((string) $i) ?>&search=<?= e(urlencode($search)) ?>"><?= e((string) $i) ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $page === $pageCount ? 'disabled' : '' ?>">
                                        <a class="page-link" href="index.php?page=<?= e((string) min($pageCount, $page + 1)) ?>&search=<?= e(urlencode($search)) ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </section>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((element) => {
            new bootstrap.Tooltip(element);
        });
    </script>
</body>
</html>
