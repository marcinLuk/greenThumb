# API Endpoint Implementation Plan: GET Journal Entries

## 1. Endpoint Overview
This endpoint retrieves journal entries for the authenticated user. 
It supports filtering by date range to enable the weekly calendar view functionality. The endpoint enforces strict user data isolation, ensuring users can only access their own journal entries.

## 2. Request Details
- **HTTP Method**: GET
- **URL Structure**: `/api/journal-entries`
- **Authentication**: Required (Laravel Sanctum token or session-based)
- **Parameters**:
  - **Optional**:
    - `start_date` (date, format: YYYY-MM-DD) - Filter entries from this date onwards
    - `end_date` (date, format: YYYY-MM-DD) - Filter entries up to this date
    - `sort` (string, default: 'desc') - Sort order by entry_date ('asc' or 'desc')
    - `per_page` (integer, default: 50, max: 50) - Number of entries per page
    - `page` (integer, default: 1) - Page number for pagination
- **Request Body**: None (GET request)
- **Headers**:
  - `Accept: application/json`
  - `Authorization: Bearer {token}` (if using Sanctum tokens)

## 3. Used Types

### Models
- **JournalEntry** - Primary model representing a journal entry
  - Attributes: id, user_id, title, content, entry_date, created_at, updated_at
  - Relationships: belongsTo User
  - Scopes: For filtering by date range and user

- **User** - Related model for authentication
  - Relationship: hasMany JournalEntry

## 4. Response Details

### Success Response (200 OK)
```
{
  "data": [
    {
      "id": 1,
      "title": "Planted tomatoes",
      "content": "Planted 5 tomato seedlings in the south garden bed...",
      "entry_date": "2025-10-05",
      "created_at": "2025-10-05T14:30:00.000000Z",
      "updated_at": "2025-10-05T14:30:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 50,
    "total": 12,
    "last_page": 1
  }
}
```

### Error Responses
- **401 Unauthorized**: User not authenticated
- **422 Unprocessable Entity**: Invalid query parameters (e.g., invalid date format)
- **500 Internal Server Error**: Unexpected server error

## 5. Data Flow

1. Request arrives at API endpoint with optional query parameters
2. Authentication middleware verifies user identity
3. Controller receives request and extracts query parameters
4. Validation rules check parameter formats and constraints
5. Query builder constructs database query with user_id filter (global scope or explicit WHERE)
6. Apply date range filters if provided (start_date, end_date)
7. Apply sorting based on entry_date field
8. Execute query with pagination
9. Transform results into API resource format
10. Return JSON response with data and pagination metadata

## 6. Security Considerations

### Authentication
- Endpoint must be protected by authentication middleware (auth:sanctum or auth)
- Verify user is authenticated before processing request

### Authorization
- Implement global scope on JournalEntry model to automatically filter by authenticated user's ID
- Never allow user_id to be passed as a query parameter
- Always use `auth()->id()` or equivalent to get current user
- Prevent access to other users' entries through explicit WHERE clauses or global scopes

### Input Validation
- Validate date format for start_date and end_date parameters
- Ensure end_date is not before start_date
- Validate sort parameter accepts only 'asc' or 'desc'
- Limit per_page to maximum of 50 (matching entry limit per user)
- Sanitize all input parameters

### Rate Limiting
- Apply rate limiting middleware to prevent abuse
- Consider throttling to 60 requests per minute per user

## 7. Error Handling

### Validation Errors (422)
- Invalid date format for start_date or end_date
- end_date before start_date
- Invalid sort parameter value
- per_page exceeds maximum (50)
- Negative page number

### Authentication Errors (401)
- Missing authentication token
- Expired token
- Invalid token

### Server Errors (500)
- Database connection failures
- Unexpected exceptions during query execution

### Error Response Format
```
{
  "message": "The given data was invalid.",
  "errors": {
    "start_date": ["The start date must be a valid date format."],
    "end_date": ["The end date must be after or equal to start date."]
  }
}
```

## 9. Implementation Steps

### Step 1: Create JournalEntry Model
1. Generate JournalEntry model using artisan command
2. Define table name as 'journal_entries'
3. Set fillable attributes: title, content, entry_date, user_id
4. Define casts: entry_date as date, created_at and updated_at as datetime
5. Implement belongsTo relationship to User model
6. Create global scope to automatically filter by authenticated user ID
7. Create local scope for date range filtering (scopeWithinDateRange)
8. Create local scope for sorting by entry_date (scopeSortByDate)
9. Add validation rule methods or form request for date constraints

### Step 2: Update User Model
1. Add hasMany relationship to JournalEntry model
2. Specify foreign key as user_id if not default

### Step 3: Create API Resource for JournalEntry
1. Generate JournalEntryResource using artisan command
2. Define toArray method to format response data
3. Include: id, title, content, entry_date, created_at, updated_at
4. Exclude: user_id (security - don't expose in API response)
5. Format dates consistently in ISO 8601 format

### Step 4: Create JournalEntry Collection Resource (Optional)
1. Generate JournalEntryCollection resource for custom collection formatting
2. Add pagination metadata structure
3. Wrap data array with meta information

### Step 5: Create Form Request for Validation
1. Generate GetJournalEntriesRequest form request class
2. Set authorize method to return true (handled by middleware)
3. Define validation rules:
   - start_date: nullable, date format Y-m-d, before or equal to today
   - end_date: nullable, date format Y-m-d, after or equal to start_date, before or equal to today
   - sort: nullable, in array (asc, desc)
   - per_page: nullable, integer, min 1, max 50
   - page: nullable, integer, min 1
4. Add custom error messages for better UX
5. Add method to check if date range is valid

### Step 6: Create JournalEntryController
1. Generate JournalEntryController as API resource controller
2. Add constructor to apply auth middleware
3. Implement index method for GET request
4. Type-hint GetJournalEntriesRequest in index method signature
5. Retrieve authenticated user via auth helper
6. Build query starting with authenticated user's entries
7. Apply date range scope if start_date and end_date provided
8. Apply sorting scope based on sort parameter (default desc)
9. Execute pagination with per_page parameter (default 50)
10. Return JournalEntryResource collection with paginated results
11. Add appropriate response status code (200)

### Step 7: Define API Route
1. Open routes/api.php file
2. Add route within auth:sanctum middleware group
3. Define GET route pointing to JournalEntryController@index
4. Use resource routing or explicit route definition
5. Ensure route is prefixed with /api
6. Apply throttle middleware for rate limiting
7. Name route as 'journal-entries.index' for consistency

### Step 8: Add Global Scope for User Isolation
1. Create UserOwnedScope global scope class
2. Implement apply method to add WHERE user_id clause
3. Attach scope to JournalEntry model using static::addGlobalScope
4. Alternatively, use ObservedBy attribute with model boot method
5. Ensure scope automatically applies to all queries
6. Test that users cannot access other users' entries

### Step 10: Configure Authentication
1. Ensure Laravel Sanctum is installed and configured
2. Add Sanctum middleware to api routes if using token authentication
5. Ensure auth middleware is properly configured for API routes

### Step 11: Add Rate Limiting
1. Configure throttle middleware in routes
2. Set appropriate limits (e.g., 60 requests per minute)
3. Customize rate limit response message
4. Consider different limits for different user roles if needed
