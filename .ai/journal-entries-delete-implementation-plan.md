# API Endpoint Implementation Plan: Delete Journal Entry

## 1. Endpoint Overview
This endpoint allows authenticated users to permanently delete their own journal entries. Upon successful deletion, the entry is removed from the database and the user's entry count is decremented. This operation is irreversible (no soft deletes in MVP scope).

## 2. Request Details
- **HTTP Method:** DELETE
- **URL Structure:** `/api/journal-entries/{id}`
- **Parameters:**
  - **Path Parameters:**
    - `id` (required, integer): The unique identifier of the journal entry to delete
  - **Required:** None (beyond the path parameter)
  - **Optional:** None
- **Request Body:** None
- **Authentication:** Required (Laravel Sanctum token or session-based)

## 3. Used Types

### Models
- **JournalEntry** (`app/Models/JournalEntry.php`)
  - Primary model for journal entries
  - Must have relationship to User model
  - Must have observer attached for entry count management

- **EntriesCount** (`app/Models/EntriesCount.php`)
  - Tracks entry count per user
  - Updated automatically via JournalEntry observer

- **User** (`app/Models/User.php`)
  - Laravel's default user model
  - Has relationship to JournalEntry

### Policies
- **JournalEntryPolicy** (`app/Policies/JournalEntryPolicy.php`)
  - Implements `delete` method for authorization
  - Ensures users can only delete their own entries

### Observers
- **JournalEntryObserver** (`app/Observers/JournalEntryObserver.php`)
  - Handles `deleted` event
  - Decrements entries_count when entry is deleted

## 4. Response Details

### Success Response (200 OK)
```json
{
  "message": "Journal entry deleted successfully"
}
```

### Error Responses

**404 Not Found** - Entry does not exist or user doesn't own it
```json
{
  "message": "Journal entry not found"
}
```

**403 Forbidden** - User not authorized to delete this entry
```json
{
  "message": "This action is unauthorized."
}
```

**401 Unauthorized** - User not authenticated
```json
{
  "message": "Unauthenticated."
}
```

**500 Internal Server Error** - Server-side error during deletion
```json
{
  "message": "An error occurred while deleting the entry",
  "error": "Error details (only in debug mode)"
}
```

## 5. Data Flow

1. **Request Reception:**
   - API receives DELETE request with journal entry ID in URL path
   - Laravel authentication middleware verifies user is logged in

2. **Entry Retrieval:**
   - Controller retrieves journal entry by ID scoped to authenticated user
   - If entry not found or doesn't belong to user, return 404

3. **Authorization Check:**
   - Laravel policy checks if user is authorized to delete this entry
   - Policy verifies user_id matches authenticated user's ID
   - If unauthorized, return 403

4. **Deletion Process:**
   - JournalEntry model's delete method is called
   - Database transaction begins (implicit in Eloquent)
   - Entry is hard-deleted from journal_entries table
   - JournalEntryObserver's deleted event fires automatically
   - Observer decrements count in entries_count table for the user
   - Transaction commits

5. **Response:**
   - Return success message with 200 status code
   - Frontend receives confirmation and updates UI accordingly

## 6. Security Considerations

### Authentication
- Endpoint must be protected by authentication middleware (`auth:sanctum` or `auth` middleware)
- Only authenticated users can access this endpoint

### Authorization
- Implement JournalEntryPolicy with `delete` method
- Policy must verify `$entry->user_id === $user->id`
- Use `$this->authorize('delete', $entry)` in controller or policy middleware

### Data Isolation
- Always scope queries by authenticated user ID
- Use `auth()->user()->journalEntries()->find($id)` pattern
- Never allow deletion of other users' entries

### Input Validation
- Validate ID parameter is a positive integer
- Handle non-existent IDs gracefully with 404 response

### Database Integrity
- Rely on foreign key CASCADE delete for related records if needed
- Ensure observer transactions complete before returning success

## 7. Error Handling

### Potential Errors and Handling

1. **Entry Not Found (404)**
   - Cause: Invalid ID or entry belongs to different user
   - Handling: Return 404 with appropriate message
   - Prevention: Query scoped to authenticated user

