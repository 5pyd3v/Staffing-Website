# Architecture

## Why no framework, no Composer

The stack is intentionally vanilla: PHP 8+, PDO, and a hand-rolled MVC skeleton.
No Laravel/Symfony, no Composer autoloader. This keeps the codebase transparent
and dependency-free for a local/shared-hosting-friendly deployment, while still
enforcing real separation of concerns (routing, controllers, models, views,
middleware are distinct layers — this is *not* a pile of top-level PHP scripts).

A tiny PSR-4-style autoloader (`config/autoload.php`) maps `App\Foo\Bar` to
`app/Foo/Bar.php`, which is all a project this size needs from an autoloader.

## Request lifecycle

```
Browser
  -> public/index.php (front controller)
     -> config/bootstrap.php (env, autoload, session, error handler)
     -> App\Core\Router::dispatch(Request)
        -> matches method + path against routes.php
        -> runs middleware chain (auth / role / guest)
        -> invokes Controller::method(Request)
           -> Controller talks to Models (App\Core\Model subclasses)
           -> Controller renders a View (plain PHP templates + layout wrapper)
```

`public/index.php` is the *only* PHP file directly reachable by the web server;
everything else lives outside `public/` (app, config, database, storage) so
source code and uploaded files are never served as static files even if
`.htaccess` is misconfigured.

## Core layer (`app/Core`)

| Class        | Responsibility |
|--------------|----------------|
| `Router`     | Registers `{method, path, handler, middleware}` routes, converts `{param}` segments to regex captures, dispatches by method+path, returns 404/405. |
| `Request`    | Wraps `$_GET`/`$_POST`/`$_FILES`/`$_SERVER`, exposes route params, method-override (`_method` for PUT/PATCH/DELETE from HTML forms), JSON body parsing. |
| `Response`   | Static helpers for redirect / JSON / abort-with-error-view. |
| `Database`   | Lazy PDO singleton (`ERRMODE_EXCEPTION`, real prepared statements — `EMULATE_PREPARES` off). |
| `Model`      | Base active-record-ish class: `find`, `findByUuid`, `findBy`, `all`, `create`, `update`, `delete` (soft-delete aware), plus identifier-safety guards for any dynamically interpolated column/order-by names. |
| `Session`    | Wraps native PHP sessions with secure cookie params (`httponly`, `SameSite=Lax`, configurable `secure`), periodic ID regeneration, flash messages, "old input" for repopulating forms after validation errors. |
| `Csrf`       | Per-session token, `hash_equals` verification, `<input type="hidden">` field helper. Every state-changing form must include `$csrfField` and every POST/PUT/PATCH/DELETE controller action must call `$this->verifyCsrf($request)`. |
| `Auth`       | Login (rate-limited via `login_attempts`), logout, current user/role accessors, `hasRole()` for RBAC checks. |
| `Validator`  | Rule-based validation (`required`, `email`, `min`, `max`, `numeric`, `in`, `confirmed`, `unique:table,column`, `date`, `alpha_dash`). |
| `View`       | Renders a view file inside a layout, auto-injects CSRF field/token and the current user. |
| `Controller` | Base class controllers extend for `view()`, `redirect()`, `json()`, `validate()`, `verifyCsrf()`, `backWithErrors()`. |

## RBAC / middleware

Routes declare middleware as strings, e.g. `['auth', 'role:admin,super_admin']`.
The router resolves `role:admin,super_admin` to `App\Middleware\RoleMiddleware`
with argument `"admin,super_admin"`. Three middleware ship today:

- `AuthMiddleware` — must be logged in; otherwise flashes the intended URL and
  redirects to `/login`.
- `RoleMiddleware` — must hold one of the listed role slugs; otherwise `403`.
- `GuestMiddleware` — must be logged **out** (used on `/login` so an
  authenticated user is bounced straight to their dashboard instead of seeing
  the login form again).

Roles are data-driven (`roles` table: `super_admin`, `admin`, `employer`,
`candidate`), not hardcoded constants, so new roles can be added without a
code change to the auth core.

## Security posture (implemented in Phase 2)

- Passwords: `password_hash()` (bcrypt, cost 12), verified with `password_verify()`.
- Brute-force: `login_attempts` table tracks failures per email+IP; 5 failures
  in 15 minutes locks further attempts out for that window.
- CSRF: session-bound token, required on every mutating request.
- XSS: `e()` global helper (`htmlspecialchars`, `ENT_QUOTES`) used in every view
  for any dynamic output; no raw `<?= $var ?>` for user-supplied data.
