# API Endpoint Implementation Plan: Create Journal Entry

## 1. Endpoint Overview
This endpoint allows authenticated users to create new journal entries for their gardening activities. Each entry consists of a title, content, and date. The system enforces a maximum of 50 entries per user and restricts entry dates to past or present dates only.

## 2. Request Details
- **HTTP Method**: POST
- **URL Structure**: `/api/journal-entries`
- **Authentication**: Required (Laravel Sanctum token or session)
- **Parameters**:
  - Required:
    - `title` (string, max 255 characters)
    - `content` (text, required)
    - `entry_date` (date, format: Y-m-d, must be today or in the past)
  - Optional: None
- **Request Body** (JSON):
```json
{
  "title": "Planted tomato seedlings",
  "content": "Transplanted 6 Roma tomato seedlings into the raised bed...",
  "entry_date": "2025-10-10"
}
```

## 3. Used Types
- **JournalEntry Model**: Main model for storing journal entries
- **EntriesCount Model**: Model for tracking entry count per user (for 50-entry limit enforcement)
- **User Model**: Existing authentication model (relationship reference)

## 4. Response Details

**Success Response (201 Created)**:
```json
{
  "data": {
    "id": 123,
    "user_id": 1,
    "title": "Planted tomato seedlings",
    "content": "Transplanted 6 Roma tomato seedlings into the raised bed...",
    "entry_date": "2025-10-10",
    "created_at": "2025-10-10T14:30:00.000000Z",
    "updated_at": "2025-10-10T14:30:00.000000Z"
  }
}
```

**Error Responses**:
- `400 Bad Request`: Validation errors (future date, missing required fields, etc.)
- `401 Unauthorized`: User not authenticated
- `403 Forbidden`: User has reached 50-entry limit

## 5. Data Flow
1. User sends POST request with entry data to the API endpoint
2. Laravel middleware authenticates the user via Sanctum/session
3. Controller receives request and delegates to validation
4. Request validation checks:
   - Title and content are present and meet requirements
   - Entry date is in valid format and not in the future
5. Controller checks if user has reached 50-entry limit via EntriesCount model
6. If validation passes and limit not reached, create new JournalEntry record
7. JournalEntry observer automatically increments user's entries_count
8. Return success response with created entry data
9. If any step fails, return appropriate error response

## 6. Security Considerations
- **Authentication**: Enforce authentication middleware on the route to ensure only logged-in users can create entries
- **Authorization**: Use Laravel policy to ensure users can only create entries for themselves (check via user_id)
- **Data Isolation**: Always set user_id from authenticated user (auth()->id()), never from request input
- **Input Validation**: Sanitize and validate all input fields to prevent XSS and injection attacks
- **Rate Limiting**: Consider implementing rate limiting to prevent abuse (e.g., max 100 requests per hour)
- **CSRF Protection**: Ensure CSRF protection is enabled for web-based requests
- **Mass Assignment Protection**: Define $fillable attributes on JournalEntry model to prevent mass assignment vulnerabilities

## 7. Error Handling

**Validation Errors**:
- Title missing or exceeds 255 characters: Return 400 with message
- Content missing: Return 400 with message
- Entry date missing or invalid format: Return 400 with message
- Entry date is in the future: Return 400 with message "Entry date must be today or in the past"

**Business Logic Errors**:
- User has 50 entries already: Return 403 with message "You have reached the maximum limit of 50 journal entries"

**Authentication Errors**:
- No valid session/token: Return 401 with message "Unauthenticated"

**Database Errors**:
- Foreign key constraint failure: Return 500 with generic error message
- Connection timeout: Return 500 with generic error message
- Log all database errors for debugging without exposing details to user

## 9. Implementation Steps

