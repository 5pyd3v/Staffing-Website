<section class="hero">
    <div class="hero__inner">
        <div class="hero__copy">
            <p class="hero__eyebrow"><?= icon('sparkle', 'icon icon--sm') ?> Professional Staffing, Matched With Precision</p>
            <h1>The workforce partner for teams that can't afford a <em>bad hire</em>.</h1>
            <p class="hero__lede">TalentBridge Partners connects vetted professionals with employers across
                logistics, healthcare, finance, and skilled trades &mdash; backed by a dedicated recruiting team,
                not just an algorithm.</p>
            <div class="hero__actions">
                <a href="/hire-talent" class="btn btn--primary btn--lg"><?= icon('briefcase', 'icon') ?>Hire Talent</a>
                <a href="/candidates/register" class="btn btn--ghost btn--lg"><?= icon('search', 'icon') ?>Find Work</a>
            </div>
        </div>
        <div class="hero__art">
            <div class="hero__photo-frame">
                <img src="/assets/img/stock/hero-team.jpg" alt="A hiring manager and recruiter reviewing candidate profiles together in a modern office" class="hero__photo" width="800" height="920">
                <div class="hero__proof-card">
                    <span class="avatar-stack">
                        <?= avatar('Alex Morgan', 'sm') ?><?= avatar('Jordan Reyes', 'sm') ?><?= avatar('Sarah Chen', 'sm') ?>
                    </span>
                    <span>
                        <strong>1,200+ placed</strong>
                        <span>this year alone</span>
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="trust-strip">
    <div class="trust-strip__inner">
        <div class="trust-stat" data-reveal><span class="trust-stat__icon"><?= icon('users', 'icon') ?></span><div><strong data-count-to="1200" data-count-suffix="+">0</strong><span>Candidates placed</span></div></div>
        <div class="trust-stat" data-reveal data-reveal-delay="80"><span class="trust-stat__icon"><?= icon('building', 'icon') ?></span><div><strong data-count-to="340" data-count-suffix="+">0</strong><span>Employer partners</span></div></div>
        <div class="trust-stat" data-reveal data-reveal-delay="160"><span class="trust-stat__icon"><?= icon('star', 'icon') ?></span><div><strong>4.9/5</strong><span>Average client rating</span></div></div>
        <div class="trust-stat" data-reveal data-reveal-delay="240"><span class="trust-stat__icon"><?= icon('clock', 'icon') ?></span><div><strong data-count-to="72" data-count-suffix=" hrs">0</strong><span>Avg. time to shortlist</span></div></div>
    </div>
</section>

