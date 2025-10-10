# REST API Plan for GreenThumb

## 1. Resources

| Resource | Database Table | Description |
|----------|---------------|-------------|
| Journal Entries | journal_entries | User gardening journal entries with title, content, and date |
| Search Analytics | search_analytics | AI search query tracking for metrics |
| Entry Count | entries_count | Per-user entry count for 50-entry limit enforcement |

---

## 2. Endpoints

### 2.1 Journal Entry Endpoints

#### List Journal Entries
- **HTTP Method**: GET
- **URL Path**: `/api/journal-entries`
- **Description**: Retrieve paginated list of user's journal entries
- **Authentication**: Required (Bearer token)
- **Query Parameters**:
    - `page`: Page number (integer, default: 1)
    - `per_page`: Items per page (integer, default: 15, max: 50)
    - `sort`: Sort field (string, values: `entry_date`, `created_at`, default: `entry_date`)
    - `order`: Sort order (string, values: `asc`, `desc`, default: `desc`)
    - `date_from`: Filter entries from date (date, format: YYYY-MM-DD)
    - `date_to`: Filter entries to date (date, format: YYYY-MM-DD)
- **Success Response** (200 OK):
```json
{
  "data": [
    {
      "id": "integer",
      "user_id": "integer",
      "title": "string",
      "content": "string",
      "entry_date": "date (YYYY-MM-DD)",
      "created_at": "ISO 8601 timestamp",
      "updated_at": "ISO 8601 timestamp"
    }
  ],
  "meta": {
    "current_page": "integer",
    "per_page": "integer",
    "total": "integer",
    "last_page": "integer"
  },
  "links": {
    "first": "string (URL)",
    "last": "string (URL)",
    "prev": "string (URL) or null",
    "next": "string (URL) or null"
  }
}
```

#### Get Journal Entry by ID
- **HTTP Method**: GET
- **URL Path**: `/api/journal-entries/{id}`
- **Description**: Retrieve a specific journal entry
- **Authentication**: Required (Bearer token)
- **URL Parameters**:
    - `id`: Entry ID (integer, required)
- **Success Response** (200 OK):
```json
{
  "data": {
    "id": "integer",
    "user_id": "integer",
    "title": "string",
    "content": "string",
    "entry_date": "date (YYYY-MM-DD)",
    "created_at": "ISO 8601 timestamp",
    "updated_at": "ISO 8601 timestamp"
  }
}
```
- **Error Responses**:
    - 403 Forbidden:
  ```json
  {
    "message": "You are not authorized to access this entry."
  }
  ```
    - 404 Not Found:
  ```json
  {
    "message": "Journal entry not found."
  }
  ```

#### Get Entries by Date Range (Weekly Calendar)
- **HTTP Method**: GET
- **URL Path**: `/api/journal-entries/calendar/week`
- **Description**: Retrieve entries for a specific week (Monday-Sunday)
- **Authentication**: Required (Bearer token)
- **Query Parameters**:
    - `start_date`: Week start date (date, format: YYYY-MM-DD, required, must be Monday)
- **Success Response** (200 OK):
```json
{
  "week": {
    "start_date": "date (YYYY-MM-DD)",
    "end_date": "date (YYYY-MM-DD)",
    "week_label": "string (e.g., 'October 7-13, 2025')"
  },
  "data": [
    {
      "id": "integer",
      "user_id": "integer",
      "title": "string",
      "content": "string",
      "entry_date": "date (YYYY-MM-DD)",
      "created_at": "ISO 8601 timestamp",
      "updated_at": "ISO 8601 timestamp"
    }
  ],
  "entries_by_date": {
    "2025-10-07": [
      {
        "id": "integer",
        "title": "string",
        "content": "string (truncated to 100 chars)",
        "entry_date": "date"
      }
    ],
    "2025-10-08": [],
    "...": []
  }
}
```
- **Error Responses**:
    - 422 Unprocessable Entity:
  ```json
  {
    "message": "The given data was invalid.",
    "errors": {
      "start_date": ["The start date must be a Monday."]
    }
  }
  ```