### Step 1: Create JournalEntry Model (if not exists)
- Check if JournalEntry model exists; if not, create it
- Generate JournalEntry model using artisan command
- Define table name as `journal_entries`
- Set fillable attributes: `user_id`, `title`, `content`, `entry_date`
- Configure timestamp behavior (use default created_at and updated_at)
- Set casts for `entry_date` to `date` type for proper date handling
- Implement `preventSilentlyDiscardingAttributes()` in development environment

### Step 3: Create EntriesCount Model
- Generate EntriesCount model using artisan command
- Define table name as `entries_count`
- Set primary key as `user_id` instead of default `id`
- Disable auto-incrementing for primary key
- Set fillable attributes: `user_id`, `count`
- Set default value for `count` as 0
- Configure timestamps (disable if not needed per schema)

### Step 4: Configure EntriesCount Relationships
- Define belongsTo relationship to User model
- In User model, define hasOne relationship to EntriesCount

### Step 5: Create JournalEntry Observer
- Generate JournalEntryObserver using artisan command
- Implement `created` event handler to increment entries_count
- Implement `deleted` event handler to decrement entries_count
- Use updateOrCreate on EntriesCount model to handle first entry case
- Ensure observer implements ShouldHandleEventsAfterCommit interface for transaction safety
- Register observer in EventServiceProvider or AppServiceProvider

### Step 6: Create JournalEntry Policy
- Generate JournalEntryPolicy using artisan command
- Implement `create` method to check if user has fewer than 50 entries
- Implement `view`, `update`, `delete` methods to ensure user_id matches authenticated user

### Step 7: Create FormRequest for Validation
- Generate StoreJournalEntryRequest using artisan command
- Define authorization logic (check policy)
- Define validation rules:
  - `title`: required, string, max 255 characters
  - `content`: required, string
  - `entry_date`: required, date format Y-m-d, date before or equal to today
- Add custom validation messages for better UX
- Consider custom validation rule for entry date restriction

### Step 8: Create API Controller
- Generate JournalEntryController in `app/Http/Controllers/Api/` directory
- Make controller an API resource controller
- Inject StoreJournalEntryRequest in store method for automatic validation
- Implement store method logic:
  - Check authorization using policy (authorize create action)
  - Query entries_count to verify user hasn't reached 50-entry limit
  - If limit reached, return 403 response with appropriate message
  - Create new JournalEntry with validated data
  - Merge user_id from auth()->id() with request data
  - Use database transaction to ensure atomicity
  - Return 201 response with created resource using JSON resource

### Step 9: Create JSON Resource
- Generate JournalEntryResource using artisan command
- Define resource transformation:
  - Map model attributes to response structure
  - Format dates appropriately
  - Include only necessary fields (exclude sensitive data if any)
- Use resource in controller response

### Step 10: Define API Route
- Open `routes/api.php` file
- Define POST route for `/journal-entries`
- Apply auth:sanctum middleware (or session-based auth middleware)
- Bind route to JournalEntryController@store method
- Consider grouping with other journal entry routes under common prefix
- Apply rate limiting middleware if needed

### Step 11: Add Global Scope for Data Isolation
- Create JournalEntryScope global scope class
- Apply WHERE user_id = auth()->id() constraint automatically
- Register scope in JournalEntry model's boot method
- This ensures all queries automatically filter by authenticated user

### Step 12: Implement Error Handling
- Create custom exception handler for entry limit exceeded
- In controller, wrap logic in try-catch blocks
- Return appropriate HTTP status codes for different error scenarios
- Log errors without exposing sensitive information to client
- Use Laravel's exception handler to format validation errors consistently

### Step 13: Add Database Transactions
- Wrap entry creation and count increment in DB transaction
- Ensure rollback occurs if any operation fails
- Place transaction logic in controller or consider extracting to service class
- Use DB facade's transaction method for automatic rollback on exceptions

### Step 15: Configure API Response Format
- Ensure consistent API response structure across application
- Consider using API resource collections for consistent formatting
- Add appropriate HTTP status codes
- Include meaningful error messages in error responses
