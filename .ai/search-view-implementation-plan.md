# View Implementation Plan: Search View

## 1. Overview

The Search View is a full-page interface that enables users to perform AI-powered natural language searches across their journal entries.
It provides instant access to relevant entries without manual calendar navigation, solving the core user problem of finding historical gardening information efficiently.
The view is implemented as a dedicated Livewire page accessible via the sidebar navigation, displaying search input, results, and the shared application layout with sidebar.

## 2. View Routing

The Search View is a standalone page accessible at the `/search` route for authenticated and verified users only.
It is accessed by clicking the "Search" link in the sidebar navigation.

**Route**: `GET /search`
**Route Name**: `search`
**Authentication**: Required (authenticated and verified users only)
**Component Location**: `resources/views/livewire/search.blade.php`
**Component Class**: `app/Livewire/Search.php`
**API Endpoint**: `POST /api/search` (defined in `routes/api.php`)

## 3. Component Structure

```
Search View (Livewire Full Page Component)
├── Authenticated Layout (resources/views/components/layouts/app.blade.php)
│   ├── Sidebar Component (Shared with Calendar View)
│   │   ├── Application Logo/Name
│   │   ├── Navigation Links
│   │   │   ├── Gardening Journal (link to /calendar)
│   │   │   └── Search (active, link to /search)
│   │   └── User Menu
│   │       ├── User Email Display
│   │       └── Logout Button
│   └── Main Content Area (Scrollable)
│       ├── Search Heading ("Search Your Journal")
│       ├── SearchInput (Form Component)
│       │   ├── Helper Text
│       │   ├── Text Input Field
│       │   └── Submit Button
│       ├── SearchEmptyState (Conditional - before first search)
│       │   ├── Message Text
│       ├── SearchLoadingState (Conditional - during search)
│       │   └── Loading Spinner with Message
│       ├── SearchResults (Conditional - after successful search)
│       │   ├── Results Header (AI Summary)
│       │   └── SearchResultsList
│       │       └── SearchResultItem (Multiple. clickable)
│       │           ├── Entry Title 
│       │           └── Entry Date
│       ├── SearchNoResults (Conditional - no results found)
│       │   ├── No Results Message
│       │   └── Clear Search Button
│       └── SearchError (Conditional - on error)
│           ├── Error Message
└── Entry Modal (Overlay, opens when clicking entry from results)
```

## 4. Component Details

### Search (Main Livewire Page Component)

- **Component Description**: The primary Livewire full-page component that manages the entire search view lifecycle, including state management, API communication, and user interactions. This component uses the authenticated layout with sidebar and displays the search interface in the main content area.

- **Main Elements**:
  - Page heading ("Search Your Journal")
  - Search input form with text field and submit button
  - Conditional rendering sections for empty state, loading, results, no results, and error states
  - Shared sidebar navigation (from authenticated layout)
  - Integration with Entry Modal for viewing full entry details

- **Handled Interactions**:
  - `submitSearch()`: Processes search query submission
  - `clearSearch()`: Clears search query and resets to empty state
  - `retrySearch()`: Re-attempts failed search with the same query
  - `openEntry($entryId)`: Opens Entry Modal for selected result

- **Handled Validation**:
  - Query must be at least 3 characters (as per ui-plan.md)
  - Query maximum length of 200 characters (as per ui-plan.md)
  - Trim whitespace from query before submission
  - Validate user authentication before allowing search (via route middleware)
  - Rate limiting validation (handled server-side but should display appropriate error)

- **Props**: None (root Livewire page component)

- **State Properties**:
  - `$query` (string): Current search query text
  - `$isLoading` (boolean): Indicates active search processing
  - `$hasSearched` (boolean): Tracks if a search has been performed
  - `$searchResults` (array): Contains search results data
  - `$resultsSummary` (string): AI-generated summary of results
  - `$resultsCount` (int): Number of results found
  - `$errorMessage` (string|null): Error message if search fails
  - `$hasError` (boolean): Indicates error state

### SearchInput (Part of Search View)

- **Component Description**: Form input section within the search view that allows users to enter natural language search queries. Includes helper text, text input field, and submit button.

- **Main Elements**:
  - Page heading: "Search Your Journal"
  - Helper text: "Enter at least 3 characters to search"
  - Flux input field component with placeholder: "Ask AI to find..."
  - Flux button component for submission (disabled until 3+ characters entered)
  - Form wrapper with Livewire wire:submit.prevent

- **Handled Interactions**:
  - `wire:model.defer="query"`: Binds input value to component state
  - `wire:submit.prevent="submitSearch"`: Handles form submission
  - Input field auto-focused on page load (using Alpine.js x-init)

- **Handled Validation**:
  - Minimum 3 characters required
  - Maximum length enforcement (200 characters)
  - Trim whitespace before validation
  - Submit button disabled when query < 3 characters
  - Display validation error messages below input field

