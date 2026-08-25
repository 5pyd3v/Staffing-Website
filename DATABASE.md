# Database Reference

MySQL 8+ / MariaDB 10.4+, InnoDB, `utf8mb4_unicode_ci`. Full DDL lives in
[`database/schema.sql`](database/schema.sql); development fixtures in
[`database/seed.sql`](database/seed.sql).

## Conventions

- Every table has an auto-increment `id BIGINT/INT UNSIGNED` primary key for
  fast joins, **plus** a `uuid CHAR(36)` used in URLs/APIs so internal row
  counts/order are never exposed externally.
- Soft deletes (`deleted_at TIMESTAMP NULL`) on business-record tables
  (`users`, `candidates`, `companies`, `jobs`, `staffing_requests`). Pure
  lookup tables (`roles`, `industries`, `services`, `skills`) are hard-delete
  only — they have no ownership/history to preserve.
- `created_at`/`updated_at` on every mutable table; `updated_at` uses
  `ON UPDATE CURRENT_TIMESTAMP` so the app never has to set it manually.
- Foreign keys are always indexed and always declared with an explicit
  `ON DELETE` policy (`CASCADE` for owned children, `SET NULL` for optional
  references, never a silent default).

## Entity map

```
roles ──< users ──< candidates ──< candidate_skills >── skills
                │         │  ├──< candidate_experience
                │         │  └──< candidate_education
                │         └──< job_applications >── jobs
                │
                └──< companies ──< company_contacts
                          │  ├──< employer_activities
                          │  ├──< jobs ──< job_applications
                          │  └──< staffing_requests ──< employer_activities
                          │
                staffing_requests ──< matches >── candidates
                jobs ─────────────────< matches

files  <-- polymorphic (entity_type, entity_id): candidate resumes/avatars, company logos
notifications  <-- polymorphic (related_entity_type, related_entity_id)
audit_logs      <-- generic action log (JSON meta column)
login_attempts, password_resets  <-- auth support tables
app_settings    <-- key/value site configuration
```

## Table-by-table notes

### `roles` / `users`
RBAC is data-driven: `roles.slug` (`super_admin`, `admin`, `employer`,
`candidate`) is what `App\Core\Auth`/`RoleMiddleware` check against, not a
hardcoded enum in PHP. `users` holds only auth concerns (email, password hash,
role, status); profile data lives in `candidates` (1:1) or `company_contacts`
(a company contact *may* have a `users` row if they need portal login).

### `candidates`
The talent pool. Deliberately decoupled from any specific job — a candidate
can (and, per the product spec, *must be able to*) register with zero open
jobs. `employment_types` is a `SET` (multi-select: full_time, part_time,
contract, temp, temp_to_hire) since a candidate may be open to more than one
arrangement. A `FULLTEXT` index on `(headline, summary, current_title)`
backs the admin free-text search; `candidate_skills` (many-to-many with
`skills`) backs structured skill filtering.

### `companies` / `company_contacts` / `employer_activities`
`companies.status` (`lead` → `active` / `inactive`) plus `employer_activities`
(typed timeline: note/call/email/meeting/status_change, each optionally tied
to a `staffing_request_id`) is the CRM-lite layer — a lightweight, in-house
alternative to a full CRM integration.

### `jobs` / `job_applications`
A published requisition tied to a company. `job_applications` is the
candidate-initiated counterpart to admin-driven `matches` — a candidate can
apply directly to a posted job; `unique(job_id, candidate_id)` prevents
duplicate applications.

### `staffing_requests`
The employer intake form target (Phase 4's multi-step "Hire Talent" flow).
Distinct from `jobs` because an employer request often arrives *before* a
formal job post exists (or never becomes one — e.g. a one-off temp order).
`company_id` is nullable because a brand-new employer's first contact may
predate a `companies` row; the contact fields (`contact_name/email/phone`)
are captured inline and a company record can be created/linked afterward.

### `matches`
The output of the rule-based matching system: links a `candidate` to either
a `staffing_request` or a `job` (or both, over time) with a `match_type`
(`strong_match` / `needs_review` / `rejected`), a numeric `score`, and its own
status lifecycle (`proposed` → `presented_to_employer` → `interviewing` →
`hired`/`rejected`) independent of the underlying application/request status.

### `files`
One polymorphic table for every uploaded file (`entity_type` +
`entity_id`: `candidate_resume`, `candidate_avatar`, `company_logo`, ...)
rather than a separate table per upload type. Stores the original filename
alongside a generated `stored_name` (never trust the client-supplied name for
the on-disk path) and the resolved `mime_type`/`size_bytes` captured at
upload time for audit purposes.

### `notifications` / `audit_logs`
`notifications` is the outbox for email-ready alerts (`status`: pending →
sent/failed) with a polymorphic `related_entity_type/id` so any table can be
the subject. `audit_logs` is a generic "who did what" trail with a `JSON meta`
column for action-specific detail, independent of `notifications`.

### `login_attempts` / `password_resets`
Support brute-force lockout (`App\Core\Auth`: 5 failures / 15 minutes per
email+IP) and future self-service password reset (hashed token, expiry,
single-use `used_at`).

## Seeded data

`seed.sql` inserts: the 4 roles; 3 dev accounts (see `README.md` for
credentials); 6 industries; 5 services (Direct Hire, Temporary Staffing,
Temp-to-Hire, Executive Search, Payrolling); 20 representative skills; one
sample company/contact/job; one sample candidate with skills/experience; and
one sample staffing request — enough to exercise every relationship in the
schema without hand-building fixtures through the UI.
