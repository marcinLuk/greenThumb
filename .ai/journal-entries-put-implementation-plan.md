# API Endpoint Implementation Plan: Update Journal Entry

## 1. Endpoint Overview
This endpoint allows authenticated users to update an existing journal entry. Users can modify the title, content, and entry date of their own journal entries. The endpoint enforces data isolation to ensure users can only update their own entries and validates that the entry date is not in the future.

## 2. Request Details
- **HTTP Method**: PUT
- **URL Structure**: `/api/journal-entries/{id}`
- **Parameters**:
    - **Path Parameters**:
        - `id` (required): The unique identifier of the journal entry to update (BIGINT UNSIGNED)
    - **Required Body Parameters**:
        - `title` (string, max 255 characters): The updated title of the journal entry
        - `content` (text): The updated content/description of the journal entry
        - `entry_date` (date, format: Y-m-d): The updated date for the entry (must be past or present, no time component)
    - **Optional Parameters**: None
- **Request Body Example Structure**:
```json
{
    "title": "Updated tomato planting notes",
    "content": "Updated detailed observations about the tomato plants...",
    "entry_date": "2025-10-08"
}
```
- **Content-Type**: application/json
- **Authentication**: Required (Laravel Sanctum token or session-based)

## 3. Used Types
- **JournalEntry Model**: Primary model representing the journal_entries table
    - Relationships: belongsTo User
    - Attributes: id, user_id, title, content, entry_date, created_at, updated_at
- **User Model**: For authentication and data isolation
    - Relationship: hasMany JournalEntry

## 4. Response Details

### Success Response (200 OK)
```json
{
    "data": {
        "id": 123,
        "title": "Updated tomato planting notes",
        "content": "Updated detailed observations about the tomato plants...",
        "entry_date": "2025-10-08",
        "created_at": "2025-10-07T10:30:00.000000Z",
        "updated_at": "2025-10-10T14:22:00.000000Z"
    }
}
```

### Error Responses

**404 Not Found** - Entry does not exist or user does not own it:
```json
{
    "message": "Journal entry not found"
}
```

**422 Unprocessable Entity** - Validation failure:
```json
{
    "message": "The given data was invalid",
    "errors": {
        "title": ["The title field is required"],
        "entry_date": ["The entry date must not be in the future"]
    }
}
```

**401 Unauthorized** - User not authenticated:
```json
{
    "message": "Unauthenticated"
}
```

## 5. Data Flow

1. **Request Reception**: Laravel routes the PUT request to the appropriate controller method
2. **Authentication Check**: Middleware verifies the user is authenticated via Sanctum or session
3. **Entry Retrieval**: Controller attempts to find the journal entry by ID with user_id constraint
4. **Authorization Check**: Implicit authorization through user_id WHERE clause ensures data isolation
5. **Validation**: Form request validates incoming data (title, content, entry_date format and restrictions)
6. **Date Validation**: Custom validation ensures entry_date is not in the future
7. **Update Operation**: Eloquent ORM updates the model with validated data
9. **Response Generation**: Return JSON response with updated entry data and success message
10. **Error Handling**: Any failures return appropriate HTTP status codes with error messages

## 6. Security Considerations

### Authentication
- Use Laravel Sanctum middleware to ensure only authenticated users can access the endpoint
- Verify token validity and user session status

### Authorization & Data Isolation
- Implement user data isolation by querying journal entries with WHERE user_id = auth()->id()
- Never expose other users' entries through the ID parameter
- Consider using Laravel Policy for explicit authorization checks (optional but recommended)
- Return 404 (not 403) when entry doesn't exist or doesn't belong to user to avoid information leakage

### Input Validation
- Sanitize all input data through Laravel's validation system
- Prevent SQL injection through Eloquent ORM parameterized queries
- Validate title length (max 255 characters per database schema)
- Validate content as required text field
- Enforce date format validation (Y-m-d)
- Prevent future dates through custom validation rule

### Mass Assignment Protection
- Define fillable attributes on JournalEntry model: title, content, entry_date
- Never allow mass assignment of user_id or id fields
- Ensure guarded property is not set to empty array