- **Props**: None (uses parent component state)

### SearchLoadingState (Conditional Component)

- **Component Description**: Visual feedback component displayed while the AI processes the search query. Provides user assurance that the request is being handled.

- **Main Elements**:
  - Flux spinner component
  - Loading message text: "Searching your journal entries..."
  - Centered container with appropriate spacing

- **Handled Interactions**: None (purely presentational)

- **Handled Validation**: None

- **Props**: None

- **Display Condition**: Shown when `$isLoading === true`

### SearchResults (Conditional Component)

- **Component Description**: Container component that displays the search results, including an AI-generated summary and a list of matching journal entries.

- **Main Elements**:
  - SearchSummary section
  - SearchResultsList with individual SearchResultItem components
  - Empty state message when no results found
  - "New Search" button to clear and start over

- **Handled Interactions**:
  - Click "New Search" button to reset search state

- **Handled Validation**: None

- **Props**: None (uses parent component state)

- **Display Condition**: Shown when `$hasSearched === true && $hasError === false && $isLoading === false`

### SearchSummary (Part of SearchResults)

- **Component Description**: Displays the AI-generated summary or count-based message about the search results.

- **Main Elements**:
  - Flux card or alert component
  - Summary text (from `$resultsSummary`)
  - Distinct styling to separate from results list

- **Handled Interactions**: None

- **Handled Validation**: None

- **Props**: None (uses parent `$resultsSummary`)

### SearchResultsList (Part of SearchResults)

- **Component Description**: Scrollable list container that holds individual search result items.

- **Main Elements**:
  - Scrollable container (max-height with overflow)
  - Collection of SearchResultItem components
  - Empty state message if `$resultsCount === 0`

- **Handled Interactions**:
  - Scroll behavior for long result lists

- **Handled Validation**: None

- **Props**: None (iterates over parent `$searchResults`)

### SearchResultItem (Individual Result)

- **Component Description**: Card component representing a single journal entry result. Displays the entry's key information in a scannable format.

- **Main Elements**:
  - Flux card component
  - Entry title (text-lg, font-semibold)
  - Entry date (formatted as "Month DD, YYYY")
  - Entry content preview (truncated, with "Read more" indicator if needed)
  - Optional link to view full entry in calendar context

- **Handled Interactions**:
  - Click to expand/collapse full content within modal (optional enhancement)
  - Click "View in Calendar" link to navigate to entry's date

- **Handled Validation**: None

- **Props**:
  - `$entry` (JournalEntry object): The journal entry data
    - `id` (int)
    - `title` (string)
    - `date` (Carbon date)
    - `content` (string)

### SearchError (Conditional Component)

- **Component Description**: Error state component that displays when the search fails, providing clear feedback and recovery options.

- **Main Elements**:
  - Flux alert component (error variant)
  - Error icon
  - Error message text
  - "Try Again" button
  - "Start New Search" button

- **Handled Interactions**:
  - Click "Try Again" to retry the same search
  - Click "Start New Search" to clear query and start over

- **Handled Validation**: None

- **Props**: None (uses parent `$errorMessage`)

- **Display Condition**: Shown when `$hasError === true && $isLoading === false`

## 5. Types

### JournalEntry (Eloquent Model)

Used to represent journal entry data returned in search results.

```php
// App\Models\JournalEntry
{
    id: int                    // Primary key
    user_id: int              // Foreign key to users table
    title: string             // Entry title (required, max 255 chars)
    content: string           // Entry content (required, text field)
    date: Carbon              // Entry date (date only, no time)
    created_at: Carbon        // Timestamp
    updated_at: Carbon        // Timestamp
}
```

### SearchRequest (Form Request)

Validation rules for the search API endpoint.

```php
// App\Http\Requests\Api\SearchRequest
{
    query: string             // Required, min:1, max:500
}
```

### JournalEntryResource (API Resource)

Formatted response structure for journal entries returned by the API.

```php
// App\Http\Resources\JournalEntryResource
{
    id: int
    title: string
    content: string
    date: string              // Formatted as Y-m-d
    formatted_date: string    // Formatted as "F j, Y" (e.g., "October 13, 2025")
}
```

### SearchResponse (API Response Structure)

The complete response structure from the search API endpoint.

```php
// API Response Type (JSON)
{
    success: bool
    data: {
        summary: string           // AI-generated summary
        entries: array<JournalEntryResource>
        results_count: int
    }
} | {
    success: bool
    message: string              // Error message
}
```

### Component State Types (Livewire Properties)

```php
// Search Component Properties
{
    query: string             // Current search query
    isLoading: bool           // Loading indicator state
    hasSearched: bool         // Whether a search has been performed
    searchResults: array      // Array of JournalEntryResource data
    resultsSummary: string    // AI-generated summary text
    resultsCount: int         // Count of results found
    errorMessage: ?string     // Error message if present
    hasError: bool            // Error state indicator
}
```