2. **Unauthorized Access (403)**
   - Cause: User attempting to delete another user's entry
   - Handling: Policy automatically returns 403
   - Prevention: Proper authorization checks

3. **Database Error (500)**
   - Cause: Database connection failure or constraint violation
   - Handling: Wrap deletion in try-catch, log error, return generic 500 message
   - Prevention: Database transaction rollback on failure

4. **Observer Failure (500)**
   - Cause: EntriesCount update fails
   - Handling: Transaction rollback, log error, return 500
   - Prevention: Ensure observer implements ShouldHandleEventsAfterCommit

5. **Concurrent Deletion (404)**
   - Cause: Entry already deleted by another request
   - Handling: Return 404 as entry no longer exists
   - Prevention: Database-level transaction isolation

## 9. Implementation Steps

### Step 1: Create or Verify JournalEntry Model
- Generate model if not exists using `php artisan make:model JournalEntry`
- Define table name as `journal_entries`
- Set `$fillable` array with: `user_id`, `title`, `content`, `entry_date`
- Define `belongsTo` relationship to User model
- Add casts for `entry_date` as `date` type
- Enable timestamps (created_at, updated_at)

### Step 2: Create or Verify EntriesCount Model
- Generate model if not exists using `php artisan make:model EntriesCount`
- Define table name as `entries_count`
- Set primary key as `user_id`
- Set `$fillable` array with: `user_id`, `count`
- Disable timestamps (no created_at/updated_at in schema)
- Define `belongsTo` relationship to User model
- Set default value for `count` as 0

### Step 3: Create JournalEntryObserver or Verify JournalEntryObserver
- Generate observer using `php artisan make:observer JournalEntryObserver --model=JournalEntry`
- Implement `ShouldHandleEventsAfterCommit` interface for transaction safety
- Implement `deleted` method that:
  - Retrieves or creates EntriesCount record for the user
  - Decrements the count by 1
  - Ensures count never goes below 0
  - Saves the updated count
- Register observer in `App\Providers\AppServiceProvider` boot method

### Step 4: Create JournalEntryPolicy or Verify JournalEntryPolicy
- Generate policy using `php artisan make:policy JournalEntryPolicy --model=JournalEntry`
- Implement `delete` method that:
  - Accepts User and JournalEntry parameters
  - Returns boolean: `$user->id === $journalEntry->user_id`
  - Ensures only entry owner can delete
- Register policy in `App\Providers\AuthServiceProvider` if not auto-discovered

### Step 5: Create API Controller or Verify JournalEntryController
- Generate controller using `php artisan make:controller Api/JournalEntryController`
- Place in `app/Http/Controllers/Api/` directory
- Add `destroy` method with signature: `public function destroy(string $id): JsonResponse`
- Inject authentication via middleware, not constructor

### Step 6: Implement Controller Destroy Method
- Type-hint return type as `JsonResponse`
- Retrieve authenticated user using `auth()->user()`
- Query for journal entry scoped to user: `auth()->user()->journalEntries()->find($id)`
- If entry not found, return 404 JSON response with error message
- Call authorization check: `$this->authorize('delete', $entry)`
- Wrap deletion in try-catch block for error handling
- Call `$entry->delete()` to trigger hard delete and observer
- Return success JSON response with 200 status code and success message
- In catch block, log exception and return 500 JSON response

### Step 7: Define API Route
- Open `routes/api.php` file
- Add route within authenticated middleware group
- Use Route::delete method with pattern: `Route::delete('/journal-entries/{id}', [JournalEntryController::class, 'destroy'])`
- Ensure route is protected by `auth:sanctum` middleware
- Consider using resource route if implementing full CRUD: `Route::resource('journal-entries', JournalEntryController::class)`

### Step 8: Add Validation Layer (Optional but Recommended)
- Create form request using `php artisan make:request DeleteJournalEntryRequest`
- Implement `authorize` method that returns true (policy handles authorization)
- Implement `rules` method that validates ID format if needed
- Type-hint request in controller method if validation added

### Step 9: Configure User Model Relationship or Verify
- Open `app/Models/User.php`
- Add `hasMany` relationship method for journalEntries
- Ensure relationship method returns correct relationship type
- Add `hasOne` relationship for entriesCount if needed for eager loading
