# API Endpoint Implementation Plan: Get Entries by Date Range (Weekly Calendar)

## 1. Endpoint Overview
This endpoint retrieves journal entries for a specific date range to populate the weekly calendar view. 
It returns all entries belonging to the authenticated user within the specified date range, ordered by entry date and creation time. The endpoint is optimized for the calendar interface's week-view display.

## 2. Request Details
- **HTTP Method**: GET
- **URL Structure**: `/api/journal-entries/date-range`
- **Authentication**: Required (Laravel Sanctum token or session-based)
- **Parameters**:
    - **Required**:
        - `start_date` (query parameter): Start date of the range in YYYY-MM-DD format (must be a Monday for weekly calendar)
        - `end_date` (query parameter): End date of the range in YYYY-MM-DD format (must be a Sunday for weekly calendar)
    - **Optional**: None for MVP
- **Request Body**: N/A (GET request)
- **Headers**:
    - `Accept: application/json`
    - `Authorization: Bearer {token}` (if using Sanctum API tokens)

**Example Request**:
```
GET /api/journal-entries/date-range?start_date=2025-10-06&end_date=2025-10-12
```

## 3. Used Types

### Primary Model: JournalEntry
- **Table**: journal_entries
- **Relationships**:
    - belongsTo User (user_id foreign key)
- **Key Fields**:
    - id (BIGINT UNSIGNED, primary key)
    - user_id (BIGINT UNSIGNED, foreign key)
    - title (VARCHAR 255)
    - content (TEXT)
    - entry_date (DATE)
    - created_at (TIMESTAMP)
    - updated_at (TIMESTAMP)

### Secondary Model: User
- **Table**: users
- **Relationship**: hasMany JournalEntry

## 4. Response Details

### Success Response (200 OK)
```json
{
  "data": [
    {
      "id": 1,
      "title": "Planted tomato seeds",
      "content": "Started 12 heirloom tomato seeds in seed trays...",
      "entry_date": "2025-10-06",
      "created_at": "2025-10-06T14:30:00.000000Z",
      "updated_at": "2025-10-06T14:30:00.000000Z"
    },
    {
      "id": 2,
      "title": "Watered roses",
      "content": "Deep watering session for the rose garden...",
      "entry_date": "2025-10-08",
      "created_at": "2025-10-08T09:15:00.000000Z",
      "updated_at": "2025-10-08T09:15:00.000000Z"
    }
  ],
  "meta": {
    "start_date": "2025-10-06",
    "end_date": "2025-10-12",
    "total_entries": 2
  }
}
```

### Empty Result Response (200 OK)
```json
{
  "data": [],
  "meta": {
    "start_date": "2025-10-06",
    "end_date": "2025-10-12",
    "total_entries": 0
  }
}
```

### Error Responses

**400 Bad Request** - Invalid date format or missing parameters
```json
{
  "message": "Validation failed",
  "errors": {
    "start_date": ["The start date field is required."],
    "end_date": ["The end date must be a valid date in YYYY-MM-DD format."]
  }
}
```

**401 Unauthorized** - Missing or invalid authentication
```json
{
  "message": "Unauthenticated."
}
```

**422 Unprocessable Entity** - Invalid date range logic
```json
{
  "message": "Validation failed",
  "errors": {
    "end_date": ["The end date must be after or equal to start date."]
  }
}
```

## 5. Data Flow

1. **Request Reception**: Controller receives GET request with start_date and end_date query parameters
2. **Authentication Check**: Middleware verifies user is authenticated via Sanctum or session
3. **Input Validation**: Validate date parameters using Form Request class
4. **Authorization**: Implicit - query automatically scoped to authenticated user's entries
5. **Database Query**:
    - Query journal_entries table with WHERE clause filtering by user_id and date range
    - Utilize composite index (user_id, entry_date) for optimal performance
    - Order results by entry_date ASC
6. **Response Formatting**: Transform Eloquent collection to JSON resource with metadata
7. **Response Delivery**: Return JSON response with 200 status code

## 6. Security Considerations

### Authentication
- Endpoint must be protected by Laravel Sanctum authentication middleware
- Reject all unauthenticated requests with 401 status

### Authorization & Data Isolation
- **Critical**: Always filter queries by authenticated user's ID using global scope or explicit WHERE clause
- Never expose other users' journal entries
- Implement user_id filtering at query level, not application level
- Consider implementing Laravel Policy for explicit authorization checks

