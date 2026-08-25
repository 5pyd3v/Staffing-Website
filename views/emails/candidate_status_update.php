<p>Hi <?= e($candidateName) ?>,</p>
<p>There's an update on your TalentBridge Partners profile status:</p>
<p style="font-size:17px;font-weight:700;margin:16px 0;"><?= e($statusLabel) ?></p>
<?php if (!empty($message)): ?>
<p><?= nl2br(e($message)) ?></p>
<?php endif; ?>
<p>&mdash; The TalentBridge Partners Team</p>