#### Create Journal Entry
- **HTTP Method**: POST
- **URL Path**: `/api/journal-entries`
- **Description**: Create a new journal entry
- **Authentication**: Required (Bearer token)
- **Request Payload**:
```json
{
  "title": "string (required, max: 255)",
  "content": "string (required)",
  "entry_date": "date (required, format: YYYY-MM-DD, must be today or past)"
}
```
- **Success Response** (201 Created):
```json
{
  "message": "Journal entry created successfully.",
  "data": {
    "id": "integer",
    "user_id": "integer",
    "title": "string",
    "content": "string",
    "entry_date": "date (YYYY-MM-DD)",
    "created_at": "ISO 8601 timestamp",
    "updated_at": "ISO 8601 timestamp"
  }
}
```
- **Error Responses**:
    - 403 Forbidden:
  ```json
  {
    "message": "You have reached the maximum limit of 50 entries."
  }
  ```
    - 422 Unprocessable Entity:
  ```json
  {
    "message": "The given data was invalid.",
    "errors": {
      "entry_date": ["The entry date must be today or a past date."],
      "title": ["The title field is required."],
      "content": ["The content field is required."]
    }
  }
  ```

#### Update Journal Entry
- **HTTP Method**: PUT/PATCH
- **URL Path**: `/api/journal-entries/{id}`
- **Description**: Update an existing journal entry
- **Authentication**: Required (Bearer token)
- **URL Parameters**:
    - `id`: Entry ID (integer, required)
- **Request Payload**:
```json
{
  "title": "string (optional, max: 255)",
  "content": "string (optional)",
  "entry_date": "date (optional, format: YYYY-MM-DD, must be today or past)"
}
```
- **Success Response** (200 OK):
```json
{
  "message": "Journal entry updated successfully.",
  "data": {
    "id": "integer",
    "user_id": "integer",
    "title": "string",
    "content": "string",
    "entry_date": "date (YYYY-MM-DD)",
    "created_at": "ISO 8601 timestamp",
    "updated_at": "ISO 8601 timestamp"
  }
}
```
- **Error Responses**:
    - 403 Forbidden:
  ```json
  {
    "message": "You are not authorized to update this entry."
  }
  ```
    - 404 Not Found:
  ```json
  {
    "message": "Journal entry not found."
  }
  ```
    - 422 Unprocessable Entity:
  ```json
  {
    "message": "The given data was invalid.",
    "errors": {
      "entry_date": ["The entry date must be today or a past date."]
    }
  }
  ```

#### Delete Journal Entry
- **HTTP Method**: DELETE
- **URL Path**: `/api/journal-entries/{id}`
- **Description**: Delete a journal entry (hard delete)
- **Authentication**: Required (Bearer token)
- **URL Parameters**:
    - `id`: Entry ID (integer, required)
- **Success Response** (200 OK):
```json
{
  "message": "Journal entry deleted successfully."
}
```
- **Error Responses**:
    - 403 Forbidden:
  ```json
  {
    "message": "You are not authorized to delete this entry."
  }
  ```
    - 404 Not Found:
  ```json
  {
    "message": "Journal entry not found."
  }
  ```

#### Get User Entry Count
- **HTTP Method**: GET
- **URL Path**: `/api/journal-entries/count`
- **Description**: Retrieve current entry count and limit status
- **Authentication**: Required (Bearer token)
- **Success Response** (200 OK):
```json
{
  "count": "integer",
  "limit": 50,
  "remaining": "integer",
  "is_at_limit": "boolean"
}
```

---

### 2.3 AI Search Endpoints

