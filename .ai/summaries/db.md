# Database Planning Summary - GreenThumb MVP

## Decisions

1. Create a `journal_entries` table with fields: id, user_id, title, content, entry_date, created_at, updated_at
2. Create an `entries_count` table with fields: user_id and count to track the 50-entry limit per user
3. Create a `search_analytics` table to track AI search usage for primary success metrics
4. Use hard deletes for journal entries (no soft deletes)
7. Implement composite index on (user_id, entry_date) in journal_entries table for weekly calendar queries
8. Set entry_date as NOT NULL (required field)
9. Do not store AI responses in the database (display transiently only)
10. Use VARCHAR(255) for title field and TEXT for content field in journal_entries
11. Implement application-layer authorization (Laravel policies/gates) instead of database-level row security
12. Use utf8mb4_unicode_ci collation for all text fields
13. No database triggers or stored procedures needed
14. No separate email verification tokens table needed (Laravel handles this)
15. No created_by/updated_by audit fields needed in journal_entries
16. Implement foreign key constraints with CASCADE delete: journal_entries.user_id → users.id and search_analytics.user_id → users.id
17. No table partitioning needed for MVP
18. No full-text search indexes needed (AI handles search via OpenRouter.ai)
19. No database-level validation for email/password (handle at application layer)
20. Use Laravel's default sessions table for session management

## Matched Recommendations

1. **journal_entries Table Schema**: Create with id (primary key), user_id (foreign key), title (VARCHAR(255), required), content (TEXT, required), entry_date (DATE, NOT NULL), created_at, updated_at. Add index on user_id and entry_date for calendar queries.

2. **50-Entry Limit Enforcement**: Implement at application layer via Laravel validation/observer using dedicated entries_count table tracking user_id and count.

3. **search_analytics Table**: Essential for measuring primary success metric ("80% of users perform 3+ AI searches"). Schema: id, user_id (foreign key, indexed), query_text (text), results_count (integer), response_time_ms (integer), created_at. Index on (user_id, created_at) for metrics queries.

4. **Composite Index Strategy**: Composite index on (user_id, entry_date) in journal_entries table optimizes weekly calendar view queries that filter by user_id and date range. Use BTREE index type for range queries.

5. **No AI Response Storage**: Don't store AI responses in MVP. Results displayed transiently in modal per US-14/US-15. Only track analytics (query, count, timestamp) to reduce storage costs and complexity.

6. **Character Limits**: title VARCHAR(255) for single-line text, content TEXT for detailed gardening observations. Add application-layer validation for UX (e.g., 10,000 char soft limit with UI indicator).

7. **Application-Layer Authorization**: Use Laravel policies/gates with global scopes to enforce user data isolation. All queries should include `where('user_id', auth()->id())`. Document this pattern in repository classes.

8. **UTF-8 Collation**: utf8mb4_unicode_ci for all text fields supports international characters (plant names, scientific terms) and case-insensitive searching.

9. **Foreign Key Cascade Behavior**:
   - journal_entries.user_id → users.id ON DELETE CASCADE
   - search_analytics.user_id → users.id ON DELETE CASCADE
   Both align with "user data isolation" requirement and GDPR-style data deletion.

10. **No Premature Optimization**: No partitioning, full-text indexes, or stored procedures for MVP. With 50-entry limit and expected small initial user base, focus on simplicity and maintainability.

11. **Session Management**: Use Laravel's default sessions table (database-backed) with fields: id, user_id (nullable, indexed), ip_address, user_agent, payload, last_activity (indexed). Supports session expiration and "active usage extends timeout" behavior.

12. **Hard Deletes**: No soft deletes as PRD explicitly scopes out "entry versioning or history tracking" (Section 4.2). Use hard deletes for MVP simplicity per US-012.

## Database Planning Summary

### Main Requirements

The GreenThumb MVP requires a MySQL database schema supporting:
- Journal entry management with 50-entry limit per user
- Weekly calendar-based navigation and filtering
- AI-powered natural language search analytics tracking
- User data isolation and privacy compliance

### Key Entities and Relationships

**1. users** (existing Laravel table)
- Primary table for authentication
- Fields: id, name, email (unique), email_verified_at, password, two_factor_secret, two_factor_recovery_codes, two_factor_confirmed_at , remember_token, created_at, updated_at
- Relationships: hasMany journal_entries, hasMany search_analytics, hasOne entries_count

