<?php
/** Expects $page, $totalPages, and $baseUrl (query string without the page param) in scope. */
if (($totalPages ?? 1) <= 1) {
    return;
}
?>
<nav class="pagination">
    <?php if ($page > 1): ?>
        <a href="<?= e($baseUrl) ?>&page=<?= $page - 1 ?>" class="btn btn--ghost">Previous</a>
    <?php endif; ?>
    <span class="pagination__status">Page <?= (int) $page ?> of <?= (int) $totalPages ?></span>
    <?php if ($page < $totalPages): ?>
        <a href="<?= e($baseUrl) ?>&page=<?= $page + 1 ?>" class="btn btn--ghost">Next</a>
    <?php endif; ?>
</nav>