#### Perform AI Search
- **HTTP Method**: POST
- **URL Path**: `/api/search/ai`
- **Description**: Search journal entries using natural language query via OpenRouter.ai
- **Authentication**: Required (Bearer token)
- **Request Payload**:
```json
{
  "query": "string (required, min: 3, max: 500)"
}
```
- **Success Response** (200 OK):
```json
{
  "query": "string (original user query)",
  "ai_summary": "string (AI-generated summary/answer)",
  "results_count": "integer",
  "entries": [
    {
      "id": "integer",
      "title": "string",
      "content": "string",
      "entry_date": "date (YYYY-MM-DD)",
      "relevance_score": "float (0-1, if available)"
    }
  ],
  "response_time_ms": "integer"
}
```
- **Error Responses**:
    - 422 Unprocessable Entity:
  ```json
  {
    "message": "The given data was invalid.",
    "errors": {
      "query": ["The query must be at least 3 characters."]
    }
  }
  ```
    - 500 Internal Server Error:
  ```json
  {
    "message": "AI search is temporarily unavailable. Please try again.",
    "error_code": "AI_SERVICE_ERROR"
  }
  ```
    - 503 Service Unavailable:
  ```json
  {
    "message": "AI search service timeout. Please try again.",
    "error_code": "AI_SERVICE_TIMEOUT"
  }
  ```

---

### 2.4 Analytics Endpoints

#### Get Search Analytics Summary
- **HTTP Method**: GET
- **URL Path**: `/api/analytics/search`
- **Description**: Retrieve aggregated search analytics for the authenticated user
- **Authentication**: Required (Bearer token, admin only for future analytics dashboard)
- **Query Parameters**:
    - `date_from`: Start date (date, format: YYYY-MM-DD)
    - `date_to`: End date (date, format: YYYY-MM-DD)
- **Success Response** (200 OK):
```json
{
  "period": {
    "from": "date (YYYY-MM-DD)",
    "to": "date (YYYY-MM-DD)"
  },
  "total_searches": "integer",
  "average_results_per_search": "float",
  "searches_with_results": "integer",
  "searches_without_results": "integer"
}
```

---

## 3. Authentication and Authorization

### Authentication Mechanism

**Token-Based Authentication (Laravel Sanctum)**

Laravel Sanctum provides a lightweight authentication system for SPAs and mobile applications using API tokens.

#### Implementation Details:

1. **Token Generation**:
    - Upon successful login, generate a personal access token using `$user->createToken('access_token')`
    - Return token in response as Bearer token
    - Token stored in `personal_access_tokens` table

2. **Token Usage**:
    - Client includes token in Authorization header: `Authorization: Bearer {token}`
    - Laravel middleware `auth:sanctum` validates token on protected routes
    - Automatic user resolution via `auth()->user()`

3. **Token Expiration**:
    - Configure token expiration in `sanctum.php` config (recommended: 24 hours)
    - Implement token refresh mechanism if needed for extended sessions
    - Expired tokens return 401 Unauthorized

4. **Token Revocation**:
    - Logout endpoint revokes current token using `$request->user()->currentAccessToken()->delete()`
    - Support revoking all user tokens: `$user->tokens()->delete()`

    
## 4. Validation and Business Logic

### 4.1 Validation Rules by Resource

#### Journal Entry Creation
- **title**: required, string, max:255
- **content**: required, string
- **entry_date**: required, date, date_format:Y-m-d, before_or_equal:today

#### Journal Entry Update
- **title**: sometimes, string, max:255
- **content**: sometimes, string
- **entry_date**: sometimes, date, date_format:Y-m-d, before_or_equal:today

#### AI Search Query
- **query**: required, string, min:3, max:500

---

### 4.2 Business Logic Implementation

#### 1. 50-Entry Limit Enforcement

**Location**: Journal entry creation endpoint

**Logic**:
1. Check `entries_count` table for user's current count
2. If count >= 50, return 403 Forbidden error
3. If count < 50, proceed with entry creation
4. Increment count in `entries_count` table via observer/event listener

**Observer Implementation**:
- `JournalEntryObserver` with `created` and `deleted` events
- On create: increment user's entry count
- On delete: decrement user's entry count
- Use database transactions to ensure atomicity

**Endpoint**: `POST /api/journal-entries`

---

#### 2. Past/Present Date Restriction

**Location**: Journal entry creation and update endpoints