### Input Validation
- Validate date format strictly (YYYY-MM-DD only)
- Sanitize date inputs to prevent SQL injection (Laravel query builder handles this)
- Validate that start_date is before or equal to end_date
- Optional: Validate that date range doesn't exceed reasonable limits (e.g., 7 days)
- Optional: Validate that start_date is Monday and end_date is Sunday for calendar consistency

### Rate Limiting
- Apply rate limiting middleware to prevent abuse
- Recommended: 60 requests per minute per user for calendar navigation

## 7. Error Handling

### Validation Errors (422)
**Scenario**: Invalid date format, missing parameters, or invalid date range
**Handling**:
- Use Laravel Form Request validation
- Return structured validation error messages
- Log validation failures for monitoring

### Authentication Errors (401)
**Scenario**: Missing or expired authentication token
**Handling**:
- Middleware automatically rejects unauthenticated requests
- Return standard Laravel authentication error message
- Frontend should redirect to login page

### Database Errors (500)
**Scenario**: Database connection failure or query errors
**Handling**:
- Catch database exceptions in global exception handler
- Log error details with context (user_id, query parameters)
- Return generic error message to user (don't expose database details)
- Consider implementing database health checks

### Empty Results (200)
**Scenario**: No entries found in date range
**Handling**:
- Return 200 status with empty data array
- Include metadata showing requested date range
- Frontend displays empty calendar state

### Date Range Edge Cases
**Scenario**: Future dates, extremely old dates, or reversed date range
**Handling**:
- Validate date range logic in Form Request
- Reject future start_date if business rules require (calendar can show future weeks)
- Enforce maximum date range span (e.g., 7-31 days)

## 9. Implementation Steps

### Step 1: Create JournalEntry Model  (if not exists)
- Check if JournalEntry model exists; if not, create it
- Generate Eloquent model using artisan command
- Define table name as 'journal_entries'
- Set fillable fields: title, content, entry_date, user_id
- Configure date casting for entry_date field to Carbon\CarbonImmutable
- Disable timestamps or use default Laravel timestamps (created_at, updated_at)
- Define belongsTo relationship with User model
- Implement global scope to automatically filter by authenticated user's ID
- Consider adding accessor for formatted entry_date if needed

### Step 3: Create Form Request for Validation
- Generate GetEntriesByDateRangeRequest Form Request class
- Define authorization method to return true (authentication handled by middleware)
- Define validation rules:
    - start_date: required, date, date_format:Y-m-d
    - end_date: required, date, date_format:Y-m-d, after_or_equal:start_date
- Add custom validation messages for user-friendly error responses
- Add custom validation rule to enforce 7-day or maximum range limit
- Validate start_date is Monday and end_date is Sunday

### Step 4: Create API Resource for Response Formatting (or use existing)
- Generate JournalEntryResource using artisan command
- Define toArray method to structure response data
- Map model attributes to response fields (id, title, content, entry_date, timestamps)
- Consider creating JournalEntryCalendarResource for lightweight calendar view (exclude content)
- Add conditional fields if needed (e.g., only include content for detail view)

### Step 5: Create API Controller
- Generate JournalEntryController in App\Http\Controllers\Api namespace
- Inject GetEntriesByDateRangeRequest into controller method via type-hinting
- Create index method or dateRange method for this endpoint
- Type-hint Form Request to trigger automatic validation
- Extract validated start_date and end_date from request
- Query JournalEntry model with where clauses for user_id and date range
- Use whereBetween for entry_date column with start_date and end_date
- Apply orderBy for entry_date ascending, then created_at ascending
- Use get method to retrieve collection (no pagination for weekly view)
- Wrap result in JournalEntryResource collection
- Return JSON response with data and meta fields
- Add meta information (start_date, end_date, total_entries count)

### Step 6: Define API Route
- Open routes/api.php file
- Create route group with auth:sanctum middleware (or appropriate authentication middleware)
- Define GET route: /api/journal-entries/date-range
- Point route to JournalEntryController dateRange method
- Use resource naming convention for consistency
- Consider adding rate limiting middleware (throttle:60,1)
- Ensure route is within authenticated middleware group

### Step 7: Implement Data Isolation via Global Scope
- Create JournalEntry global scope that automatically adds WHERE user_id = auth()->id()
- Register global scope in JournalEntry model's booted method
- Test that queries automatically filter by authenticated user
- This prevents accidentally exposing other users' data
- Alternatively, use explicit WHERE clause in controller if global scope is too broad

### Step 10: Implement Error Handling
- Leverage Laravel's default exception handler for most errors
- Catch specific exceptions in controller if custom handling needed
- Use try-catch for database errors if implementing fallback behavior
- Return appropriate HTTP status codes (200, 400, 401, 422, 500)
- Log errors with context for debugging
