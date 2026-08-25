-- =====================================================================
-- TalentBridge Partners — Staffing & Recruitment Marketplace
-- Core Schema (MySQL 8+ / MariaDB 10.4+)
-- =====================================================================
-- Conventions:
--   * Every table has an auto-increment `id` (fast joins/indexes) plus a
--     `uuid` CHAR(36) used in URLs/APIs so internal IDs are never exposed.
--   * Soft deletes via `deleted_at` on entities that represent business
--     records (users, candidates, companies, jobs, ...). Lookup/reference
--     tables (roles, industries, skills) are hard-delete only.
--   * All timestamps are UTC `TIMESTAMP`/`DATETIME`.
--   * InnoDB + utf8mb4 everywhere for FK support and full unicode.
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS staffing_platform
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE staffing_platform;

-- ---------------------------------------------------------------------
-- Roles & Users (Auth + RBAC)
-- ---------------------------------------------------------------------

CREATE TABLE roles (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug         VARCHAR(30) NOT NULL UNIQUE,
    name         VARCHAR(60) NOT NULL,
    description  VARCHAR(255) NULL,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE users (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid            CHAR(36) NOT NULL UNIQUE,
    email           VARCHAR(190) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    role_id         INT UNSIGNED NOT NULL,
    status          ENUM('active','inactive','pending','suspended') NOT NULL DEFAULT 'active',
    email_verified_at TIMESTAMP NULL DEFAULT NULL,
    last_login_at   TIMESTAMP NULL DEFAULT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id),
    INDEX idx_users_role (role_id),
    INDEX idx_users_status (status)
) ENGINE=InnoDB;

CREATE TABLE login_attempts (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email         VARCHAR(190) NOT NULL,
    ip_address    VARCHAR(45) NOT NULL,
    success       TINYINT(1) NOT NULL DEFAULT 0,
    attempted_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_login_attempts_email (email),
    INDEX idx_login_attempts_ip (ip_address),
    INDEX idx_login_attempts_time (attempted_at)
) ENGINE=InnoDB;