## 6. State Management

The Search View uses **Livewire component state management** exclusively, without requiring custom hooks or external state management solutions. All state is contained within the `SearchModal` Livewire component and follows Livewire v3 conventions.

### State Properties

**Modal State:**
- `$isOpen`: Controls modal visibility using Alpine.js integration with Livewire

**Search Input State:**
- `$query`: Bound to the search input field using `wire:model.defer`
- Updates on form submission rather than real-time to optimize performance

**Loading State:**
- `$isLoading`: Set to `true` when search is submitted, `false` when response received
- Triggers display of loading spinner and disables form submission

**Results State:**
- `$hasSearched`: Boolean flag indicating if any search has been performed in current modal session
- `$searchResults`: Array of journal entry data from API response
- `$resultsSummary`: String containing AI-generated summary or fallback message
- `$resultsCount`: Integer count of results for conditional rendering

**Error State:**
- `$hasError`: Boolean indicating if the last search failed
- `$errorMessage`: String containing error message from API or generic fallback

### State Transitions

**Initial State (Modal Closed):**
```php
$isOpen = false
$query = ''
$isLoading = false
$hasSearched = false
$searchResults = []
$resultsSummary = ''
$resultsCount = 0
$hasError = false
$errorMessage = null
```

**Modal Opened:**
```php
$isOpen = true
// All other properties remain in initial state
```

**Search Submitted:**
```php
$isLoading = true
$hasError = false
$errorMessage = null
// Keep previous results visible until new results arrive (optional UX choice)
```

**Search Success:**
```php
$isLoading = false
$hasSearched = true
$searchResults = [/* API response data */]
$resultsSummary = '/* AI summary */'
$resultsCount = count($searchResults)
$hasError = false
```

**Search Error:**
```php
$isLoading = false
$hasSearched = true
$hasError = true
$errorMessage = '/* Error message */'
$searchResults = []
$resultsCount = 0
```

**New Search Started:**
```php
$query = ''
$hasSearched = false
$searchResults = []
$resultsSummary = ''
$resultsCount = 0
$hasError = false
$errorMessage = null
// Keep $isOpen = true to remain in modal
```

### State Management Methods

**`openModal()`**
- Sets `$isOpen = true`
- No other state changes (preserves previous search if any)

**`closeModal()`**
- Sets `$isOpen = false`
- Resets all search-related state to initial values

**`submitSearch()`**
- Validates `$query` is not empty after trimming
- Sets `$isLoading = true`
- Makes HTTP request to `/api/search` endpoint
- Handles response in `handleSearchResponse()` or `handleSearchError()`

**`handleSearchResponse($response)`**
- Sets `$isLoading = false`
- Sets `$hasSearched = true`
- Populates `$searchResults`, `$resultsSummary`, `$resultsCount`
- Ensures `$hasError = false`

**`handleSearchError($error)`**
- Sets `$isLoading = false`
- Sets `$hasSearched = true`
- Sets `$hasError = true`
- Sets `$errorMessage` with user-friendly message

**`retrySearch()`**
- Calls `submitSearch()` with existing `$query` value

**`startNewSearch()`**
- Resets search state but keeps modal open
- Clears `$query`, `$searchResults`, etc.
- Sets `$hasSearched = false`

### Livewire Lifecycle

- **`mount()`**: Initialize component with default state values
- **`render()`**: Return view with current state
- No computed properties needed (all state is explicit)
- Use `#[Validate]` attribute on `$query` property for real-time validation

### Alpine.js Integration

Use Alpine.js for modal visibility and focus management:
- `x-data="{ open: @entangle('isOpen') }"` for modal state synchronization
- `x-show="open"` for modal display
- `x-init="$watch('open', value => { if(value) $nextTick(() => $refs.searchInput.focus()) })"` for auto-focus

## 7. API Integration

The Search View integrates with the Laravel API endpoint defined in `app/Http/Controllers/Api/SearchController.php`.

### API Endpoint

**URL**: `/api/search`
**Method**: `POST`
**Authentication**: Required (Laravel Sanctum token via Livewire)
**Rate Limiting**: Apply rate limiting as needed (e.g., 10 requests per minute)

### Request Structure

The Livewire component sends a POST request to the API endpoint with the following payload:

```json
{
  "query": "when did I water the tomatoes?"
}
```

**Request Validation** (handled by `App\Http\Requests\Api\SearchRequest`):
- `query`: required, string, min:1, max:500

### Response Structure

**Success Response** (HTTP 200):
```json
{
  "success": true,
  "data": {
    "summary": "Found 3 entries matching your query: \"when did I water the tomatoes?\"",
    "entries": [
      {
        "id": 123,
        "title": "Watering the Garden",
        "content": "Watered the tomatoes thoroughly this morning...",
        "date": "2025-10-10",
        "formatted_date": "October 10, 2025"
      }
    ],
    "results_count": 3
  }
}
```