- SQL injection: 100% PDO prepared statements; the only interpolated SQL
  fragments are table/column/order-by identifiers, which are whitelisted
  through a `[a-zA-Z0-9_]+` regex guard in `Model` before use.
- Session fixation: session ID regenerated on login and periodically during
  a long-lived session.
- Errors: raw PHP/PDO exceptions are never shown to end users in production;
  `set_exception_handler` renders `views/errors/500.php` and logs the real
  trace to `storage/logs/php-error.log`. Debug mode is a single `.env` flag.

## Views

Plain PHP templates, no template-engine dependency. `layouts/app.php` (public
site), `layouts/admin.php` (admin shell), `layouts/auth.php` (centered auth
card) each wrap a `content` view. Partials (`views/partials/*`) hold the nav,
footer, and admin sidebar/topbar so they're defined once.

## Admin panel & CRM (Phase 3)

Every `App\Models\*` class beyond `User` follows the same shape: thin wrappers
around `App\Core\Model` plus a handful of query methods for the joins each
admin screen needs (e.g. `Job::listWithCompany()`, `Company::findWithIndustry()`).
Controllers stay thin — they validate, call one or two model methods, and
render a view or redirect; no business logic lives in views.

- **Jobs / Industries / Services** (`app/Controllers/Admin/{Job,Industry,Service}Controller.php`):
  standard server-rendered CRUD. Industries/services are hard-delete lookup
  tables (`$softDeletes = false`, `$timestamps = false` on their models);
  `IndustryController::destroy()` checks `Industry::inUseCount()` first and
  refuses to delete an industry still referenced by a job/company/staffing
  request, rather than leaving orphaned foreign keys.
- **Employers** (`CompanyController`): list/create/edit plus a `show()` page
  that is the CRM-lite surface — company overview, contacts, its jobs and
  staffing requests, and an `employer_activities` timeline with a form to log
  a new note/call/email/meeting inline. A status change (e.g. `lead` →
  `active`) auto-logs a `status_change` timeline entry so the history is
  never hand-maintained.
- **Candidates** (`CandidateController`): `show()` renders the full profile
  (skills, experience, education) with inline status and admin-notes forms.
  `index()` renders an empty shell; all data comes from `search()`, the one
  controller action in this phase that returns JSON instead of a view —
  everything else stays server-rendered by design (see Request lifecycle above).
- **Candidate search** (`GET /admin/candidates/search`, `app/Models/Candidate.php::search()`):
  builds a parameterized `WHERE` clause from keyword/status/availability/
  location/experience-range/remote/skill filters, runs a `COUNT` query and a
  `GROUP_CONCAT`-joined data query, and returns `{data, meta}` JSON.
  `public/assets/js/admin-candidates.js` is a dependency-free `fetch()` +
  `AbortController` client: text/number inputs debounce 300ms, selects/
  checkboxes re-query immediately, and an in-flight request is aborted if a
  newer one starts — the page never reloads.
  - **Gotcha worth knowing**: `App\Core\Database` disables
    `PDO::ATTR_EMULATE_PREPARES` to get real server-side prepared statements.
    MySQL's native prepare protocol rejects a named parameter used more than
    once in the same query (unlike emulated mode) — e.g. `:qlike` can't
    appear in five `OR` branches. `Candidate::buildSearchWhere()` gives each
    occurrence its own placeholder (`:qlike1`..`:qlike5`) bound to the same
    value. Keep this in mind before adding new multi-branch filters.
- **Staffing Requests** (`StaffingRequestController`): list/detail/status-update
  now; matching against candidates is Phase 5.
- **Settings** (`SettingController` + `app_settings` key/value table): minimal
  by design — just enough to back the sidebar link without dead-ending it.

## Public frontend & multi-step wizards (Phase 4)

- **Job Board** (`app/Controllers/Public/JobBoardController.php`,
  `Job::publicSearch()`/`Job::findOpenBySlug()`): always scoped to
  `status = 'open'`; `apply()` branches three ways — already applied (badge),
  logged in as a candidate (creates `job_applications` directly), or a guest
  (stores the job slug in `Session::set('apply_job_slug', ...)` and sends
  them into the registration wizard, which completes the application on
  final submit — see below).
