-- =====================================================================
-- TalentBridge Partners — Development Seed Data
-- Run AFTER schema.sql. Safe to re-run against a fresh database only
-- (uses fixed UUIDs / unique keys, will fail on duplicate re-seed).
-- =====================================================================

USE staffing_platform;

-- ---------------------------------------------------------------------
-- Roles
-- ---------------------------------------------------------------------
INSERT INTO roles (id, slug, name, description) VALUES
    (1, 'super_admin', 'Super Admin', 'Full platform access, including staff and settings management.'),
    (2, 'admin', 'Admin', 'Recruiting operations: candidates, employers, jobs, matching.'),
    (3, 'employer', 'Employer', 'Company representative submitting staffing requests and reviewing candidates.'),
    (4, 'candidate', 'Candidate', 'Talent pool member with a profile and job applications.');

-- ---------------------------------------------------------------------
-- Seeded users
--   admin@talentbridge.test      / AdminDev!2026
--   recruiter@talentbridge.test  / EmployerDev!2026   (employer demo account)
--   candidate@talentbridge.test  / CandidateDev!2026  (candidate demo account)
-- Passwords are bcrypt-hashed (cost 12). Change these before any shared/staging deploy.
-- ---------------------------------------------------------------------
INSERT INTO users (id, uuid, email, password_hash, role_id, status, email_verified_at) VALUES
    (1, 'd1050559-d95b-4471-bc47-fd3f7c423d78', 'admin@talentbridge.test', '$2y$12$ywp660W2AZdrOH92jqw6ROyrV34FI93CiCLcIOIJNh088DsajTxOe', 1, 'active', NOW()),
    (2, '53b97b92-f550-49c7-802a-0cd764f2652f', 'employer@talentbridge.test', '$2y$12$jVvG.HHxGLwjQHD1ieSVFu5Av/dabbz5PnEnjqGnVd.6wifreC5/6', 3, 'active', NOW()),
    (3, '2bc86fee-454d-4589-b3d1-d34119cf2d48', 'candidate@talentbridge.test', '$2y$12$DSEVl5OSXmriR71jQJ9UeuHxJApKJ9zNsAt5SxaYRHbzmqq7FQtc6', 4, 'active', NOW());

-- ---------------------------------------------------------------------
-- Industries
-- ---------------------------------------------------------------------
INSERT INTO industries (id, name, slug) VALUES
    (1, 'Information Technology', 'information-technology'),
    (2, 'Healthcare', 'healthcare'),
    (3, 'Financial Services', 'financial-services'),
    (4, 'Manufacturing & Logistics', 'manufacturing-logistics'),
    (5, 'Construction & Skilled Trades', 'construction-skilled-trades'),
    (6, 'Professional & Administrative', 'professional-administrative');

-- ---------------------------------------------------------------------
-- Services
-- ---------------------------------------------------------------------
INSERT INTO services (id, name, slug, description, icon, sort_order) VALUES
    (1, 'Direct Hire', 'direct-hire', 'Permanent placement search for full-time roles.', 'briefcase', 1),
    (2, 'Temporary Staffing', 'temporary-staffing', 'Flexible short-term and seasonal workforce support.', 'clock', 2),
    (3, 'Temp-to-Hire', 'temp-to-hire', 'Trial engagement leading to a permanent offer.', 'arrow-right-circle', 3),
    (4, 'Executive Search', 'executive-search', 'Retained search for leadership and executive roles.', 'award', 4),
    (5, 'Payrolling', 'payrolling', 'We manage payroll for workers you have already sourced.', 'file-text', 5);

-- ---------------------------------------------------------------------
-- Skills (representative sample; expand via admin panel over time)
-- ---------------------------------------------------------------------
INSERT INTO skills (name, category) VALUES
    ('JavaScript', 'Software Development'), ('PHP', 'Software Development'), ('Python', 'Software Development'),
    ('SQL', 'Software Development'), ('React', 'Software Development'), ('Project Management', 'Operations'),
    ('Customer Service', 'Administrative'), ('Data Entry', 'Administrative'), ('Bookkeeping', 'Finance'),
    ('Accounts Payable', 'Finance'), ('Forklift Operation', 'Manufacturing & Logistics'),
    ('Warehouse Management', 'Manufacturing & Logistics'), ('Electrical Wiring', 'Construction & Skilled Trades'),
    ('HVAC', 'Construction & Skilled Trades'), ('Registered Nursing', 'Healthcare'),
    ('Medical Billing', 'Healthcare'), ('Payroll Processing', 'Finance'), ('Recruiting', 'Human Resources'),
    ('Sales', 'Business Development'), ('Executive Assistance', 'Administrative');