**Error Response** (HTTP 500):
```json
{
  "success": false,
  "message": "An error occurred while processing your search. Please try again."
}
```

**Validation Error Response** (HTTP 422):
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "query": ["The query field is required."]
  }
}
```

### Integration Implementation

**In SearchModal Livewire Component:**

```php
public function submitSearch()
{
    // Trim and validate query
    $this->query = trim($this->query);

    $this->validate([
        'query' => 'required|string|min:1|max:500'
    ]);

    // Set loading state
    $this->isLoading = true;
    $this->hasError = false;
    $this->errorMessage = null;

    try {
        // Make API request using Laravel HTTP client
        $response = Http::withToken(auth()->user()->currentAccessToken()->plainTextToken)
            ->timeout(30)
            ->post(route('api.search'), [
                'query' => $this->query
            ]);

        if ($response->successful() && $response->json('success')) {
            $this->handleSearchResponse($response->json('data'));
        } else {
            $this->handleSearchError($response->json('message', 'Search failed'));
        }
    } catch (\Exception $e) {
        $this->handleSearchError('Unable to complete search. Please try again.');
    } finally {
        $this->isLoading = false;
    }
}
```

**Note**: Since this is a Livewire component within a Laravel application, you can alternatively call the controller method directly without making an HTTP request, but using the API endpoint approach maintains separation of concerns and allows for future API usage by other clients.

### Controller Response Mapping

The controller's `search()` method in `SearchController.php` returns:
- `summary`: String summary of results
- `entries`: Collection of `JournalEntryResource` instances
- `results_count`: Integer count

These map directly to the Livewire component's state properties:
- `$resultsSummary = $data['summary']`
- `$searchResults = $data['entries']`
- `$resultsCount = $data['results_count']`

### Analytics Logging

The controller automatically logs search analytics via `logSearchAnalytics()` method. No additional frontend implementation is required. Each search is logged with:
- `user_id`: Current authenticated user
- `query`: The search query text
- `results_count`: Number of results returned

## 8. User Interactions

### Opening the Search Modal

**Trigger**: User clicks the "Ask AI to find..." button in the main navigation bar

**Flow**:
1. User clicks SearchModalTrigger component
2. Livewire event dispatched: `$dispatch('open-search-modal')`
3. SearchModal component listens for event and sets `$isOpen = true`
4. Modal appears with search input focused
5. If previous search results exist, they remain visible (or can be cleared based on UX preference)

**Expected Outcome**:
- Modal slides in or fades in with overlay
- Search input field is focused and ready for input
- Previous search state is either preserved or cleared (implementation decision)

### Entering a Search Query

**Trigger**: User types in the search input field

**Flow**:
1. User types natural language query (e.g., "when did I fertilize the roses?")
2. Input bound to `$query` using `wire:model.defer` (updates on blur or submit)
3. Character count indicator updates if approaching 500 character limit
4. Submit button remains enabled if query length > 0 after trim

**Expected Outcome**:
- Query text captured in component state
- Visual feedback on character limit if applicable
- Submit button state reflects query validity

### Submitting a Search

**Trigger**: User clicks submit button or presses Enter in search input

**Flow**:
1. Form submission prevented with `wire:submit.prevent`
2. `submitSearch()` method called
3. Query validated (trim whitespace, check length)
4. `$isLoading` set to `true`
5. Loading state displayed (spinner + message)
6. API request sent to `/api/search` endpoint
7. Response processed and state updated

**Expected Outcome**:
- Loading spinner appears immediately
- Search input and submit button disabled during processing
- User understands request is being processed

### Viewing Search Results

**Trigger**: API returns successful response with results

**Flow**:
1. `handleSearchResponse()` processes API data
2. `$isLoading` set to `false`
3. `$hasSearched` set to `true`
4. `$searchResults`, `$resultsSummary`, `$resultsCount` populated
5. Results section rendered conditionally
6. Summary displayed at top
7. Individual result items listed below

**Expected Outcome**:
- Loading spinner disappears
- Summary text appears (e.g., "Found 5 entries matching your query...")
- List of result items displayed with title, date, and content preview
- Results are scannable and clearly separated
- Empty state message if no results found

### Interacting with Result Items

**Trigger**: User clicks or hovers over a result item

**Flow**:
1. User reads entry title, date, and content preview
2. Optional: Click to expand full content within modal
3. Optional: Click "View in Calendar" link to navigate to entry's date in calendar view
4. Modal can remain open for reference or close on navigation

**Expected Outcome**:
- Result items are interactive and provide visual feedback on hover
- Full entry content accessible without leaving modal (optional)
- Navigation to calendar view preserves context

### Starting a New Search

**Trigger**: User clicks "New Search" button after viewing results

**Flow**:
1. User clicks "New Search" button in results section
2. `startNewSearch()` method called
3. Search state reset: `$query = ''`, `$hasSearched = false`, `$searchResults = []`
4. Modal remains open
5. Input field focused and cleared

**Expected Outcome**:
- Previous results cleared
- Input field empty and focused
- User can immediately type new query

### Handling Search Errors

**Trigger**: API returns error response or request fails

**Flow**:
1. `handleSearchError()` processes error
2. `$isLoading` set to `false`
3. `$hasError` set to `true`
4. `$errorMessage` populated with user-friendly message
5. Error component rendered conditionally
6. "Try Again" and "New Search" buttons displayed

**Expected Outcome**:
- Error message displayed clearly
- User understands something went wrong
- Options to retry or start over are obvious
- Modal remains open for user action

### Retrying a Failed Search

**Trigger**: User clicks "Try Again" button in error state

**Flow**:
1. User clicks "Try Again" button
2. `retrySearch()` method called
3. Same query re-submitted via `submitSearch()`
4. Loading state displayed again
5. Process repeats from search submission

**Expected Outcome**:
- Same query attempted again without re-typing
- Loading state indicates retry in progress
- User sees success or error result

### Closing the Modal

**Trigger**: User clicks close button, clicks outside modal, or presses Escape key

**Flow**:
1. Close action triggered (button click, backdrop click, or Esc key)
2. `closeModal()` method called
3. `$isOpen` set to `false`
4. All search state reset to initial values
5. Modal dismissed with animation

**Expected Outcome**:
- Modal disappears smoothly
- Search state cleared for next session
- User returns to previous view without disruption

### Multiple Searches in One Session

**Trigger**: User performs multiple searches without closing modal

**Flow**:
1. User completes first search and views results
2. User clicks "New Search" button
3. State reset but modal remains open
4. User enters second query and submits
5. Process repeats with new results replacing old

**Expected Outcome**:
- Each search is independent
- Analytics logged for each query
- Smooth transition between searches

## 9. Conditions and Validation

### Input Field Validation

**Component**: SearchInput (within SearchModal)

**Conditions**:
1. **Query Required**: Query cannot be empty or only whitespace
   - **Validation**: `trim($query)` must have length > 0
   - **Effect**: Submit button disabled when condition not met
   - **Message**: "Please enter a search query" (displayed if user attempts to submit)

2. **Query Maximum Length**: Query cannot exceed 500 characters
   - **Validation**: `strlen($query) <= 500`
   - **Effect**: Input field enforces max length attribute, character counter displayed
   - **Message**: "Query must be 500 characters or less" (displayed if exceeded)

3. **Query Format**: Query must be a string (no special validation for natural language)
   - **Validation**: String type check performed by Livewire
   - **Effect**: N/A (string input field)
   - **Message**: N/A

**Implementation**:
- Client-side: HTML5 `required` and `maxlength` attributes on input field
- Client-side: Livewire `#[Validate]` attribute on `$query` property
- Client-side: Submit button disabled state bound to query length
- Server-side: `SearchRequest` form request validates on API endpoint

