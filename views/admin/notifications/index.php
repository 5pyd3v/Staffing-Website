<h1 class="page-title">Notifications</h1>
<p class="page-subtitle">The last 50 email-ready alerts queued by the platform (candidate signups, staffing requests, applications, matches).</p>

<div class="panel">
    <table class="data-table">
        <thead>
            <tr><th scope="col">When</th><th scope="col">Type</th><th scope="col">Recipient</th><th scope="col">Subject</th><th scope="col">Status</th></tr>
        </thead>
        <tbody>
            <?php if (empty($notifications)): ?>
                <tr><td colspan="5" class="empty-state">No notifications yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($notifications as $n): ?>
                <tr>
                    <td class="text-muted"><?= e(date('M j, g:ia', strtotime($n['created_at']))) ?></td>
                    <td><?= e(ucwords(str_replace('_', ' ', $n['type']))) ?></td>
                    <td class="text-muted"><?= e($n['recipient_email'] ?? '&mdash;') ?></td>
                    <td><?= e($n['subject'] ?? '') ?></td>
                    <td><span class="badge badge--<?= $n['status'] === 'sent' ? 'active' : ($n['status'] === 'failed' ? 'inactive' : 'pending') ?>"><?= e(ucfirst($n['status'])) ?></span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