**2. journal_entries** (new)
- Stores user gardening journal entries
- Fields:
  - id (primary key, auto-increment)
  - user_id (foreign key → users.id, indexed, ON DELETE CASCADE)
  - title (VARCHAR(255), NOT NULL)
  - content (TEXT, NOT NULL)
  - entry_date (DATE, NOT NULL) - no time component, past/present only
  - created_at (timestamp)
  - updated_at (timestamp)
- Indexes:
  - Composite: (user_id, entry_date) - optimizes weekly calendar queries
  - Foreign key: user_id
- Constraints: NOT NULL on title, content, entry_date
- Collation: utf8mb4_unicode_ci for title and content

**3. entries_count** (new)
- Tracks entry count per user for 50-entry limit enforcement
- Fields:
  - user_id (primary key, foreign key → users.id, ON DELETE CASCADE)
  - count (INTEGER, NOT NULL, default 0)
- Updated via Laravel observers/events on journal entry create/delete

**4. search_analytics** (new)
- Tracks AI search queries for success metrics
- Fields:
  - id (primary key, auto-increment)
  - user_id (foreign key → users.id, indexed, ON DELETE CASCADE)
  - query_text (TEXT, NOT NULL)
  - results_count (INTEGER, NOT NULL)
  - response_time_ms (INTEGER) - for performance monitoring
  - created_at (timestamp, indexed)
- Indexes:
  - Composite: (user_id, created_at) - optimizes metrics queries
  - Foreign key: user_id
- Collation: utf8mb4_unicode_ci for query_text

### Entity Relationships

```
users (1) ──→ (many) journal_entries [ON DELETE CASCADE]
users (1) ──→ (many) search_analytics [ON DELETE CASCADE]
users (1) ──→ (1) entries_count [ON DELETE CASCADE]
```

### Important Security and Scalability Concerns

**Security:**
1. **User Data Isolation**: Enforced at application layer using Laravel policies and global scopes. All journal_entries and search_analytics queries must filter by authenticated user_id.
2. **No AI Training Data**: AI responses not stored in database. Search analytics stored server-side only, never transmitted for model training (per US-021).
3. **Authentication**: Laravel Fortify handles 2FA, email verification, password hashing (bcrypt), and session management.
4. **Foreign Key Cascades**: ON DELETE CASCADE ensures complete user data removal for GDPR compliance.

**Scalability:**
1. **Entry Limit**: 50-entry maximum per user prevents unbounded growth in MVP phase. Tracked via entries_count table.
2. **Indexing Strategy**: Composite indexes on high-query columns (user_id, entry_date) and (user_id, created_at) optimize weekly calendar and analytics queries.
3. **No Premature Optimization**: No partitioning, full-text indexes, or stored procedures until user base exceeds 100K or analytics exceeds 10M rows.
4. **AI Processing**: Server-side only via OpenRouter.ai API, no database load for NLP processing.
5. **Character Limits**: VARCHAR(255) for titles, TEXT for content balances flexibility with reasonable storage expectations.

**Performance Considerations:**
1. Database-backed sessions support session expiration queries and activity-based timeout extension.
2. search_analytics table includes response_time_ms for API performance monitoring.
3. BTREE indexes on date ranges support efficient weekly calendar navigation.

### Implementation Notes

**Application-Layer Logic:**
- Entry limit validation: Laravel observer on JournalEntry model updates entries_count
- Date restrictions (past/present only): Laravel validation rules, client-side date picker constraints
- User authorization: Laravel policies with `where('user_id', auth()->id())` global scope
- AI search: Server-side controller method calling OpenRouter.ai API, results returned transiently
- Session timeout: Laravel session configuration with database driver

**Database Configuration:**
- Character set: utf8mb4
- Collation: utf8mb4_unicode_ci (all text fields)
- Engine: InnoDB (default for Laravel, supports foreign keys and transactions)
- Time zone handling: Store timestamps in UTC, convert to user timezone at application layer

**Migration Strategy:**
1. Create journal_entries migration with composite index and foreign key
3. Create entries_count migration with foreign key
4. Create search_analytics migration with composite index and foreign key
5. No seed data required for MVP (users create own entries)

## Unresolved Issues

None. All database planning decisions have been finalized and documented. The schema is ready for migration implementation.