### Authentication Validation

**Component**: SearchModal (root component)

**Conditions**:
1. **User Must Be Authenticated**: Only logged-in users can perform searches
   - **Validation**: `auth()->check()` in Livewire component
   - **Effect**: Component only rendered for authenticated users
   - **Message**: Redirect to login if accessed without authentication

**Implementation**:
- Laravel middleware protects routes
- Livewire component only accessible within authenticated layouts
- API endpoint requires authentication via Sanctum

### Search Submission Validation

**Component**: SearchModal

**Conditions**:
1. **No Concurrent Searches**: User cannot submit multiple searches simultaneously
   - **Validation**: `$isLoading === false`
   - **Effect**: Submit button disabled when `$isLoading === true`
   - **Message**: Loading spinner displayed with "Searching..." message

2. **Rate Limiting**: User cannot exceed search rate limits (server-side)
   - **Validation**: Laravel rate limiting middleware on API endpoint
   - **Effect**: API returns 429 Too Many Requests if limit exceeded
   - **Message**: "Too many search requests. Please wait a moment and try again."

**Implementation**:
- Client-side: Disable form submission during `$isLoading` state
- Server-side: Apply rate limiting middleware to `/api/search` endpoint
- Error handling: Catch 429 response and display user-friendly message

### Results Display Validation

**Component**: SearchResults

**Conditions**:
1. **Search Must Be Completed**: Results only shown after successful search
   - **Validation**: `$hasSearched === true && $hasError === false && $isLoading === false`
   - **Effect**: Results section conditionally rendered
   - **Message**: N/A

2. **Results Must Exist**: Handle empty results gracefully
   - **Validation**: `$resultsCount === 0`
   - **Effect**: Display empty state message instead of empty list
   - **Message**: "No entries found matching your query: \"{query}\""

