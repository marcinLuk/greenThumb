# API Endpoint Implementation Plan: AI Search

## 1. Endpoint Overview
This endpoint receives natural language search queries from authenticated users and returns relevant journal entries. 
The endpoint handles query processing, result retrieval, analytics logging, and error handling. The actual AI processing logic is intentionally excluded from this implementation plan.

## 2. Request Details
- **HTTP Method**: POST
- **URL Structure**: `/api/search`
- **Authentication**: Required (Laravel Sanctum token or session-based)
- **Parameters**:
  - Required:
    - `query` (string, min: 3 characters, max: 500 characters) - The natural language search query
  - Optional: None

- **Request Body**:
```json
{
  "query": "when did I plant tomatoes?"
}
```

## 3. Used Types

### Models Required
1. **JournalEntry** - Primary model for retrieving user's journal entries
2. **SearchAnalytic** - Model for logging search queries and metrics
3. **User** - Accessed via authentication for user identification

### Relationships
- JournalEntry belongs to User
- SearchAnalytic belongs to User

## 4. Response Details

### Success Response (200 OK)
```json
{
  "success": true,
  "data": {
    "summary": "AI-generated summary of findings (placeholder for AI integration)",
    "entries": [
      {
        "id": 1,
        "title": "Planted tomatoes",
        "content": "Planted 5 tomato seedlings...",
        "entry_date": "2025-09-15",
        "created_at": "2025-09-15T10:30:00Z",
        "updated_at": "2025-09-15T10:30:00Z"
      }
    ],
    "results_count": 1
  }
}
```

### Error Responses
- **401 Unauthorized**: User not authenticated
- **422 Unprocessable Entity**: Validation failed
- **500 Internal Server Error**: Search processing failed

## 5. Data Flow

1. **Request Reception**: Controller receives POST request with search query
2. **Authentication Check**: Verify user is authenticated via middleware
3. **Input Validation**: Validate query parameter meets requirements
4. **User Context**: Retrieve authenticated user's ID
5. **Data Retrieval**: Fetch all journal entries belonging to the authenticated user
6. **Search Processing**: Process query against user's entries (AI integration point - not detailed here)
7. **Result Compilation**: Format matching entries for response
8. **Analytics Logging**: Record search query, results count, and user ID to search_analytics table
9. **Response Return**: Send formatted JSON response to client

## 6. Security Considerations

### Authentication & Authorization
- Endpoint must be protected by authentication middleware (auth:sanctum or web)
- User can only search their own journal entries (enforced via query scope)
- Query must be sanitized to prevent injection attacks

### Data Isolation
- All database queries must include `WHERE user_id = auth()->id()`
- Use Laravel global scopes on JournalEntry model to automatically filter by user
- Never expose other users' data in responses

### Input Validation
- Enforce minimum query length (3 characters) to prevent abuse
- Enforce maximum query length (500 characters) to prevent payload attacks
- Sanitize input to remove potentially harmful content
- Rate limit the endpoint to prevent abuse (consider 10 requests per minute per user)

### Data Privacy
- Do not log sensitive query content in application logs
- Ensure search_analytics table is protected and only accessible via authorized queries
- User data must never be transmitted to external services without explicit implementation

## 7. Error Handling

### Validation Errors (422)
- Empty query string
- Query too short (< 3 characters)
- Query too long (> 500 characters)
- Invalid data types

