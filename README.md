# TalentBridge Partners — Staffing & Recruitment Platform

A production-grade staffing/recruitment marketplace: candidates join a talent pool,
employers submit staffing requests, and internal admins run search/matching between
the two — all on a vanilla PHP 8 + MySQL stack with no framework dependency.

See [`ARCHITECTURE.md`](ARCHITECTURE.md) for the application design and
[`DATABASE.md`](DATABASE.md) for the schema reference.

## Requirements

- PHP 8.2+ with `pdo_mysql`, `mbstring`, `fileinfo`, `gd`, `openssl`
- MySQL 8+ or MariaDB 10.4+
- No Composer/npm dependency is required to run the app (see Architecture notes on why)

This was developed against a local **XAMPP** stack (Apache + MariaDB 10.4) on Windows,
but runs equally well under the PHP built-in server for development.

## Setup

1. **Configure environment**

   ```bash
   cp .env.example .env
   ```

   Edit `.env` with your database credentials. Defaults match a stock XAMPP install
   (`root` user, no password, host `127.0.0.1`).

2. **Create the database**

   ```bash
   mysql -u root < database/schema.sql
   mysql -u root < database/seed.sql
   ```

   `schema.sql` creates the `staffing_platform` database and all tables.
   `seed.sql` adds roles, reference data (industries/services/skills), and three
   development accounts (see below).

3. **Run the app**

   - **XAMPP/Apache**: point a vhost's document root at `/public` (mod_rewrite must
     be enabled — `.htaccess` handles clean URLs).
   - **PHP built-in server** (fastest for local dev):

     ```bash
     php -S localhost:8000 -t public public/router.php
     ```

     `public/router.php` is only used by the built-in server to emulate the
     `.htaccess` rewrite rule; it is inert under Apache/Nginx.

4. Visit `http://localhost:8000`.

## Seeded development accounts

| Role      | Email                          | Password            |
|-----------|---------------------------------|----------------------|
| Admin     | admin@talentbridge.test         | `AdminDev!2026`      |
| Employer  | employer@talentbridge.test      | `EmployerDev!2026`   |
| Candidate | candidate@talentbridge.test     | `CandidateDev!2026`  |

**Change or remove these before any shared/staging/production deployment.**

## Project layout

```
app/            Application code (Core framework, Controllers, Models, Middleware, Helpers)
config/         Environment loading, app config, DB config, route table, bootstrap
database/       schema.sql, seed.sql
public/         Web root — front controller, .htaccess, compiled assets
storage/        Uploads (resumes, avatars, logos) and logs — never web-accessible directly
views/          Plain-PHP templates (layouts, partials, per-role pages)
```

## Status

This repository is being built in phases (see `ARCHITECTURE.md` for the full list).
Complete and tested so far:

- **Phase 1** — scaffolding, schema, seed data
- **Phase 2** — auth/RBAC core
- **Phase 3** — Admin dashboard with live stats; full CRUD for Jobs, Industries,
  and Services; Employer management with a CRM-lite activity timeline;
  Candidate management (profile view, status, admin notes); an AJAX-powered
  candidate search/filter interface (skill, location, experience, availability,
  keyword — no full page reloads); a Settings screen for site-wide key/value config.
- **Phase 4** — Public homepage (dynamic services + latest open jobs); the
  Job Board (search/filter/pagination + job detail + apply); a 6-step,
  session-backed candidate registration wizard (account → profile →
  professional background → skills → drag-and-drop resume upload → review)
  that logs the candidate in and completes any pending job application on
  submit; a 4-step "Hire Talent" staffing-request wizard with conditional
  logic (temp/temp-to-hire hint, remote toggling location fields); and the
  expanded design system (wizard shell, job cards, tag input, dropzone).
- **Phase 5** — Rule-based talent matching (transparent 100-point scoring
  across skills/location/employment type/availability/budget, with a ranked
  Strong Match / Needs Review view per staffing request and one-click
  save-and-notify); a full notification architecture (HTML email templates,
  an admin alert feed, a swappable mail driver — `log` writes to
  `storage/logs/mail.log` in dev, `smtp` sends real email over a
  dependency-free SMTP client, see `MAIL_*` in `.env.example`); inline
  per-field validation on every public-facing form; a security/CSRF audit
  (closed a missing check on logout); a protected admin resume-download
  endpoint; and an accessibility pass (skip-to-content links, table header
  scopes, visible focus states).