### Rate Limiting
- Consider implementing rate limiting middleware to prevent abuse
- Suggested limit: 60 requests per minute per user

## 7. Error Handling

### Validation Errors (422)
- Missing required fields (title, content, entry_date)
- Title exceeds 255 characters
- Invalid date format
- Future date provided
- Empty string values for required fields

### Not Found Errors (404)
- Journal entry ID does not exist in database
- Journal entry exists but belongs to different user (enforce data isolation)
- Return consistent error message: "Journal entry not found"

### Authentication Errors (401)
- Missing or invalid authentication token
- Expired session
- Middleware should handle automatically

### Handling Strategy
- Use try-catch blocks in controller for unexpected errors
- Log errors using Laravel's logging system
- Return appropriate HTTP status codes
- Provide clear, actionable error messages
- Never expose sensitive system information in error responses

## 9. Implementation Steps

### Step 1: Create or Update JournalEntry Model
- Generate the JournalEntry model if not already created
- Define the table name as 'journal_entries'
- Set fillable attributes: ['title', 'content', 'entry_date']
- Configure the entry_date column as a date cast (not datetime)
- Define the belongsTo relationship with User model
- Ensure timestamps are enabled (created_at, updated_at)
- Set the connection to MySQL if not default

### Step 2: Create Update Request Validation Class
- Generate a FormRequest class named UpdateJournalEntryRequest
- Define authorization method to return true (authorization handled at query level)
- Create validation rules array with:
    - title: required, string, max 255 characters
    - content: required, string
    - entry_date: required, date format Y-m-d, custom rule for not future
- Implement custom validation rule or use Laravel's before_or_equal:today for date restriction
- Define custom error messages for better UX
- Configure validation to stop on first failure for each field

### Step 3: Create API Controller
- Generate an API controller named JournalEntryController in app/Http/Controllers/Api directory
- Create an update method that accepts UpdateJournalEntryRequest and the entry ID
- Inject the validated request data and route parameter ID into the method signature

### Step 4: Implement Update Method Logic
- Retrieve the journal entry using Eloquent with composite WHERE clause: where('id', $id)->where('user_id', auth()->id())
- Use firstOrFail() to automatically throw 404 if not found
- Update the model with validated data using the update() method or mass assignment
- Catch ModelNotFoundException and return JSON 404 response
- Wrap logic in try-catch for unexpected errors, log them, and return 500 response

### Step 5: Create API Resource for Response Transformation
- Generate a JournalEntryResource in app/Http/Resources
- Define the toArray method to structure the response data
- Include: id, title, content, entry_date, created_at, updated_at
- Format dates consistently using Carbon or default ISO 8601 format
- Add optional fields like formatted_entry_date for frontend convenience

### Step 6: Configure Routes
- Open routes/api.php
- Define the PUT route: Route::put('/journal-entries/{id}', [JournalEntryController::class, 'update'])
- Apply auth:sanctum middleware to the route or route group
- Consider grouping all journal entry routes under a prefix
- Ensure route model binding is not used (manual query needed for user_id check)

### Step 7: Apply Middleware
- Ensure auth:sanctum middleware is applied via route definition or controller constructor
- Consider adding throttle middleware for rate limiting (e.g., throttle:60,1)
- Verify EnsureEmailIsVerified middleware if email verification is required per PRD

### Step 8: Implement Data Isolation at Query Level
- In the update method, always scope the query with where('user_id', auth()->id())
- Never trust the ID parameter alone without user_id verification
- This approach prevents unauthorized access and returns 404 for non-owned entries

### Step 9: Add Date Validation Rule
- Create a custom validation rule or use Laravel's built-in before_or_equal:today
- Ensure the rule checks that entry_date is not in the future
- Add clear error message: "The entry date must not be in the future"
- Consider timezone handling (store dates in UTC, validate against user's timezone if needed)

### Step 10: Configure Error Responses
- Implement global exception handler customization if needed in app/Exceptions/Handler.php
- Ensure ModelNotFoundException returns 404 with JSON response in API context
- Configure ValidationException to return 422 with error details
- Test that all error responses return proper JSON format with message and errors keys