**Response Example**:
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "query": ["The query must be at least 3 characters."]
  }
}
```

### Authentication Errors (401)
- Missing authentication token
- Invalid or expired token
- Unauthenticated user

**Response Example**:
```json
{
  "message": "Unauthenticated."
}
```

### Server Errors (500)
- Database connection failures
- Search processing failures
- Analytics logging failures (should not block response)

**Response Example**:
```json
{
  "success": false,
  "message": "An error occurred while processing your search. Please try again."
}
```
## 9. Implementation Steps

### Step 1: Create SearchAnalytic Model
- Generate Eloquent model for search_analytics table
- Define table name as `search_analytics`
- Set fillable fields: user_id, results_count
- Configure timestamps (created_at will track when search occurred)
- Define belongsTo relationship with User model
- Add any necessary casts (results_count as integer)

### Step 2: Update JournalEntry Model
- Ensure global scope exists to filter by authenticated user
- Verify relationships are properly defined (belongsTo User)
- Confirm fillable fields include: user_id, title, content, entry_date
- Add date casts for entry_date field
- Ensure model uses soft deletes if required (check PRD - currently no soft deletes)

### Step 3: Create SearchController
- Generate API controller in `app/Http/Controllers/Api/SearchController.php`
- Create single action method: `search(Request $request)`
- Inject dependencies via constructor if needed (no repositories required)
- Follow Laravel 12 controller conventions

### Step 4: Implement Request Validation
- Create form request class `SearchRequest` in `app/Http/Requests/Api/`
- Define validation rules:
  - query: required, string, min:3, max:500
- Define custom error messages for better UX
- Include authorization method returning true for authenticated users

### Step 5: Implement Search Logic Structure
- In SearchController@search method:
  - Accept SearchRequest as parameter (auto-validates)
  - Retrieve authenticated user via `auth()->user()` or `$request->user()`
  - Fetch all journal entries for authenticated user
  - Create placeholder for AI processing integration point
  - For MVP, return all entries or implement basic keyword matching as fallback
  - Prepare results array with entry data

### Step 6: Implement Analytics Logging
- After search results are compiled, create SearchAnalytic record
- Use try-catch to handle logging failures gracefully
- Log: user_id, results_count
- Consider dispatching a job for async logging
- Ensure logging failure does not prevent search response

### Step 7: Format Response
- Create consistent response structure using Laravel response helpers
- Include success status, data object with summary and entries
- Return results_count for client-side metrics
- Use appropriate HTTP status codes (200 for success, 500 for errors)
- Consider using API Resource classes for consistent entry formatting

### Step 8: Define Routes
- Add route in `routes/api.php`
- Route definition: `Route::post('/search', [SearchController::class, 'search'])`
- Apply authentication middleware: `->middleware('auth:sanctum')`
- Apply rate limiting middleware: `->middleware('throttle:search')`
- Consider route naming: `->name('api.search')`

### Step 9: Configure Rate Limiting
- Define custom rate limiter for search endpoint in `app/Providers/AppServiceProvider.php` or `RouteServiceProvider.php`
- Set appropriate limits (e.g., 10 requests per minute per user)
- Configure rate limit response message
- Consider different limits for authenticated vs. guest users (though guests shouldn't access this endpoint)

### Step 10: Create API Resource (Optional but Recommended)
- Generate JournalEntryResource in `app/Http/Resources/`
- Define which fields to expose in API responses
- Format dates consistently
- Truncate content if needed for list view
- Use resource for consistent response formatting

### Step 11: Add Middleware
- Ensure `auth:sanctum` middleware is applied to route
- Add custom rate limiting middleware for search endpoint
- Consider adding middleware to ensure email verification if required
- Apply any additional security middleware needed

### Step 12: Error Handling
- Implement try-catch blocks for database operations
- Handle validation errors via Laravel's automatic validation
- Create custom exception handler if needed for search-specific errors
- Log errors appropriately for debugging without exposing sensitive data
- Return user-friendly error messages

### Step 13: Testing Preparation
- Create feature test for search endpoint
- Test cases should include:
  - Authenticated user can perform search
  - Unauthenticated user receives 401
  - Validation errors for invalid queries
  - User only receives their own entries
  - Analytics are logged correctly
  - Rate limiting works as expected
- Mock any external service calls during testing

### Step 14: Integration Point Documentation
- Add clear comments in code indicating where AI processing should be integrated
- Document expected input format for AI integration
- Document expected output format from AI integration
- Create placeholder method for AI processing that can be replaced later
- Ensure code structure allows easy integration of external AI service

### Step 15: Observer Setup (Optional)
- Consider creating SearchAnalyticObserver if additional logic needed on analytics creation
- Register observer in EventServiceProvider if created
- Use observer to handle side effects of search analytics creation
