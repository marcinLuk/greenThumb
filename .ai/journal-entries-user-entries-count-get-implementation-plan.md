# API Endpoint Implementation Plan: Get User Entry Count

## 1. Endpoint Overview
This endpoint retrieves the current journal entry count for the authenticated user. It returns the number of entries the user has created, which is essential for enforcing the 50-entry maximum limit per user (as specified in Section 3.2 of the PRD). The count is maintained in the `entries_count` table and updated via Laravel observers when entries are created or deleted.

## 2. Request Details
- **HTTP Method**: GET
- **URL Structure**: `/api/user/entry-count`
- **Authentication**: Required (Sanctum token or session-based)
- **Parameters**:
    - Required: None (user identification comes from authentication)
    - Optional: None
- **Request Body**: None (GET request)

## 3. Used Types

### Models
- **User** (Eloquent model representing authenticated user)
  - Location: `app/Models/User.php`
  - Relationships needed: `hasOne` relationship to `EntryCount`

- **EntryCount** (Eloquent model for tracking entry counts)
  - Location: `app/Models/EntryCount.php`
  - Table: `entries_count`
  - Primary Key: `user_id`
  - Attributes: `user_id`, `count`
  - Relationships needed: `belongsTo` relationship to `User`

### DTOs/Resources
- **EntryCountResource** (API Resource for formatting response)
  - Location: `app/Http/Resources/EntryCountResource.php`
  - Purpose: Transform EntryCount model data into consistent API response format

## 4. Response Details

### Success Response (200 OK)
```json
{
  "data": {
    "count": 23,
    "limit": 50,
    "remaining": 27,
    "is_at_limit": false
  }
}
```

### Response when user has no entry count record yet (200 OK)
```json
{
  "data": {
    "count": 0,
    "limit": 50,
    "remaining": 50,
    "is_at_limit": false
  }
}
```

### Response when user is at limit (200 OK)
```json
{
  "data": {
    "count": 50,
    "limit": 50,
    "remaining": 0,
    "is_at_limit": true
  }
}
```

### Error Responses
- **401 Unauthorized**: User is not authenticated
  ```json
  {
    "message": "Unauthenticated."
  }
  ```

## 5. Data Flow

1. **Authentication Middleware** validates the incoming request and identifies the authenticated user
2. **Controller** receives the authenticated user from the request
3. **Model Query** retrieves the `EntryCount` record for the authenticated user:
   - Use eager loading or direct relationship access
   - If no record exists, return default count of 0
4. **Calculation** determines:
   - Current count (from database or default 0)
   - Remaining entries (50 - current count)
   - Whether user is at limit (count >= 50)
5. **Resource Transformation** formats the data into standardized API response
6. **Response** returns JSON with count information

### Interaction with Other Components
- **entries_count table**: Direct read operation to fetch current count
- **JournalEntry model**: No direct interaction (count is maintained separately via observers)
- **Authentication system**: Laravel Sanctum or session-based auth provides user context

## 6. Security Considerations

### Authentication
- Endpoint must be protected by `auth:sanctum` or `auth` middleware
- Only authenticated users can access their own entry count
- No user_id parameter should be accepted (always use authenticated user)

### Authorization
- No additional authorization needed beyond authentication
- User can only see their own count (automatic via auth()->user())

### Data Validation
- No input validation required (no user-provided parameters)
- Ensure authenticated user object is valid before querying

### Data Isolation
- Query must always filter by authenticated user's ID
- Use `auth()->user()->entryCount` or `EntryCount::where('user_id', auth()->id())`
- Never expose other users' counts

## 7. Error Handling

### Potential Errors and Handling

1. **Unauthenticated Request**
   - Cause: No valid authentication token or session
   - HTTP Status: 401
   - Handling: Laravel authentication middleware automatically returns appropriate response
   - Response: Standard Laravel authentication error message

2. **Missing EntryCount Record**
   - Cause: New user who hasn't created any entries yet
   - HTTP Status: 200 (not an error condition)
   - Handling: Return count of 0 with calculated remaining entries
   - Use `firstOrNew`, `firstOrCreate`, or null coalescing to handle gracefully

3. **Database Connection Error**
   - Cause: Database unavailable or connection timeout
   - HTTP Status: 500
   - Handling: Let Laravel's exception handler catch and log
   - Consider implementing retry logic for transient failures

4. **Invalid User Object**
   - Cause: Auth user object is null or invalid (edge case)
   - HTTP Status: 401 or 500
   - Handling: Add defensive check in controller
   - Log error and return appropriate error response

## 9. Implementation Steps

### Step 1: Create EntryCount Model (if not exists)
- Generate Eloquent model for `entries_count` table
- Set table name to `entries_count`
- Define primary key as `user_id`
- Disable auto-incrementing primary key
- Set fillable attributes: `user_id`, `count`
- Disable timestamps (table doesn't have created_at/updated_at per schema)
- Define `belongsTo` relationship to User model
- Add type casting for `count` as integer

### Step 2: Update User Model
- Add `hasOne` relationship method to User model
- Relationship name: `entryCount()`
- Return type: `HasOne`
- Target model: `EntryCount`
- Foreign key: `user_id`

### Step 3: Create API Resource for EntryCount
- Generate API Resource class for formatting entry count responses
- Include fields: count, limit (constant 50), remaining, is_at_limit
- Calculate `remaining` as (50 - count)
- Calculate `is_at_limit` as boolean (count >= 50)
- Ensure all values are integers except is_at_limit (boolean)

### Step 4: Create Controller Method
- Use existing API controller or create new UserController in `app/Http/Controllers/Api/`
- Create method: `getEntryCount()`
- No parameters needed (uses authenticated user from request)
- Retrieve authenticated user via `auth()->user()` or dependency injection
- Access user's entry count via relationship or direct query
- Handle case where no EntryCount record exists (return 0)
- Transform data using EntryCountResource
- Return JSON response with 200 status code

### Step 5: Define API Route
- Add route to `routes/api.php`
- Use GET method
- Path: `/user/entry-count`
- Apply `auth:sanctum` middleware (or appropriate auth middleware)
- Route to controller method created in Step 4
- Consider route naming for easy reference: `->name('api.user.entry-count')`

### Step 6: Add Defensive Checks
- Verify authenticated user exists before querying
- Handle null EntryCount gracefully (use firstOrNew or null coalescing)
- Add try-catch for database errors if needed
- Log any unexpected errors for debugging

### Step 7: Implement Observer for EntryCount Maintenance (if not exists)
- Create JournalEntry observer
- In `created` event: increment user's entry count
- In `deleted` event: decrement user's entry count
- Use `firstOrCreate` to handle users without existing EntryCount records
- Implement atomic increment/decrement operations
- Register observer in EventServiceProvider or Model boot method
- Add `ShouldHandleEventsAfterCommit` interface for transaction safety

### Step 8: Add Helper Method to User Model (Optional)
- Create convenience method `getRemainingEntryCount()` on User model
- Calculate and return (50 - current count)
- Create convenience method `isAtEntryLimit()` on User model
- Return boolean indicating if count >= 50
- These methods can be reused across the application