**Implementation**:
- Conditional rendering using `@if` Blade directives
- Empty state message displayed when no results
- Summary always shown regardless of result count

### Error State Validation

**Component**: SearchError

**Conditions**:
1. **Error Occurred**: Error component only shown when search fails
   - **Validation**: `$hasError === true && $isLoading === false`
   - **Effect**: Error section conditionally rendered, results hidden
   - **Message**: Content of `$errorMessage` displayed

2. **Error Message Present**: Error message must exist to display
   - **Validation**: `$errorMessage !== null && $errorMessage !== ''`
   - **Effect**: Display specific error or generic fallback
   - **Message**: `$errorMessage` or "An error occurred. Please try again."

**Implementation**:
- Conditional rendering with `@if($hasError)` in Blade
- Fallback error message if `$errorMessage` is empty
- Clear visual distinction from results state

### Modal State Validation

**Component**: SearchModal

**Conditions**:
1. **Modal Visibility**: Modal only visible when explicitly opened
   - **Validation**: `$isOpen === true`
   - **Effect**: Modal rendered and displayed
   - **Message**: N/A

2. **State Isolation**: Each modal session independent
   - **Validation**: State reset on `closeModal()`
   - **Effect**: Fresh state when reopening modal
   - **Message**: N/A

**Implementation**:
- Alpine.js `x-show` directive bound to `$isOpen`
- State reset in `closeModal()` method
- Backdrop click and Esc key trigger close

### Data Privacy Validation

**Component**: SearchModal + SearchController (API)

**Conditions**:
1. **User Data Isolation**: Users can only search their own entries
   - **Validation**: API filters entries by `auth()->user()->id`
   - **Effect**: Only authenticated user's entries returned
   - **Message**: N/A (transparent to user)

2. **No Training Data**: User queries not sent for AI model training
   - **Validation**: Documented API behavior (OpenRouter.ai configuration)
   - **Effect**: Privacy guaranteed at API level
   - **Message**: Privacy notice in application footer/documentation

**Implementation**:
- API scopes query to current user's entries (see SearchController:35)
- OpenRouter.ai integration configured to prevent data usage for training
- Privacy policy documentation updated

## 10. Error Handling

### API Request Failures

**Scenario**: Network error, timeout, or API unavailable

**Detection**:
- HTTP request throws exception
- Response status code indicates failure (500, 503)
- Request timeout (>30 seconds)

**Handling**:
1. Catch exception in `submitSearch()` method
2. Set `$isLoading = false`
3. Set `$hasError = true`
4. Set `$errorMessage = "Unable to complete search. Please check your connection and try again."`
5. Display error component with retry option

**User Experience**:
- Clear error message without technical jargon
- "Try Again" button to retry with same query
- "New Search" button to start over
- Modal remains open for user action

### API Validation Errors (422)

**Scenario**: Query fails server-side validation

**Detection**:
- Response status code 422
- Response contains `errors` object with field-specific messages

**Handling**:
1. Extract validation errors from response
2. Display field-specific error below input field
3. Keep modal open with query intact
4. Allow user to correct and resubmit

**User Experience**:
- Specific error message (e.g., "Query must be between 1 and 500 characters")
- Input field highlighted with error state
- User can immediately correct and retry

### Rate Limiting Errors (429)

**Scenario**: User exceeds search rate limit

**Detection**:
- Response status code 429
- Response may include `Retry-After` header

**Handling**:
1. Set `$errorMessage = "Too many search requests. Please wait a moment and try again."`
2. Optionally display countdown timer if `Retry-After` present
3. Disable "Try Again" button temporarily
4. Re-enable after cooldown period

**User Experience**:
- Clear explanation of rate limiting
- Visual indication of wait time
- Automatic re-enable of retry button

### Authentication Errors (401)

**Scenario**: User session expired or authentication token invalid

**Detection**:
- Response status code 401
- Laravel middleware redirects to login

**Handling**:
1. Redirect user to login page
2. Store search query in session (optional)
3. After re-authentication, optionally restore search context

**User Experience**:
- Seamless redirect to login
- Clear message: "Your session has expired. Please log in again."
- Optionally preserve search intent after login

### Empty Results

**Scenario**: Search returns no matching entries

**Detection**:
- Response successful but `results_count === 0`
- `entries` array is empty

**Handling**:
1. Display summary: "No entries found matching your query: \"{query}\""
2. Suggest user try different keywords
3. Provide "New Search" button
4. Do not treat as error state

**User Experience**:
- Clear indication that search completed successfully but found nothing
- Helpful suggestion to refine query
- Easy way to start new search

### Malformed API Response

**Scenario**: API returns unexpected data structure

**Detection**:
- Response missing expected fields (`summary`, `entries`, `results_count`)
- Data types don't match expectations