- **Candidate registration** (`CandidateRegistrationController`): a 6-step
  wizard (account → profile → professional → skills → resume → review), each
  step a `GET` (render, pre-filled from the draft) + `POST` (validate, merge
  into the draft, advance) pair. The draft lives in
  `Session::get('candidate_draft')` as a plain array with a `step` counter;
  `requireStep($n)` bounces any request for step `$n` back to wherever the
  user actually left off if `$n` is beyond `draft['step']` — this is what
  stops someone from POSTing straight to `/review` with an empty draft.
  Nothing touches the `users`/`candidates` tables until the final `submit()`,
  which creates both rows, attaches the resume file (see below), creates any
  new skills, logs the candidate in via `Auth::login()`, and — if
  `apply_job_slug` is set — inserts the pending `job_applications` row.
  **Why session-backed instead of a persisted drafts table**: durable,
  resumable-by-email drafts would need outbound email, which doesn't exist
  until Phase 5's notification system. A session draft is what's honest to
  build today; upgrading to a DB-backed draft with a resume link is a
  reasonable Phase 5+ enhancement, not a Phase 4 gap.
- **Resume upload** (`app/Services/FileUploadService.php`): validates real
  extension + `finfo`-detected MIME + size against `config/app.php`'s
  `uploads` block, stores the file under `storage/uploads/resumes` with a
  random filename, and inserts a `files` row with `entity_id = 0`
  ("unattached"). `FileRecord::attach()` retroactively sets the real
  `entity_type`/`entity_id` once the candidate row exists — this lets a file
  be uploaded mid-wizard, before there's anything to attach it to yet,
  without a schema change (there's no FK on `files.entity_id`).
- **Hire Talent** (`HireTalentController`): the same session-draft pattern,
  4 steps (company/contact → role → requirements/budget → review), but
  simpler — no account is created. It inserts directly into
  `staffing_requests` with `company_id = null` and the contact captured
  inline, matching the schema note in DATABASE.md that a `staffing_requests`
  row can predate any `companies` row. Conditional logic
  (`public/assets/js/hire-talent-wizard.js`) shows a temp/temp-to-hire
  budget hint and disables the city/state inputs when "remote OK" is
  checked (disabled inputs don't submit, so the fields are simply omitted
  rather than sent empty).
- **Client-side widgets, all dependency-free vanilla JS**:
  `tag-input.js` (skills — Enter/comma to add, backspace to remove, feeds a
  hidden comma-separated field), `dropzone.js` (drag-and-drop resume upload
  with a native `<input type="file">` underneath for click-to-browse and
  accessibility), `admin-candidates.js` (Phase 3's debounced AJAX search).

## Matching & notifications (Phase 5)

- **Matching** (`app/Services/MatchingService.php`): a deliberately
  transparent, rule-based scorer — no ML — so a recruiter can see exactly
  why a candidate ranked where they did. 100 points split across must-have
  skills (40), nice-to-have skills (15), location/remote fit (15),
  employment-type match (10), availability (10), and budget-vs-salary
  overlap (10); `matchTypeFor()` buckets the total into `strong_match`
  (≥65), `needs_review` (≥35), or `rejected` (below — not surfaced in the
  admin UI). Skill matching handles the mismatch between free-text employer
  requirements ("forklift certification") and short candidate skill tags
  ("Forklift Operation") by falling back from a whole-phrase substring check
  to shared-significant-word overlap (`phrasesOverlap()`) — a pure substring
  check was tested and missed obvious matches, which is why the fallback
  exists. `Admin\MatchController::forRequest()` runs this live (nothing
  persisted) so a recruiter can browse rankings before committing; only
  `saveForRequest()` writes a `matches` row, logs an `employer_activities`
  entry, and notifies the candidate.
  **Naming note**: the model is `App\Models\MatchRecord`, not `Match` —
  `match` has been a reserved keyword since PHP 8.0's match-expression, so
  `class Match` is a fatal parse error.
- **Notifications** (`app/Services/NotificationService.php`): every
  templated email (`views/emails/*.php`, wrapped by `views/emails/layout.php`)
  is queued as a `notifications` row and delivered by a swappable driver
  (`MAIL_DRIVER`): `log` (dev default) appends a plain-text rendering to
  `storage/logs/mail.log` and marks the row `sent` immediately; `smtp` sends
  it for real over a hand-rolled SMTP client
  (`app/Services/SmtpMailer.php` — EHLO/STARTTLS-or-SSL/AUTH LOGIN/MAIL/RCPT/
  DATA over a raw socket, RFC-5321 dot-stuffing, no library — consistent with
  the project's zero-Composer-dependency stance) configured via
  `MAIL_HOST`/`MAIL_PORT`/`MAIL_ENCRYPTION`/`MAIL_USERNAME`/`MAIL_PASSWORD`
  in `.env`. A failed SMTP delivery marks the row `failed` and appends the
  SMTP server's error to `storage/logs/mail.log` instead of silently
  dropping it. `queueAdminAlert()` fans a message out to every active admin/super_admin;
  `queueUserNotification()` targets one user. Wired into: candidate
  registration (alerts admins), staffing-request submission (alerts admins),
  job application (alerts admins), a saved match (notifies the candidate),
  and an admin status change to `shortlisted`/`placed` (notifies the
  candidate). The admin sidebar's "Notifications" link
  (`Admin\NotificationController`) shows the last 50 queued alerts, with an
  unread-last-24h count badge.

## Form UX & accessibility (Phase 5)

- Every public-facing form (login, both wizards) now renders **inline
  per-field errors** via `field_error()`/`field_class()` (`app/helpers.php`)
  in addition to the top-of-form summary used elsewhere — `views/partials/
  form-alert.php` is the lighter alert partial for pages that do this, so
  the same message isn't shown twice.
- Skip-to-content links (`.skip-link`, visually hidden until focused) on the
  public and admin layouts; every admin data table header has `scope="col"`;
  focus states use a visible box-shadow ring (see `:focus` rules in
  `app.css`) rather than the browser default being suppressed.
- `public/.htaccess` sets long-lived cache headers + gzip for static assets
  and baseline hardening headers (`X-Content-Type-Options`,
  `X-Frame-Options`, `Referrer-Policy`) for Apache deployments.

## Visual design system v2

The original CSS pass (Phase 4) was functional but generic — system fonts,
flat cards, no imagery. It was rebuilt wholesale rather than patched:

- **Typography**: Fraunces (a serif with real editorial character) for all
  headings, Inter for body/UI, loaded from Google Fonts with a full
  system-font fallback stack so the site never blocks on the CDN.
- **Imagery**: there is no stock-photo library wired in (fetching real photos
  would mean inventing URLs, which isn't done here) — imagery is instead
  custom-authored: a hand-built abstract SVG hero illustration
  (`views/partials/hero-illustration.php`, floating candidate/employer/match
  cards with subtle CSS-driven float/pulse animation, respecting
  `prefers-reduced-motion`), and a small hand-authored inline icon set
  (`App\Helpers\Icon`, ~28 stroke icons, `icon($name)` global helper) used
  throughout instead of an external icon font/CDN.
- **Avatars**: candidates and companies have no photo/logo upload yet, so
  `avatar($name)` (`app/helpers.php`) renders an initials badge colored
  deterministically from the name's character sum (same person always gets
  the same hue) — the same pattern Linear/Notion/GitHub use for the same
  reason.
- **Depth & color**: a layered shadow scale (`--shadow-xs` through
  `--shadow-lg`), a warm neutral palette extended with gold (ratings) and a
  light/dark variant of each semantic color for badges, gradient-mesh
  backgrounds on hero/auth/wizard surfaces instead of flat fills.
- **Admin shell**: sidebar recolored to a solid dark teal (`--color-primary-
  dark`) with icon+label nav items and a left-edge accent bar on the active
  item, replacing the plain white sidebar with text-only links.
- Applied across the homepage, job board, job detail, admin dashboard,
  candidate/company detail and list pages, the AJAX candidate-search cards
  (`admin-candidates.js` renders the same avatar/icon markup client-side),
  match cards, and both onboarding wizards.

While reworking the header partials, a real bug surfaced and was fixed: a
session can outlive its account (the user row gets deleted/deactivated while
they're still "logged in"), which rendered a broken half-authenticated nav
with no CSRF token. `AuthMiddleware` now checks `Auth::user() !== null` and
forces a clean logout + redirect if the account is gone, instead of letting
the request continue with a stale session.

## Known gaps (not yet built)

- **Avatar/logo upload**: `config/app.php`'s `uploads` block and the `files`
  table already have `avatar`/`logo` paths and MIME whitelists wired, and
  `FileUploadService::store()` already accepts an `avatar` kind — but no
  controller/view actually exposes an upload form for it yet. Candidates and
  companies currently only ever get the deterministic initials badge from
  `avatar()` (`app/helpers.php`).
- **Persisted registration drafts**: the 6-step candidate wizard
  (`CandidateRegistrationController`) keeps its draft in
  `Session::get('candidate_draft')` only. If the session dies mid-wizard
  (expiry, browser close, cookie clear) the draft is gone with no way to
  resume via an emailed link — moving it to a DB-backed draft table (now that
  Phase 5 added real outbound email — see Notifications above) is the
  natural next step, not a Phase 4 gap anymore.
- **No automated test suite**: correctness has been verified so far by
  `php -l`, manual curl smoke tests, and in-browser exercising of each flow —
  there is no PHPUnit (or equivalent) regression suite yet.
- **No DB migration tooling**: `database/schema.sql` is applied once at setup;
  there's no versioned migration runner for evolving the schema afterward.