<?php if (!empty($services)): ?>
<section class="page-band">
    <div class="page-band__inner">
        <span class="eyebrow-label" data-reveal>Engagement Models</span>
        <h2 class="section-title" data-reveal>How We Staff</h2>
        <p class="section-subtitle" data-reveal>Flexible engagement models built around how your team actually hires.</p>
        <div class="numbered-list">
            <?php foreach ($services as $i => $service): ?>
                <?php if (!$service['is_active']) continue; ?>
                <div class="numbered-row" data-reveal data-reveal-delay="<?= $i * 60 ?>">
                    <span class="numbered-row__index"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                    <div class="numbered-row__body">
                        <h3><?= e($service['name']) ?></h3>
                        <p><?= e($service['description'] ?? '') ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="bento-showcase">
    <div class="page-band__inner">
        <span class="eyebrow-label" data-reveal>Inside TalentBridge</span>
        <h2 class="section-title" data-reveal>See the platform recruiters actually use.</h2>
        <p class="section-subtitle" data-reveal>Not a black box &mdash; the same scoring, search, and pipeline tools our team runs on, every day.</p>

        <div class="bento-grid">
            <div class="bento-card bento-card--wide bento-card--tall" data-reveal>
                <span class="bento-card__icon"><?= icon('trending-up', 'icon') ?></span>
                <h3>Match scoring, shown in the open</h3>
                <p>Every candidate is scored against your requirements across skills, location, availability, and budget &mdash; not a mystery ranking.</p>
                <div class="preview-frame">
                    <div class="preview-frame__bar"><span></span><span></span><span></span></div>
                    <div class="bento-card__preview mini-match">
                        <div class="mini-match__head">
                            <div class="mini-match__identity"><?= avatar('Alex Morgan', 'sm') ?> Alex Morgan</div>
                            <div class="mini-match__score">87<span>/100</span></div>
                        </div>
                        <div class="mini-match__bars">
                            <div class="mini-match__bar-row"><span>Skills</span><span class="mini-match__bar-track"><span class="mini-match__bar-fill" style="width:92%;"></span></span><span class="mini-match__bar-value">92%</span></div>
                            <div class="mini-match__bar-row"><span>Location</span><span class="mini-match__bar-track"><span class="mini-match__bar-fill" style="width:100%;"></span></span><span class="mini-match__bar-value">100%</span></div>
                            <div class="mini-match__bar-row"><span>Availability</span><span class="mini-match__bar-track"><span class="mini-match__bar-fill" style="width:100%;"></span></span><span class="mini-match__bar-value">100%</span></div>
                            <div class="mini-match__bar-row"><span>Budget fit</span><span class="mini-match__bar-track"><span class="mini-match__bar-fill" style="width:70%;"></span></span><span class="mini-match__bar-value">70%</span></div>
                        </div>
                        <div class="mini-match__tags">
                            <span class="mini-match__tag"><?= icon('check-circle', 'icon') ?> Forklift certified</span>
                            <span class="mini-match__tag"><?= icon('check-circle', 'icon') ?> Available now</span>
                            <span class="mini-match__tag"><?= icon('check-circle', 'icon') ?> Within budget</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bento-card bento-card--wide" data-reveal data-reveal-delay="80">
                <span class="bento-card__icon bento-card__icon--gold"><?= icon('search', 'icon') ?></span>
                <h3>Search the whole talent pool</h3>
                <p>Filter by skill, location, experience, and availability &mdash; results update instantly, no page reloads.</p>
                <div class="preview-frame">
                    <div class="preview-frame__bar"><span></span><span></span><span></span></div>
                    <div class="bento-card__preview">
                        <div class="mini-result"><?= avatar('Sarah Chen', 'sm') ?><div class="mini-result__body"><strong>Sarah Chen</strong><span>Warehouse Lead &middot; Columbus, OH</span></div><span class="badge badge--active">New</span></div>
                        <div class="mini-result"><?= avatar('Jordan Reyes', 'sm') ?><div class="mini-result__body"><strong>Jordan Reyes</strong><span>Ops Supervisor &middot; Remote OK</span></div><span class="badge badge--shortlisted">Shortlisted</span></div>
                    </div>
                </div>
            </div>

            <div class="bento-card" data-reveal data-reveal-delay="140">
                <span class="bento-card__icon"><?= icon('message-circle', 'icon') ?></span>
                <h3>A recruiter in your corner</h3>
                <p>Real conversations, not a chatbot.</p>
                <div class="preview-frame">
                    <div class="preview-frame__bar"><span></span><span></span><span></span></div>
                    <div class="bento-card__preview mini-chat">
                        <div class="mini-chat__bubble mini-chat__bubble--in">Found 3 strong matches for the warehouse role &mdash; want the shortlist today?</div>
                        <div class="mini-chat__bubble mini-chat__bubble--out">Yes, send them over.</div>
                    </div>
                </div>
            </div>

            <div class="bento-card" data-reveal data-reveal-delay="200">
                <span class="bento-card__icon bento-card__icon--gold"><?= icon('kanban', 'icon') ?></span>
                <h3>Every application, tracked</h3>
                <p>From first application to signed offer.</p>
                <div class="preview-frame">
                    <div class="preview-frame__bar"><span></span><span></span><span></span></div>
                    <div class="bento-card__preview mini-pipeline">
                        <div class="mini-pipeline__stage mini-pipeline__stage--done"><span class="mini-pipeline__dot"><?= icon('check-circle', 'icon icon--sm') ?></span><span>Applied</span></div>
                        <div class="mini-pipeline__stage mini-pipeline__stage--done"><span class="mini-pipeline__dot"><?= icon('check-circle', 'icon icon--sm') ?></span><span>Screened</span></div>
                        <div class="mini-pipeline__stage mini-pipeline__stage--current"><span class="mini-pipeline__dot">3</span><span>Interview</span></div>
                        <div class="mini-pipeline__stage"><span class="mini-pipeline__dot">4</span><span>Offer</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="proof-band">
    <div class="proof-band__inner">
        <div class="proof-band__copy" data-reveal>
            <p class="hero__eyebrow"><?= icon('shield-check', 'icon icon--sm') ?> How we work</p>
            <h2 class="section-title">A recruiter on your side, not just a database.</h2>
            <p class="section-subtitle" style="margin-bottom:1.5rem;">Every engagement is run by a dedicated recruiter who screens for fit before a resume ever reaches your inbox &mdash; whether you're filling a single warehouse shift or building out a whole department.</p>
            <ul class="proof-list">
                <li><?= icon('check-circle', 'icon') ?> Every candidate phone-screened before submission</li>
                <li><?= icon('check-circle', 'icon') ?> Dedicated recruiter for the life of the engagement</li>
                <li><?= icon('check-circle', 'icon') ?> Replacement guarantee on every direct-hire placement</li>
            </ul>
        </div>
        <div class="proof-band__art" data-reveal data-reveal-delay="120">
            <img src="/assets/img/stock/office-collab.jpg" alt="A recruiting team reviewing candidate shortlists together" width="640" height="480">
        </div>
    </div>