**Handling**:
1. Validate response structure before setting state
2. Use fallback values for missing fields
3. Log error for debugging
4. Display generic error to user if response unusable

**User Experience**:
- Generic error message: "Unable to display search results. Please try again."
- Option to retry or start new search

### Client-Side Validation Errors

**Scenario**: User attempts to submit empty or invalid query

**Detection**:
- `$query` is empty after trimming
- `$query` length exceeds 500 characters

**Handling**:
1. Prevent form submission
2. Display inline validation error
3. Focus remains on input field
4. No API request made

**User Experience**:
- Immediate feedback without server round-trip
- Clear error message below input field
- Submit button remains disabled

### Long-Running Searches

**Scenario**: Search takes longer than expected (>10 seconds)

**Detection**:
- Set timer when search initiated
- Check elapsed time periodically

**Handling**:
1. Display additional message: "Still searching... this is taking longer than usual"
2. Continue loading state
3. Allow user to cancel search (optional enhancement)
4. Set maximum timeout (30 seconds)

**User Experience**:
- User reassured that request is still processing
- Option to cancel if taking too long (future enhancement)
- Automatic timeout prevents indefinite waiting

### Browser/JavaScript Errors

**Scenario**: JavaScript error in Livewire component

**Detection**:
- Livewire error event
- Browser console errors

**Handling**:
1. Livewire's built-in error handling displays notification
2. Log error to console for debugging
3. Optionally send error report to logging service
4. Allow user to close modal and retry

**User Experience**:
- Error notification displayed (Livewire default)
- Modal can be closed and reopened
- User can retry operation

### Handling Strategy Summary

| Error Type | Detection | User Message | Recovery Options |
|------------|-----------|--------------|------------------|
| Network failure | Exception/timeout | "Unable to complete search. Please check your connection." | Try Again, New Search |
| Validation error (422) | Status code | Field-specific message | Correct input, Retry |
| Rate limiting (429) | Status code | "Too many requests. Please wait." | Wait, Try Again (after cooldown) |
| Authentication (401) | Status code | "Session expired. Please log in." | Redirect to login |
| Empty results | `results_count === 0` | "No entries found matching your query." | New Search |
| Malformed response | Data validation | "Unable to display results." | Try Again, New Search |
| Client validation | Pre-submit check | Field-specific message | Correct input |
| Long-running search | Timer | "Still searching..." | Wait, Cancel (future) |

## 11. Implementation Steps

### Step 1: Create API Route and Controller (Already Complete)

The SearchController and API endpoint are already implemented. Verify the route exists in `routes/api.php`:

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/search', [SearchController::class, 'search'])->name('api.search');
});
```

### Step 2: Create SearchModal Livewire Component

Generate the Livewire component:

```bash
php artisan livewire:make SearchModal
```

This creates:
- `app/Livewire/SearchModal.php` (component class)
- `resources/views/livewire/search-modal.blade.php` (component view)

### Step 3: Implement SearchModal Component Class

In `app/Livewire/SearchModal.php`, implement:

- Public properties for state management (`$isOpen`, `$query`, `$isLoading`, etc.)
- Validation rules using `#[Validate]` attribute
- `mount()` method to initialize state
- `openModal()` method
- `closeModal()` method
- `submitSearch()` method with API integration
- `handleSearchResponse()` method
- `handleSearchError()` method
- `retrySearch()` method
- `startNewSearch()` method
- Event listeners for modal triggers

### Step 4: Create SearchModal Blade Template

In `resources/views/livewire/search-modal.blade.php`, implement:

- Flux modal component wrapper with Alpine.js directives
- Modal header with title and close button
- Modal body containing:
  - Search input form
  - Loading state (conditional with `@if($isLoading)`)
  - Results display (conditional with `@if($hasSearched && !$hasError && !$isLoading)`)
  - Error display (conditional with `@if($hasError)`)
- Wire directives for data binding and event handling
- Accessibility attributes (ARIA labels, roles)

### Step 5: Create SearchModalTrigger Blade Component

Generate anonymous blade component:

```bash
php artisan make:component SearchModalTrigger --view
```

In `resources/views/components/search-modal-trigger.blade.php`, implement:

- Flux button component
- Search icon
- "Ask AI to find..." text
- Click handler that dispatches Livewire event

### Step 6: Create SearchResultItem Blade Component

Generate blade component:

```bash
php artisan make:component SearchResultItem
```

In `app/View/Components/SearchResultItem.php`:
- Accept `$entry` as constructor parameter
- Pass data to view

In `resources/views/components/search-result-item.blade.php`:
- Flux card component
- Entry title display
- Entry date formatting
- Entry content preview with truncation
- Optional "View in Calendar" link

### Step 7: Integrate SearchModal into Main Layout

In `resources/views/components/layouts/app.blade.php`:

