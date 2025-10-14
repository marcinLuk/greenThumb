# API Endpoint Implementation Plan: Get Journal Entry by ID

## 1. Endpoint Overview
This endpoint retrieves a single journal entry by its ID. The endpoint enforces data isolation to ensure users can only access their own journal entries. 
It returns the complete entry details including title, content, entry date, and timestamps.

## 2. Request Details
- **HTTP Method**: GET
- **URL Structure**: `/api/journal-entries/{id}`
- **Parameters**:
    - Required: `id` (integer, path parameter) - The unique identifier of the journal entry
    - Optional: None
- **Request Body**: None (GET request)
- **Authentication**: Required (user must be authenticated)

## 3. Used Types
- **JournalEntry Model**: Primary model for journal entry data
- **User Model**: For relationship verification and authentication context

## 4. Response Details

**Success Response (200 OK)**:
```json
{
  "id": 1,
  "user_id": 42,
  "title": "First tomato planting",
  "content": "Planted three Cherokee Purple tomato seedlings in the raised bed...",
  "entry_date": "2025-10-05",
  "created_at": "2025-10-05T14:30:00.000000Z",
  "updated_at": "2025-10-05T14:30:00.000000Z"
}
```

**Error Responses**:
- **401 Unauthorized**: User is not authenticated
- **403 Forbidden**: Entry belongs to another user
- **404 Not Found**: Entry does not exist

## 5. Data Flow
1. Request arrives with entry ID in URL path
2. Middleware validates authentication (Laravel Sanctum or Fortify session)
3. Controller receives authenticated user from request
4. Query builder retrieves entry with WHERE clause filtering by both ID and authenticated user_id
5. If entry not found or belongs to different user, return 404 (no 403 to avoid information disclosure)
6. If found, serialize entry data to JSON
7. Return response with entry data

## 6. Security Considerations
- **Authentication**: Enforce authentication middleware on route
- **Authorization**: Implement data isolation using one of these approaches:
    - Global scope on JournalEntry model to automatically filter by authenticated user
    - Explicit WHERE clause in controller: `where('user_id', auth()->id())`
    - Laravel Policy with `view` method to authorize access
- **Information Disclosure**: Return 404 instead of 403 when entry belongs to another user to prevent entry ID enumeration
- **Input Validation**: ID parameter should be validated as integer/numeric
- **Mass Assignment Protection**: Ensure $fillable or $guarded is properly configured on JournalEntry model

## 7. Error Handling
- **Entry Not Found**: Return 404 with error message "Journal entry not found"
- **Unauthenticated Access**: Return 401 with error message "Unauthenticated" (handled by auth middleware)
- **Authorization Failure**: Return 404 (not 403) to prevent information leakage about entry existence
- **Invalid ID Format**: Return 400 with error message "Invalid entry ID format" if ID is not numeric
- **Database Connection Errors**: Return 500 with generic error message, log detailed error server-side

## 9. Implementation Steps

### Step 1: Create JournalEntry Model (if not exists)
- Check if JournalEntry model exists; if not, create it
- Generate model using `php artisan make:model JournalEntry`
- Configure table name as `journal_entries` (follows Laravel convention, should be automatic)
- Define fillable fields: `title`, `content`, `entry_date`, `user_id`
- Set up timestamps to true (enabled by default)
- Configure casts:
    - `entry_date` as `date` cast
    - `user_id` as `integer` cast
- Implement global scope to automatically filter by authenticated user's ID:
    - Create scope that adds `where('user_id', auth()->id())` to all queries
    - Apply scope in model's `boot` method or use `#[ScopedBy]` attribute
- Define relationship: `belongsTo(User::class)` method for user relationship

### Step 2: Create JournalEntryController
- Generate controller using `php artisan make:controller Api/JournalEntryController --api`
- Inject dependencies via constructor if needed (User model likely not needed due to auth helper)
- Implement `show` method with parameter type hinting: `show(string $id)`

### Step 3: Implement Show Method Logic
- Validate that $id is numeric using validation or type casting
- Query JournalEntry model with filtering:
    - Use `findOrFail($id)` for automatic 404 handling
    - Global scope should automatically filter by authenticated user
    - Alternatively, use explicit query: `JournalEntry::where('id', $id)->where('user_id', auth()->id())->firstOrFail()`
- Catch ModelNotFoundException if using findOrFail without try-catch
- Return JSON response with entry data and 200 status code
- Use resource transformation if API responses need consistent formatting

### Step 4: Define Route
- Open `routes/api.php` file
- Add route within authenticated middleware group
- Define route: `Route::get('/journal-entries/{id}', [JournalEntryController::class, 'show'])`
- Apply middleware: `auth:sanctum` or `auth:web` depending on authentication method
- Consider route model binding if not using custom query logic

### Step 5: Create API Resource
- Generate resource using `php artisan make:resource JournalEntryResource`
- Define resource transformation in `toArray` method
- Include fields: id, title, content, entry_date, created_at, updated_at
- Exclude user_id from response for cleaner API (already known via authentication)
- Use resource in controller: `return new JournalEntryResource($entry)`

### Step 6: Implement Authorization Policy
- Add Sanctum middleware to api routes if using token authentication
- Ensure auth middleware is properly configured for API routes

### Step 7: Add Request Validation
- Create form request using `php artisan make:request ShowJournalEntryRequest`
- In `rules` method, return validation rules:
    - `id` parameter should be validated as `required|integer|min:1`
- In `authorize` method, return true (authorization handled by policy or global scope)
- Type-hint request in controller method: `show(ShowJournalEntryRequest $request, string $id)`

### Step 8: Configure Error Handling
- In `app/Exceptions/Handler.php`, customize exception rendering for API routes
- Ensure ModelNotFoundException returns JSON response with 404 status
- Ensure AuthenticationException returns JSON response with 401 status
- Add custom exception handling for authorization failures if needed
- Use `render` method to format error responses consistently