**Logic**:
1. Validate `entry_date` is not in the future
2. Custom validation rule: `before_or_equal:today`
3. Client-side validation prevents future date selection
4. Server-side validation enforces constraint

**Implementation**:
- Laravel validation rule: `'entry_date' => 'required|date|before_or_equal:today'`
- Return 422 error with clear message if validation fails

**Endpoints**:
- `POST /api/journal-entries`
- `PUT/PATCH /api/journal-entries/{id}`

---

#### 3. User Data Isolation

**Location**: All journal entry and search endpoints

**Logic**:
1. Apply global scope to `JournalEntry` model filtering by `auth()->id()`
2. Laravel policies verify ownership before update/delete operations
3. Never expose other users' data in queries

**Endpoints**: All journal entry and search endpoints

---

#### 4. AI Search Processing

**Location**: AI search endpoint

**Logic**:
1. Receive natural language query from user
2. Retrieve all user's journal entries (max 50)
3. Parse AI response for summary and relevant entry IDs
4. Return AI summary and matched entries to user (for now use mock data)
5. Log search analytics (query text, results count, response time)
6. Handle API errors gracefully with retry logic

**Implementation**:
- Service class: `AISearchService`
- Queue failed searches for retry (max 2 retries)
- Timeout: 30 seconds for AI API call
- Cache recent searches (5 minutes) to reduce API calls
- For MVP, mock AI response with static data

**Data Privacy**:
- All AI processing occurs server-side
- User data never sent for model training
- API configuration includes training opt-out flags
- Secure API key storage in environment variables

**Endpoint**: `POST /api/search/ai`

---

#### 5. Search Analytics Tracking

**Location**: AI search endpoint

**Logic**:
1. After successful AI search, log analytics data
2. Store: user_id, results_count, created_at (timestamp auto-generated)
3. Track for success metrics (80% of users performing 3+ searches)

**Implementation**:
- Create `SearchAnalytic` record after each search
- Event listener on successful search completion
- Analytics aggregation for admin dashboard (future feature)

**Endpoint**: `POST /api/search/ai`

---

#### 6. Pagination and Performance

**Location**: List endpoints (journal entries)

**Logic**:
1. Default pagination: 15 items per page
2. Max pagination: 50 items per page
3. Use composite indexes for optimized queries
4. Eager load relationships to prevent N+1 queries

**Implementation**:
- Laravel pagination with `paginate()` method
- Composite index on (user_id, entry_date) for calendar queries
- Response includes pagination metadata and links

**Endpoints**:
- `GET /api/journal-entries`
- `GET /api/journal-entries/calendar/week`

---

#### 7. Weekly Calendar Logic

**Location**: Calendar endpoint

**Logic**:
1. Accept `start_date` parameter (must be Monday)
2. Calculate end date (Sunday, 6 days after start)
3. Query entries between start and end dates
4. Group entries by date for calendar display
5. Return entries organized by day of week

**Implementation**:
- Validation: ensure `start_date` is Monday
- Use composite index (user_id, entry_date) for performance
- Return structured response with entries grouped by date
- Include empty arrays for dates without entries

**Endpoint**: `GET /api/journal-entries/calendar/week`

---

#### 8. Error Handling Standards

**Global error handling strategy**:

1. **Validation Errors (422)**:
    - Return structured error messages with field-specific errors
    - Use Laravel FormRequest validation

2. **Authentication Errors (401)**:
    - Invalid or expired token
    - Missing authentication header

3. **Authorization Errors (403)**:
    - User not authorized to access resource
    - Entry limit reached
    - Email not verified

4. **Not Found Errors (404)**:
    - Resource does not exist
    - User does not own resource (security)

5. **Server Errors (500)**:
    - Log detailed error information
    - Return generic message to user
    - Alert development team for critical failures

6. **Service Unavailable (503)**:
    - AI service timeout or unavailable
    - Database connection issues

**Implementation**:
- Custom exception handler in Laravel
- Structured JSON error responses
- Logging via Laravel Pail for monitoring
- User-friendly error messages (no stack traces in production)