- Add SearchModalTrigger to navigation bar
- Include SearchModal component at bottom of layout (outside main content)
- Ensure component is only rendered for authenticated users

### Step 8: Style Components with Tailwind CSS

Apply Tailwind utility classes to:

- Modal backdrop (overlay with semi-transparent background)
- Modal dialog (centered, responsive width, shadow)
- Search input (proper sizing, focus states)
- Loading spinner (centered, appropriate size)
- Result items (card styling, hover states, spacing)
- Error messages (error color scheme, icon)
- Buttons (primary/secondary styles, disabled states)

### Step 9: Implement Alpine.js Enhancements

Add Alpine.js directives for:

- Modal visibility with smooth transitions
- Auto-focus on search input when modal opens
- Escape key handling to close modal
- Backdrop click to close modal
- Loading state animations

### Step 10: Add Validation and Error Handling

Implement comprehensive error handling:

- Client-side validation with Livewire rules
- Server-side validation via FormRequest
- API error response handling
- Network error handling
- Rate limiting error handling
- Empty results handling
- Display appropriate user-facing error messages

### Step 11: Implement Analytics Logging

Verify analytics logging is working:

- Confirm SearchController logs each search
- Test that user_id, query, and results_count are recorded
- Verify logging doesn't block search response on failure

### Step 12: Add Loading States and Transitions

Enhance user experience with:

- Loading spinner during search
- Smooth transitions between states (loading → results/error)
- Skeleton screens or placeholders (optional)
- Fade-in animations for results

### Step 13: Implement Responsive Design

Ensure mobile compatibility:

- Test modal on mobile screen sizes
- Adjust modal width for different breakpoints
- Ensure touch interactions work properly
- Test keyboard behavior on mobile devices
- Verify scrolling works correctly in modal

### Step 14: Add Accessibility Features

Implement ARIA attributes and keyboard navigation:

- Modal has `role="dialog"` and `aria-modal="true"`
- Focus trap within modal when open
- Escape key closes modal
- Screen reader announcements for state changes
- Proper label associations for form fields

### Step 15: Test Search Functionality

Perform comprehensive testing:

- Test with various natural language queries
- Test with empty query (validation)
- Test with very long query (500 char limit)
- Test with no results
- Test with many results
- Test error scenarios (network failure, timeout)
- Test multiple searches in one session
- Test modal open/close behavior
- Test authentication requirements

### Step 16: Optimize Performance

Implement performance optimizations:

- Use `wire:model.defer` instead of `wire:model` for query input
- Lazy load results if list is very long
- Debounce input if implementing real-time search (future enhancement)
- Cache search results in component session (optional)
- Optimize API response size if needed

### Step 17: Add Polish and Final Touches

Refine user experience:

- Fine-tune transitions and animations
- Ensure consistent spacing and alignment
- Add helpful placeholder text in input
- Include keyboard shortcuts hint (e.g., "Press / to search")
- Add empty state illustration (optional)
- Ensure brand consistency with rest of application

### Step 18: Documentation and Comments

Document the implementation:

- Add PHPDoc comments to component methods
- Document component props and state properties
- Add inline comments for complex logic
- Update project documentation with search feature usage
- Document any configuration requirements (e.g., OpenRouter.ai setup)

### Step 19: Create Feature Tests

Write automated tests:

```bash
php artisan make:test SearchModalTest
```

Test cases:
- Modal opens and closes correctly
- Search submission with valid query
- Search returns results correctly
- Empty query validation
- Error handling for API failures
- Rate limiting behavior
- Results display correctly
- Multiple searches in one session

### Step 20: Deploy and Monitor

Prepare for production:

- Review all error logging
- Verify analytics tracking works
- Test on staging environment
- Monitor initial user behavior
- Gather feedback on search relevance
- Plan iterations based on success metrics (US-014 acceptance criteria)

### Implementation Checklist

- [ ] Step 1: Verify API route exists
- [ ] Step 2: Generate SearchModal Livewire component
- [ ] Step 3: Implement SearchModal component class
- [ ] Step 4: Create SearchModal blade template
- [ ] Step 5: Create SearchModalTrigger component
- [ ] Step 6: Create SearchResultItem component
- [ ] Step 7: Integrate into main layout
- [ ] Step 8: Apply Tailwind styling
- [ ] Step 9: Add Alpine.js enhancements
- [ ] Step 10: Implement validation and error handling
- [ ] Step 11: Verify analytics logging
- [ ] Step 12: Add loading states
- [ ] Step 13: Implement responsive design
- [ ] Step 14: Add accessibility features
- [ ] Step 15: Test search functionality
- [ ] Step 16: Optimize performance
- [ ] Step 17: Add polish and final touches
- [ ] Step 18: Add documentation
- [ ] Step 19: Write automated tests
- [ ] Step 20: Deploy and monitor