-- ---------------------------------------------------------------------
-- Sample company + contact + job (illustrates the employer flow end to end)
-- ---------------------------------------------------------------------
INSERT INTO companies (id, uuid, name, slug, industry_id, website, size_range, description, headquarters_city, headquarters_state, status, owner_admin_id) VALUES
    (1, '859bab10-13d0-4d12-9dba-10568f192121', 'Meridian Logistics Group', 'meridian-logistics-group', 4,
     'https://example.com', '201-500', 'Regional third-party logistics provider serving the Midwest.',
     'Columbus', 'OH', 'active', 1);

INSERT INTO company_contacts (company_id, user_id, full_name, email, phone, job_title, is_primary) VALUES
    (1, 2, 'Jordan Reyes', 'employer@talentbridge.test', '614-555-0142', 'Director of Operations', 1);

INSERT INTO jobs (id, uuid, company_id, industry_id, service_id, title, slug, employment_type, location_city, location_state, is_remote, salary_min, salary_max, salary_period, description, requirements, benefits, positions_available, status, created_by, published_at) VALUES
    (1, '819e0ad8-6f44-4099-8998-71957b186519', 1, 4, 2, 'Warehouse Shift Supervisor', 'warehouse-shift-supervisor-meridian',
     'temp_to_hire', 'Columbus', 'OH', 0, 52000, 61000, 'year',
     'Oversee a 20-person warehouse crew across receiving, put-away, and outbound shipping for a growing 3PL client.',
     '3+ years warehouse leadership experience. Comfortable with WMS software and forklift-certified staff scheduling.',
     'Medical/dental after 90 days, weekly pay, path to permanent placement.',
     2, 'open', 1, NOW());

-- ---------------------------------------------------------------------
-- Sample candidate (proves the "register even with no open jobs" flow)
-- ---------------------------------------------------------------------
INSERT INTO candidates (id, uuid, user_id, first_name, last_name, phone, location_city, location_state, headline, summary, current_title, experience_years, availability, employment_types, salary_expectation_min, salary_expectation_max, is_remote_ok, status, source) VALUES
    (1, '138018fb-bacf-411f-9e62-a07d3ef41617', 3, 'Alex', 'Morgan', '614-555-0199', 'Columbus', 'OH',
     'Warehouse Operations Lead with 6 years in 3PL environments',
     'Reliable operations professional with a track record of reducing pick errors and improving on-time shipping rates.',
     'Warehouse Lead', 6.0, 'immediate', 'full_time,temp_to_hire', 50000, 60000, 0, 'new', 'self_registered');

INSERT INTO candidate_skills (candidate_id, skill_id, proficiency) VALUES
    (1, 11, 'expert'), (1, 12, 'advanced'), (1, 6, 'intermediate');

INSERT INTO candidate_experience (candidate_id, company_name, job_title, start_date, end_date, is_current, description, sort_order) VALUES
    (1, 'Buckeye Freight Solutions', 'Warehouse Lead', '2021-03-01', NULL, 1,
     'Lead a 12-person receiving team; implemented a cycle-count process that cut inventory variance by 30%.', 1),
    (1, 'Central Ohio Distribution', 'Warehouse Associate', '2018-06-01', '2021-02-15', 0,
     'Picked, packed, and shipped B2B orders in a high-volume distribution center.', 2);

-- ---------------------------------------------------------------------
-- Sample staffing request (employer intake independent of a job post)
-- ---------------------------------------------------------------------
INSERT INTO staffing_requests (uuid, company_id, contact_name, contact_email, contact_phone, role_title, industry_id, service_id, employment_type, positions_needed, budget_min, budget_max, must_have_skills, location_city, location_state, is_remote_ok, start_date_needed, additional_notes, status, assigned_admin_id) VALUES
    ('bfe97ddd-b441-46b1-bff3-63b9038367d5', 1, 'Jordan Reyes', 'employer@talentbridge.test', '614-555-0142',
     'Forklift Operators (3x)', 4, 2, 'temp', 3, 18, 22, 'Forklift certification, 1+ year warehouse experience',
     'Columbus', 'OH', 0, DATE_ADD(CURDATE(), INTERVAL 14 DAY),
     'Second shift, overtime available during peak season.', 'new', 1);

-- ---------------------------------------------------------------------
-- App settings
-- ---------------------------------------------------------------------
INSERT INTO app_settings (setting_key, value) VALUES
    ('site_tagline', 'Professional staffing, matched with precision.'),
    ('support_email', 'support@talentbridge.test');