CREATE TABLE password_resets (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       BIGINT UNSIGNED NOT NULL,
    token_hash    VARCHAR(255) NOT NULL,
    expires_at    TIMESTAMP NOT NULL,
    used_at       TIMESTAMP NULL DEFAULT NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_password_resets_user (user_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Files (generic polymorphic attachment store: resumes, avatars, logos)
-- ---------------------------------------------------------------------

CREATE TABLE files (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid           CHAR(36) NOT NULL UNIQUE,
    original_name  VARCHAR(255) NOT NULL,
    stored_name    VARCHAR(255) NOT NULL,
    disk_path      VARCHAR(500) NOT NULL,
    mime_type      VARCHAR(100) NOT NULL,
    size_bytes     INT UNSIGNED NOT NULL,
    entity_type    VARCHAR(50) NOT NULL,   -- 'candidate_resume', 'candidate_avatar', 'company_logo'
    entity_id      BIGINT UNSIGNED NOT NULL,
    uploaded_by    BIGINT UNSIGNED NULL,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_files_uploaded_by FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_files_entity (entity_type, entity_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Reference / lookup tables
-- ---------------------------------------------------------------------

CREATE TABLE industries (
    id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name  VARCHAR(100) NOT NULL UNIQUE,
    slug  VARCHAR(120) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE services (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100) NOT NULL UNIQUE,
    slug         VARCHAR(120) NOT NULL UNIQUE,
    description  VARCHAR(500) NULL,
    icon         VARCHAR(50) NULL,
    is_active    TINYINT(1) NOT NULL DEFAULT 1,
    sort_order   SMALLINT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE skills (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name      VARCHAR(100) NOT NULL UNIQUE,
    category  VARCHAR(80) NULL,
    INDEX idx_skills_category (category)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Candidates & Talent Pool
-- ---------------------------------------------------------------------

CREATE TABLE candidates (
    id                     BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid                   CHAR(36) NOT NULL UNIQUE,
    user_id                BIGINT UNSIGNED NOT NULL UNIQUE,
    first_name             VARCHAR(100) NOT NULL,
    last_name              VARCHAR(100) NOT NULL,
    phone                  VARCHAR(30) NULL,
    location_city          VARCHAR(100) NULL,
    location_state         VARCHAR(100) NULL,
    location_country       VARCHAR(100) NULL DEFAULT 'United States',
    headline               VARCHAR(180) NULL,
    summary                TEXT NULL,
    current_title          VARCHAR(150) NULL,
    experience_years        DECIMAL(4,1) NULL,
    availability           ENUM('immediate','2_weeks','1_month','not_looking') NOT NULL DEFAULT 'immediate',
    employment_types       SET('full_time','part_time','contract','temp','temp_to_hire') NOT NULL DEFAULT 'full_time',
    salary_expectation_min INT UNSIGNED NULL,
    salary_expectation_max INT UNSIGNED NULL,
    salary_currency        CHAR(3) NOT NULL DEFAULT 'USD',
    is_remote_ok           TINYINT(1) NOT NULL DEFAULT 0,
    resume_file_id         BIGINT UNSIGNED NULL,
    avatar_file_id         BIGINT UNSIGNED NULL,
    linkedin_url           VARCHAR(255) NULL,
    portfolio_url          VARCHAR(255) NULL,
    status                 ENUM('new','in_review','shortlisted','placed','inactive') NOT NULL DEFAULT 'new',
    source                 VARCHAR(80) NULL DEFAULT 'self_registered',
    admin_notes            TEXT NULL,
    created_at             TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at             TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at             TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_candidates_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_candidates_resume FOREIGN KEY (resume_file_id) REFERENCES files(id) ON DELETE SET NULL,
    CONSTRAINT fk_candidates_avatar FOREIGN KEY (avatar_file_id) REFERENCES files(id) ON DELETE SET NULL,
    INDEX idx_candidates_status (status),
    INDEX idx_candidates_location (location_city, location_state),
    INDEX idx_candidates_experience (experience_years),
    FULLTEXT INDEX ftx_candidates_search (headline, summary, current_title)
) ENGINE=InnoDB;

CREATE TABLE candidate_skills (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    candidate_id     BIGINT UNSIGNED NOT NULL,
    skill_id         INT UNSIGNED NOT NULL,
    proficiency      ENUM('beginner','intermediate','advanced','expert') NOT NULL DEFAULT 'intermediate',
    CONSTRAINT fk_candidate_skills_candidate FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
    CONSTRAINT fk_candidate_skills_skill FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE,
    UNIQUE KEY uq_candidate_skill (candidate_id, skill_id),
    INDEX idx_candidate_skills_skill (skill_id)
) ENGINE=InnoDB;

CREATE TABLE candidate_experience (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    candidate_id  BIGINT UNSIGNED NOT NULL,
    company_name  VARCHAR(150) NOT NULL,
    job_title     VARCHAR(150) NOT NULL,
    start_date    DATE NOT NULL,
    end_date      DATE NULL,
    is_current    TINYINT(1) NOT NULL DEFAULT 0,
    description   TEXT NULL,
    sort_order    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT fk_candidate_experience_candidate FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
    INDEX idx_candidate_experience_candidate (candidate_id)
) ENGINE=InnoDB;

CREATE TABLE candidate_education (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    candidate_id   BIGINT UNSIGNED NOT NULL,
    institution    VARCHAR(150) NOT NULL,
    degree         VARCHAR(150) NULL,
    field_of_study VARCHAR(150) NULL,
    start_year     SMALLINT UNSIGNED NULL,
    end_year       SMALLINT UNSIGNED NULL,
    sort_order     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT fk_candidate_education_candidate FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
    INDEX idx_candidate_education_candidate (candidate_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Companies (Employers) & CRM-lite
-- ---------------------------------------------------------------------

CREATE TABLE companies (
    id                   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid                 CHAR(36) NOT NULL UNIQUE,
    name                 VARCHAR(150) NOT NULL,
    slug                 VARCHAR(180) NOT NULL UNIQUE,
    industry_id          INT UNSIGNED NULL,
    website              VARCHAR(255) NULL,
    logo_file_id         BIGINT UNSIGNED NULL,
    size_range           ENUM('1-10','11-50','51-200','201-500','501-1000','1000+') NULL,
    description          TEXT NULL,
    headquarters_city    VARCHAR(100) NULL,
    headquarters_state   VARCHAR(100) NULL,
    headquarters_country VARCHAR(100) NULL DEFAULT 'United States',
    status               ENUM('lead','active','inactive') NOT NULL DEFAULT 'lead',
    owner_admin_id       BIGINT UNSIGNED NULL,
    created_at           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at           TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_companies_industry FOREIGN KEY (industry_id) REFERENCES industries(id) ON DELETE SET NULL,
    CONSTRAINT fk_companies_logo FOREIGN KEY (logo_file_id) REFERENCES files(id) ON DELETE SET NULL,
    CONSTRAINT fk_companies_owner FOREIGN KEY (owner_admin_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_companies_status (status)
) ENGINE=InnoDB;

CREATE TABLE company_contacts (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id    BIGINT UNSIGNED NOT NULL,
    user_id       BIGINT UNSIGNED NULL,
    full_name     VARCHAR(150) NOT NULL,
    email         VARCHAR(190) NOT NULL,
    phone         VARCHAR(30) NULL,
    job_title     VARCHAR(150) NULL,
    is_primary    TINYINT(1) NOT NULL DEFAULT 0,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_company_contacts_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_company_contacts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_company_contacts_company (company_id)
) ENGINE=InnoDB;

CREATE TABLE employer_activities (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id      BIGINT UNSIGNED NOT NULL,
    staffing_request_id BIGINT UNSIGNED NULL,
    created_by      BIGINT UNSIGNED NULL,
    activity_type   ENUM('note','call','email','meeting','status_change') NOT NULL DEFAULT 'note',
    subject         VARCHAR(200) NULL,
    body            TEXT NULL,
    occurred_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_employer_activities_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_employer_activities_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_employer_activities_company (company_id, occurred_at)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Jobs & Applications
-- ---------------------------------------------------------------------

CREATE TABLE jobs (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid                CHAR(36) NOT NULL UNIQUE,
    company_id          BIGINT UNSIGNED NOT NULL,
    industry_id         INT UNSIGNED NULL,
    service_id          INT UNSIGNED NULL,
    title               VARCHAR(180) NOT NULL,
    slug                VARCHAR(220) NOT NULL UNIQUE,
    employment_type     ENUM('full_time','part_time','contract','temp','temp_to_hire') NOT NULL DEFAULT 'full_time',
    location_city       VARCHAR(100) NULL,
    location_state      VARCHAR(100) NULL,
    location_country    VARCHAR(100) NULL DEFAULT 'United States',
    is_remote           TINYINT(1) NOT NULL DEFAULT 0,
    salary_min          INT UNSIGNED NULL,
    salary_max          INT UNSIGNED NULL,
    salary_currency     CHAR(3) NOT NULL DEFAULT 'USD',
    salary_period        ENUM('hour','year') NOT NULL DEFAULT 'year',
    description         TEXT NOT NULL,
    requirements        TEXT NULL,
    benefits            TEXT NULL,
    positions_available INT UNSIGNED NOT NULL DEFAULT 1,
    status              ENUM('draft','open','on_hold','filled','closed') NOT NULL DEFAULT 'draft',
    created_by          BIGINT UNSIGNED NULL,
    published_at        TIMESTAMP NULL DEFAULT NULL,
    closes_at           TIMESTAMP NULL DEFAULT NULL,
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at          TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_jobs_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_jobs_industry FOREIGN KEY (industry_id) REFERENCES industries(id) ON DELETE SET NULL,
    CONSTRAINT fk_jobs_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL,
    CONSTRAINT fk_jobs_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_jobs_status (status),
    INDEX idx_jobs_company (company_id),
    INDEX idx_jobs_location (location_city, location_state),
    FULLTEXT INDEX ftx_jobs_search (title, description, requirements)
) ENGINE=InnoDB;

CREATE TABLE job_applications (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid          CHAR(36) NOT NULL UNIQUE,
    job_id        BIGINT UNSIGNED NOT NULL,
    candidate_id  BIGINT UNSIGNED NOT NULL,
    cover_note    TEXT NULL,
    status        ENUM('applied','screening','interview','offer','hired','rejected','withdrawn') NOT NULL DEFAULT 'applied',
    applied_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_job_applications_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    CONSTRAINT fk_job_applications_candidate FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
    UNIQUE KEY uq_job_candidate (job_id, candidate_id),
    INDEX idx_job_applications_status (status)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Staffing Requests (Employer intake, before/independent of a job post)
-- ---------------------------------------------------------------------

CREATE TABLE staffing_requests (
    id                     BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid                   CHAR(36) NOT NULL UNIQUE,
    company_id             BIGINT UNSIGNED NULL,
    contact_name           VARCHAR(150) NOT NULL,
    contact_email          VARCHAR(190) NOT NULL,
    contact_phone          VARCHAR(30) NULL,
    role_title             VARCHAR(180) NOT NULL,
    industry_id            INT UNSIGNED NULL,
    service_id             INT UNSIGNED NULL,
    employment_type        ENUM('full_time','part_time','contract','temp','temp_to_hire') NOT NULL DEFAULT 'full_time',
    positions_needed        INT UNSIGNED NOT NULL DEFAULT 1,
    budget_min             INT UNSIGNED NULL,
    budget_max             INT UNSIGNED NULL,
    budget_currency        CHAR(3) NOT NULL DEFAULT 'USD',
    must_have_skills       TEXT NULL,
    nice_to_have_skills    TEXT NULL,
    location_city          VARCHAR(100) NULL,
    location_state         VARCHAR(100) NULL,
    is_remote_ok           TINYINT(1) NOT NULL DEFAULT 0,
    start_date_needed      DATE NULL,
    additional_notes       TEXT NULL,
    status                 ENUM('new','contacted','qualified','in_progress','matched','closed_won','closed_lost') NOT NULL DEFAULT 'new',
    assigned_admin_id      BIGINT UNSIGNED NULL,
    created_at             TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at             TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at             TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_staffing_requests_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL,
    CONSTRAINT fk_staffing_requests_industry FOREIGN KEY (industry_id) REFERENCES industries(id) ON DELETE SET NULL,
    CONSTRAINT fk_staffing_requests_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL,
    CONSTRAINT fk_staffing_requests_admin FOREIGN KEY (assigned_admin_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_staffing_requests_status (status)
) ENGINE=InnoDB;

ALTER TABLE employer_activities
    ADD CONSTRAINT fk_employer_activities_request FOREIGN KEY (staffing_request_id) REFERENCES staffing_requests(id) ON DELETE SET NULL;

-- ---------------------------------------------------------------------
-- Matching
-- ---------------------------------------------------------------------

CREATE TABLE matches (
    id                   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid                 CHAR(36) NOT NULL UNIQUE,
    staffing_request_id BIGINT UNSIGNED NULL,
    job_id               BIGINT UNSIGNED NULL,
    candidate_id         BIGINT UNSIGNED NOT NULL,
    match_type           ENUM('strong_match','needs_review','rejected') NOT NULL DEFAULT 'needs_review',
    score                DECIMAL(5,2) NOT NULL DEFAULT 0,
    status               ENUM('proposed','presented_to_employer','interviewing','hired','rejected') NOT NULL DEFAULT 'proposed',
    matched_by           BIGINT UNSIGNED NULL,
    notes                TEXT NULL,
    created_at           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_matches_request FOREIGN KEY (staffing_request_id) REFERENCES staffing_requests(id) ON DELETE CASCADE,
    CONSTRAINT fk_matches_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    CONSTRAINT fk_matches_candidate FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
    CONSTRAINT fk_matches_admin FOREIGN KEY (matched_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_matches_candidate (candidate_id),
    INDEX idx_matches_request (staffing_request_id),
    INDEX idx_matches_job (job_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Notifications & Audit
-- ---------------------------------------------------------------------

CREATE TABLE notifications (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid                CHAR(36) NOT NULL UNIQUE,
    user_id             BIGINT UNSIGNED NULL,
    recipient_email     VARCHAR(190) NULL,
    channel             ENUM('email','in_app') NOT NULL DEFAULT 'email',
    type                VARCHAR(80) NOT NULL,
    subject             VARCHAR(200) NULL,
    body                TEXT NULL,
    status              ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
    related_entity_type VARCHAR(50) NULL,
    related_entity_id   BIGINT UNSIGNED NULL,
    sent_at             TIMESTAMP NULL DEFAULT NULL,
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notifications_status (status),
    INDEX idx_notifications_user (user_id)
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      BIGINT UNSIGNED NULL,
    action       VARCHAR(100) NOT NULL,
    entity_type  VARCHAR(50) NULL,
    entity_id    BIGINT UNSIGNED NULL,
    ip_address   VARCHAR(45) NULL,
    user_agent   VARCHAR(255) NULL,
    meta         JSON NULL,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_audit_logs_entity (entity_type, entity_id),
    INDEX idx_audit_logs_user (user_id)
) ENGINE=InnoDB;

CREATE TABLE app_settings (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    value       TEXT NULL,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