</section>

<section class="page-band page-band--muted">
    <div class="page-band__inner">
        <h2 class="section-title" data-reveal>What people say</h2>
        <p class="section-subtitle" data-reveal>From employers building their teams and candidates who found the right one.</p>
        <div class="testimonial-grid">
            <figure class="testimonial-card" data-reveal>
                <blockquote>&ldquo;We had three qualified forklift operators in front of us within 48 hours. That kind of speed is rare in this industry &mdash; and every one of them was actually a fit.&rdquo;</blockquote>
                <figcaption>
                    <img src="/assets/img/stock/testimonial-4.jpg" alt="" width="48" height="48">
                    <span><strong>Jordan Reyes</strong><span>Director of Operations, Meridian Logistics</span></span>
                </figcaption>
            </figure>
            <figure class="testimonial-card" data-reveal data-reveal-delay="90">
                <blockquote>&ldquo;I signed up on a Friday and had two interviews lined up by Monday. My recruiter actually understood what I was looking for instead of mass-blasting my resume.&rdquo;</blockquote>
                <figcaption>
                    <img src="/assets/img/stock/testimonial-2.jpg" alt="" width="48" height="48">
                    <span><strong>Sarah Chen</strong><span>Warehouse Operations Candidate</span></span>
                </figcaption>
            </figure>
            <figure class="testimonial-card" data-reveal data-reveal-delay="180">
                <blockquote>&ldquo;We've cut our time-to-hire in half since partnering with TalentBridge. Their team feels like an extension of ours, not a vendor.&rdquo;</blockquote>
                <figcaption>
                    <img src="/assets/img/stock/testimonial-3.jpg" alt="" width="48" height="48">
                    <span><strong>Maria Alvarez</strong><span>VP People, Growth-Stage Client</span></span>
                </figcaption>
            </figure>
        </div>
    </div>
</section>

<section class="page-band">
    <div class="page-band__inner">
        <div class="section-header-row" data-reveal>
            <div>
                <h2 class="section-title">Recently Opened Roles</h2>
                <p class="section-subtitle">A sample of what our employer partners are hiring for right now.</p>
            </div>
            <a href="/jobs" class="btn btn--ghost"><?= icon('grid', 'icon icon--sm') ?>View All Jobs</a>
        </div>

        <?php if (empty($jobs)): ?>
            <div class="empty-state">
                <?= icon('briefcase') ?>
                <p>No open roles at the moment &mdash; <a href="/candidates/register">join the talent pool</a> and we'll reach out the moment a match opens up.</p>
            </div>
        <?php else: ?>
            <div class="job-grid">
                <?php foreach ($jobs as $i => $job): ?>
                    <a class="job-card" href="/jobs/<?= e($job['slug']) ?>" data-reveal data-reveal-delay="<?= $i * 60 ?>">
                        <div class="job-card__header">
                            <h3><?= e($job['title']) ?></h3>
                            <span class="badge badge--neutral"><?= e(ucfirst(str_replace('_', ' ', $job['employment_type']))) ?></span>
                        </div>
                        <p class="job-card__company"><?= icon('building', 'icon icon--sm') ?><?= e($job['company_name']) ?></p>
                        <p class="job-card__meta">
                            <?= icon('map-pin', 'icon icon--sm') ?>
                            <?= $job['is_remote'] ? 'Remote' : e(trim(($job['location_city'] ?? '') . ', ' . ($job['location_state'] ?? ''), ', ') ?: 'Location on request') ?>
                        </p>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="cta-band">
    <div class="cta-band__inner" data-reveal>
        <h2>Ready to build your team, or your career?</h2>
        <div class="hero__actions" style="justify-content:center;">
            <a href="/hire-talent" class="btn btn--primary btn--lg"><?= icon('briefcase', 'icon') ?>Hire Talent</a>
            <a href="/candidates/register" class="btn btn--ghost-invert btn--lg"><?= icon('search', 'icon') ?>Join the Talent Pool</a>
        </div>
    </div>
</section>

<script src="<?= asset_url('/assets/js/scroll-reveal.js') ?>" defer></script>
<script src="<?= asset_url('/assets/js/count-up.js') ?>" defer></script>
