# GreenThumb Database Schema

## Tables

### 1. users
Existing Laravel table for authentication and user management.

| Column | Type | Constraints |
|--------|------|-------------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT |
| name | VARCHAR(255) | NOT NULL |
| email | VARCHAR(255) | NOT NULL, UNIQUE |
| email_verified_at | TIMESTAMP | NULL |
| password | VARCHAR(255) | NOT NULL |
| two_factor_secret | TEXT | NULL |
| two_factor_recovery_codes | TEXT | NULL |
| two_factor_confirmed_at | TIMESTAMP | NULL |
| remember_token | VARCHAR(100) | NULL |
| created_at | TIMESTAMP | NULL |
| updated_at | TIMESTAMP | NULL |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (email)

**Collation:** utf8mb4_unicode_ci

---

### 2. journal_entries
Stores user gardening journal entries.

| Column | Type | Constraints |
|--------|------|-------------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT |
| user_id | BIGINT UNSIGNED | NOT NULL, FOREIGN KEY → users.id |
| title | VARCHAR(255) | NOT NULL |
| content | TEXT | NOT NULL |
| entry_date | DATE | NOT NULL |
| created_at | TIMESTAMP | NULL |
| updated_at | TIMESTAMP | NULL |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (user_id)
- COMPOSITE INDEX (user_id, entry_date)

**Foreign Keys:**
- user_id REFERENCES users(id) ON DELETE CASCADE

**Collation:** utf8mb4_unicode_ci (title, content)

---

### 3. entries_count
Tracks entry count per user for 50-entry limit enforcement.

| Column | Type | Constraints |
|--------|------|-------------|
| user_id | BIGINT UNSIGNED | PRIMARY KEY, FOREIGN KEY → users.id |
| count | INT | NOT NULL, DEFAULT 0 |

**Indexes:**
- PRIMARY KEY (user_id)

**Foreign Keys:**
- user_id REFERENCES users(id) ON DELETE CASCADE

---

### 4. search_analytics
Tracks AI search queries for success metrics.

| Column | Type | Constraints |
|--------|------|-------------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT |
| user_id | BIGINT UNSIGNED | NOT NULL, FOREIGN KEY → users.id |
| results_count | INT | NOT NULL |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (user_id)
- COMPOSITE INDEX (user_id, created_at)

**Foreign Keys:**
- user_id REFERENCES users(id) ON DELETE CASCADE

**Collation:** utf8mb4_unicode_ci (query_text)

---

## Relationships

- **users → journal_entries**: One-to-Many (CASCADE on delete)
- **users → search_analytics**: One-to-Many (CASCADE on delete)
- **users → entries_count**: One-to-One (CASCADE on delete)

---

## Indexes

### Composite Indexes
- **journal_entries (user_id, entry_date)**: Optimizes weekly calendar queries filtering by user and date range
- **search_analytics (user_id, created_at)**: Optimizes metrics queries for AI search usage patterns

### Single Column Indexes
- **journal_entries (user_id)**: Foreign key index for joins and user data isolation
- **search_analytics (user_id)**: Foreign key index for joins and user data isolation
- **search_analytics (created_at)**: Supports time-based analytics queries

### Index Types
All indexes use BTREE (default) for efficient range queries and equality lookups.

---

## Database Configuration

**Character Set:** utf8mb4
**Collation:** utf8mb4_unicode_ci (all text fields)
**Engine:** InnoDB (supports foreign keys, transactions, and row-level locking)
**Timezone:** Store all timestamps in UTC, convert to user timezone at application layer

---

## Security & Data Isolation

### Application-Layer Authorization
- All queries must include `WHERE user_id = auth()->id()` via Laravel global scopes
- Laravel policies enforce user data isolation for journal_entries and search_analytics
- No row-level security policies at database level (handled by application layer)

### Foreign Key Cascades
- **ON DELETE CASCADE** for all foreign keys ensures complete user data removal for GDPR compliance
- Deleting a user automatically removes all associated journal_entries, search_analytics, and entries_count records
---

## Design Decisions

1. **Hard Deletes**: No soft deletes implemented per PRD scope (Section 4.2: entry versioning excluded from MVP)

2. **No AI Response Storage**: AI search results displayed transiently only; not persisted to reduce storage costs and complexity

3. **Entry Limit Enforcement**: Dedicated entries_count table updated via Laravel observers on journal entry create/delete

4. **Date-Only Storage**: entry_date uses DATE type (no time component) per requirement for past/present date restrictions

5. **Character Limits**:
   - title: VARCHAR(255) for single-line headings
   - content: TEXT for detailed gardening observations (supports ~65KB)
   - Application-layer validation recommended for UX (e.g., 10,000 character soft limit with UI indicator)

6. **No Premature Optimization**: No table partitioning, full-text indexes, or stored procedures for MVP. With 50-entry limit and expected small initial user base, focus on simplicity.

7. **Analytics Tracking**: search_analytics includes response_time_ms for API performance monitoring per Section 6 success metrics

---

## Migration Strategy

1. Create journal_entries migration with composite index and foreign key constraint
3. Create entries_count migration with foreign key constraint
4. Create search_analytics migration with composite index and foreign key constraint
5. No seed data required for MVP (users create own entries)
