<?php

require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/layout.php';

$token = $_GET['token'] ?? '';
$id = $_GET['id'] ?? '';

if ($id !== '') {
    $stmt = db()->prepare('
        SELECT d.*, NULL AS recipient_email
        FROM documents d
        WHERE d.readable_id = ?
    ');
    $stmt->execute([$id]);
} else {
    $stmt = db()->prepare('
        SELECT d.*, s.recipient_email
        FROM shares s
        JOIN documents d ON d.id = s.document_id
        WHERE s.token = ?
    ');
    $stmt->execute([$token]);
}

$doc = $stmt->fetch();

if (!$doc) {
    http_response_code(404);
    render_header('Not found');
    ?>
    <div class="centered-message">
        <h1>Share link not found</h1>
        <p>The link you used is invalid or has been removed.</p>
    </div>
    <?php
    render_footer();
    exit;
}

if (!empty($doc['publish_at']) && strtotime($doc['publish_at']) > time()) {
    http_response_code(403);
    render_header('Not yet available');
    ?>
    <div class="centered-message">
        <h1>Document not yet available</h1>
        <p>This document is scheduled to be published later.</p>
    </div>
    <?php
    render_footer();
    exit;
}

render_header($doc['title']);
?>

<h1 class="page-title"><?= h($doc['title']) ?></h1>

<?php if (!empty($doc['recipient_email'])): ?>
    <p class="meta">Shared with <?= h($doc['recipient_email']) ?></p>
<?php endif ?>

<?php if (!empty($doc['readable_id'])): ?>
    <p class="meta">Readable ID: <?= h($doc['readable_id']) ?></p>
<?php endif ?>

<pre class="doc-body"><?= h($doc['body']) ?></pre>

<?php render_footer(); ?>