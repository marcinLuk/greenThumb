# View Implementation Plan: Calendar View

## 1. Overview

The Calendar View is the primary interface for displaying and managing journal entries in GreenThumb. 
It presents a weekly calendar layout (Monday through Sunday) that shows the current week by default, 
with navigation controls to browse through past weeks. Each date displays visual indicators for entries, 
allowing users to quickly see their gardening activity patterns. 
The view integrates with the Journal Entry API to fetch entries for the visible week and provides smooth, reactive navigation without full page reloads using Livewire.

## 2. View Routing

**Path:** `/calendar`

**Route Definition:**
```php
Route::get('/calendar', CalendarView::class)
    ->middleware(['auth', 'verified'])
    ->name('calendar');
```

The view should be protected by authentication and email verification middleware, as specified in the PRD requirements.

## 3. Component Structure

```
CalendarView (Livewire Volt Component)
├── CalendarHeader
│   ├── CurrentWeekDisplay
│   └── WeekNavigationButtons
│       ├── PreviousWeekButton (Flux Button)
│       └── NextWeekButton (Flux Button)
├── CalendarGrid
│   ├── DayRow (x7, Monday-Sunday)
│   │   ├── DayHeader
│   │   └── EntryIndicators


```

## 4. Component Details

### CalendarView (Main Livewire Component)

**Component Description:**
The root Livewire Volt component that manages the calendar state, fetches entry data, and orchestrates all child components. It handles week navigation logic, API communication, and entry management operations.

**Main Elements:**
- Container div with full-width responsive layout
- CalendarHeader for week display and navigation
- CalendarGrid for the 7-day week layout
- Loading spinner (Flux Spinner) displayed during data fetching
- Error messages (Flux Alert) for API failures

**Handled Events:**
- `navigateToWeek($direction)` - Changes the displayed week (previous/next)
- `loadWeekEntries($startDate, $endDate)` - Fetches entries for the specified date range
- `openCreateModal($date)` - Triggers entry creation for selected date
- `openEditModal($entryId)` - Triggers entry editing
- `deleteEntry($entryId)` - Handles entry deletion with confirmation

**Handled Validation:**
- Ensures start date is always Monday
- Validates that fetched entries match the requested date range
- Verifies user authentication before any operations
- Ensures entry limit (50 max) hasn't been exceeded before allowing new entries

**State Properties:**
- `$currentWeekStart` (Carbon date) - The Monday of the currently displayed week
- `$entries` (Collection) - Journal entries for the current week
- `$isLoading` (boolean) - Loading state indicator
- `$error` (string|null) - Error message if API call fails

**Props:**
None - this is the root component

### CalendarHeader

**Component Description:**
Displays the current week date range and navigation controls. Provides clear context about which week is being viewed.

**Main Elements:**
- Flex container for horizontal layout
- CurrentWeekDisplay component (center)
- WeekNavigationButtons component (left and right)

**Handled Events:**
None - presentational component

**Handled Validation:**
None

**Props:**
- `weekStart` (Carbon date) - First day of displayed week
- `weekEnd` (Carbon date) - Last day of displayed week

### CurrentWeekDisplay

**Component Description:**
Displays the formatted date range for the current week (e.g., "October 7-13, 2025").

**Main Elements:**
- Heading element (h2 or h3) with formatted date range text
- Styling to emphasize current week

**Handled Events:**
None

**Handled Validation:**
None

**Props:**
- `weekStart` (Carbon date) - First day of displayed week
- `weekEnd` (Carbon date) - Last day of displayed week

### WeekNavigationButtons

**Component Description:**
Contains previous and next week navigation buttons with appropriate icons.

**Main Elements:**
- Container div for button group
- PreviousWeekButton (Flux Button with left arrow icon)
- NextWeekButton (Flux Button with right arrow icon)

**Handled Events:**
- Emits `navigateWeek` event with direction parameter ('previous' or 'next')

**Handled Validation:**
None - navigation is unlimited for browsing past entries

**Props:**
None

### CalendarGrid

**Component Description:**
The main grid layout displaying 7 day columns for the week. Uses responsive design to adapt to different screen sizes.

**Main Elements:**
- Grid container with 7 equal rows
- DayRow components (one for each day of the week)

**Handled Events:**
None - passes events from children to parent

**Handled Validation:**
None

**Props:**
- `weekDays` (array) - Array of 7 Carbon date objects (Monday-Sunday)
- `entries` (Collection) - All entries for the week, grouped by date
- `isLoading` (boolean) - Loading state for showing skeleton states

### DayRow

**Component Description:**
Represents a single day in the calendar, showing the day header and all entries for that date. Includes an "Add Entry" button.

**Main Elements:**
- Row container div
- DayHeader component
- Add entry button (Flux Button, icon-only or small)
- List/stack of EntryCard components
- Empty state when no entries exist for the date

**Handled Events:**
- `createEntry` - Emitted when add button is clicked, passes date
- `editEntry` - Bubbled from EntryCard, passes entry ID
- `deleteEntry` - Bubbled from EntryCard, passes entry ID

**Handled Validation:**
- Checks if user has reached 50-entry limit before showing add button
- Ensures date is today or in the past

**Props:**
- `date` (Carbon date) - The date this column represents
- `entries` (Collection) - Entries for this specific date
- `canAddEntry` (boolean) - Whether user can add more entries (under 50 limit)

### DayHeader

**Component Description:**
Displays the day name and date number at the top of each column.

**Main Elements:**
- Day name (e.g., "Monday", "Mon" on mobile)
- Date number (e.g., "7")
- Optional highlighting if date is today

**Handled Events:**
None

**Handled Validation:**
None

**Props:**
- `date` (Carbon date) - The date to display

## 5. Types

### Frontend Types (TypeScript/PHP Types)

#### JournalEntry
```php
// From API Resource (app/Http/Resources/JournalEntryResource.php)
[
    'id' => int,
    'title' => string,
    'content' => string,
    'entry_date' => string, // Y-m-d format
    'created_at' => string, // ISO 8601 datetime
    'updated_at' => string, // ISO 8601 datetime
]
```

#### DateRangeResponse
```php
// From dateRange() controller method response
[
    'data' => [
        // Array of JournalEntry resources
    ],
    'meta' => [
        'start_date' => string, // Y-m-d format
        'end_date' => string,   // Y-m-d format
        'total_entries' => int
    ]
]
```

#### WeekData
```php
// Internal component type
[
    'start_date' => Carbon,
    'end_date' => Carbon,
    'days' => array, // Array of 7 Carbon dates
]
```

#### EntryCollection
```php
// Grouped entries by date
// Collection keyed by date string (Y-m-d)
[
    '2025-10-07' => Collection of JournalEntry,
    '2025-10-08' => Collection of JournalEntry,
    // ... etc
]
```

## 6. State Management

### Livewire Component State

The CalendarView Livewire component manages all state internally without needing external state management solutions. State is reactive and automatically updates the UI when changed.

**Primary State Variables:**

1. **`$currentWeekStart`** (Carbon date)
   - Purpose: Tracks the Monday of the currently displayed week
   - Initial value: Carbon::now()->startOfWeek(Carbon::MONDAY)
   - Updated by: navigateToWeek() method

2. **`$entries`** (Collection)
   - Purpose: Stores all journal entries for the current week
   - Initial value: Empty collection
   - Updated by: loadWeekEntries() method after API call
   - Format: Collection of entry arrays matching JournalEntry type

3. **`$isLoading`** (boolean)
   - Purpose: Indicates when API requests are in progress
   - Initial value: false
   - Updated by: Before/after API calls
   - Triggers: Loading spinner display

4. **`$error`** (string|null)
   - Purpose: Stores error messages from failed API calls
   - Initial value: null
   - Updated by: Catch blocks in API calls
   - Triggers: Error alert display

**Computed Properties:**

1. **`weekEnd`** (Carbon date)
   - Computed from: $currentWeekStart->copy()->addDays(6)
   - Purpose: Calculate Sunday of current week

2. **`weekDays`** (array of Carbon dates)
   - Computed from: Array of 7 dates starting from $currentWeekStart
   - Purpose: Provide dates for each DayRow

3. **`entriesByDate`** (Collection)
   - Computed from: $entries->groupBy('entry_date')
   - Purpose: Organize entries by date for easy access in DayRow

## 7. Controller Integration

### API Endpoints Used

#### 1. GET /api/journal-entries/date-range

**Purpose:** Fetch all entries within a specific week

**Request Parameters:**
```php
[
    'start_date' => 'Y-m-d', // Required, Monday of the week
    'end_date' => 'Y-m-d',   // Required, Sunday of the week
]
```

**Validation:** Handled by GetEntriesByDateRangeRequest
- Both dates required
- Must be valid date format
- end_date must be after or equal to start_date

**Response (Success - 200):**
```php
[
    'data' => [
        // Array of JournalEntryResource objects
    ],
    'meta' => [
        'start_date' => string,
        'end_date' => string,
        'total_entries' => int
    ]
]
```

**Response (Error - 401):** Unauthenticated
**Response (Error - 422):** Validation failure

**Usage in Component:**
Called in `loadWeekEntries()` method when component mounts and when navigating weeks.


### Integration Pattern

All API calls should be made using Laravel's HTTP facade within Livewire methods. 
Responses are automatically bound to component state, triggering reactive UI updates.

**Error Handling Strategy:**
- Catch all HTTP exceptions
- Display user-friendly error messages in Flux Alert components
- Log actual errors for debugging
- Provide retry mechanisms for failed requests

## 8. User Interactions

### 1. Initial Page Load
**User Action:** User navigates to /calendar
**Expected Outcome:**
- Calendar displays current week (Monday-Sunday)
- Week range shown in header (e.g., "October 7-13, 2025")
- All entries for current week are fetched and displayed
- Loading spinner shows during data fetch
- Empty state shown if no entries exist

### 2. Navigate to Previous Week
**User Action:** User clicks the "Previous Week" button
**Expected Outcome:**
- Calendar updates to show previous week
- Week range updates in header
- New entries are fetched for the previous week
- Loading spinner appears during fetch
- Calendar smoothly transitions without full page reload
- URL can optionally update with query parameter (e.g., ?week=2025-W41)

### 3. Navigate to Next Week
**User Action:** User clicks the "Next Week" button
**Expected Outcome:**
- Calendar updates to show next week
- Week range updates in header
- New entries are fetched for the next week
- Loading spinner appears during fetch
- Calendar smoothly transitions without full page reload

### 8. View on Mobile
**User Action:** User accesses calendar on mobile device
**Expected Outcome:**
- Calendar grid adapts to smaller screen
- Day columns may stack or use horizontal scroll
- Day names abbreviated (e.g., "Mon" instead of "Monday")
- All functionality remains accessible
- Touch interactions work properly
- Navigation buttons remain easily tappable

### 9. Handle API Errors
**User Action:** Network error or API failure occurs
**Expected Outcome:**
- Error message displayed inline
- Previous data remains visible if available
- User can click "Try Again" to retry the request
- Specific error context provided when possible

### 10. View Empty Calendar
**User Action:** New user views calendar with no entries
**Expected Outcome:**
- Calendar is displayed
- No confusing blank spaces or error-like appearance

## 9. Conditions and Validation

### Client-Side Validations

#### 1. Entry Creation Date Validation
**Component:** DayRow
**Condition:** User can only create entries for today or past dates
**Implementation:**
- Client-side validation before API call
- Visual feedback if user attempts to select future date

**Effect on Interface:**
- Future dates have disabled "Add Entry" button
- Validation error shown if validation fails

#### 3. Week Navigation Boundaries
**Component:** CalendarView, WeekNavigationButtons
**Condition:** No restriction on navigating to past weeks
**Implementation:**
- Previous week button always enabled
- Next week button can navigate to future weeks to view later date ranges
- Current week highlighted when viewing it

**Effect on Interface:**
- Navigation buttons always functional
- Visual indicator showing current week vs. other weeks

#### 4. Authentication Requirement
**Component:** CalendarView (Root)
**Condition:** User must be authenticated and verified
**Implementation:**
- Route protected by auth and verified middleware
- Unauthenticated users redirected to login
- Unverified users redirected to verification page

**Effect on Interface:**
- Calendar not accessible without authentication
- Automatic redirect if session expires

## 10. Error Handling

### API Error Scenarios

#### 1. Network Connectivity Failure
**Scenario:** User loses internet connection while loading entries
**Detection:** HTTP client throws exception or timeout
**Handling:**
- Display error alert: "Unable to connect. Please check your internet connection."
- Keep previous data visible if available
- Provide "Retry" button to attempt reload

#### 4. Entry Not Found (404)
**Scenario:** Entry was deleted by another session or doesn't exist
**Detection:** API returns 404
**Handling:**
- Display transient error message
- Remove entry from local state
- Refresh calendar to sync with server
- Message: "This entry no longer exists."

#### 5. Validation Errors (422)
**Scenario:** Invalid data submitted (shouldn't happen with proper client validation)
**Detection:** API returns 422 with validation errors
**Handling:**
- Display validation errors inline
- Highlight invalid fields
- Prevent modal from closing
- Allow user to correct and resubmit

#### 6. Server Error (500)
**Scenario:** Unexpected server error during operation
**Detection:** API returns 500 status
**Handling:**
- Display generic error message: "Something went wrong. Please try again."
- Log full error details for debugging
- Provide retry mechanism
- Preserve user's current view state

### UI Error States

#### 1. Empty Week State
**Scenario:** Selected week has no entries
**Handling:**
- Show empty state message per day: "No entries for this day"
- Keep "Add Entry" button visible and functional
- Provide encouraging message: "Start documenting your garden!"

#### 2. Loading State
**Scenario:** Data is being fetched from API
**Handling:**
- Display Flux Spinner component
- Show skeleton loading states in day columns
- Disable navigation buttons during load
- Prevent multiple simultaneous requests

#### 3. Total Empty State (No Entries Ever)
**Scenario:** New user with zero entries
**Handling:**
- Show calendar

### Edge Cases

#### 1. Date Range Mismatch
**Scenario:** API returns entries outside requested date range
**Detection:** Validate entry dates against $currentWeekStart and $weekEnd
**Handling:**
- Filter entries client-side to only show matching dates
- Log warning about data mismatch
- Continue displaying filtered data

#### 3. Rapid Navigation Clicks
**Scenario:** User rapidly clicks previous/next week buttons
**Detection:** Multiple simultaneous API calls
**Handling:**
- Debounce navigation button clicks
- Cancel in-flight requests when new navigation triggered
- Use loading state to disable buttons during fetch

#### 4. Browser Back/Forward Navigation
**Scenario:** User uses browser navigation buttons
**Handling:**
- Optionally implement URL query parameters (?week=2025-W41)
- Sync component state with URL on mount
- Handle popstate events gracefully

### Error Recovery Mechanisms

1. **Automatic Retry:** For transient errors (network issues), automatically retry after delay
2. **Manual Retry:** Provide explicit "Try Again" button for user-initiated retry
3. **State Persistence:** Preserve user's current week selection during errors
4. **Graceful Degradation:** Show cached/previous data when available during errors
5. **Error Logging:** Comprehensive logging for debugging production issues

## 11. Implementation Steps

### Step 1: Create Database Migration and Model (If Not Exists)
- Ensure `journal_entries` table exists with proper schema
- Verify `JournalEntry` model has necessary relationships and scopes
- Confirm `withinDateRange` and `sortByDate` query scopes exist

### Step 2: Set Up API Routes (If Not Exists)
- Verify routes in `routes/api.php` for journal entry endpoints
- Ensure Sanctum authentication middleware is applied
- Test route accessibility with authenticated requests

### Step 3: Create Livewire Volt Component
- Create at `resources/views/livewire/calendar-view.blade.php`
- Set up component class with state properties: `$currentWeekStart`, `$entries`, `$isLoading`, `$error`
- Implement `mount()` method to initialize current week and load entries
- Create `navigateToWeek()` method for week navigation
- Implement `loadWeekEntries()` method for API integration

### Step 4: Implement API Integration
- Add HTTP calls to `/api/journal-entries/date-range` in `loadWeekEntries()`
- Implement error handling with try-catch blocks
- Set up loading states before/after API calls
- Parse and store response data in `$entries` collection

### Step 5: Create CalendarHeader Component
- Create anonymous component in `resources/views/components/calendar/header.blade.php`
- Accept `weekStart` and `weekEnd` props
- Format date range display (e.g., "October 7-13, 2025")
- Add previous/next week buttons with wire:click directives

### Step 6: Create CalendarGrid Component
- Create 7-rows responsive grid layout using Tailwind
- Use CSS Grid or Flexbox for responsive behavior
- Accept `weekDays` and `entriesByDate` props
- Loop through 7 days and render DayRow for each

### Step 7: Create DayRow Component
- Accept `date`, `entries`, and `canAddEntry` props
- Display day name and date number in header
- Implement "Add Entry" button with wire:click
- Loop through entries and render EntryCard for each
- Show empty state when no entries exist

### Step 11: Implement Loading States
- Add Flux Spinner component to CalendarView
- Create skeleton loading states for DayRow components
- Show/hide based on `$isLoading` property
- Disable navigation buttons during loading

### Step 13: Add Responsive Design
- Test calendar on mobile, tablet, and desktop viewports
- Adjust grid layout for smaller screens (stack or scroll)
- Ensure buttons are touch-friendly (minimum 44px tap targets)
- Abbreviate day names on mobile ("Mon" vs "Monday")
- Test navigation and all interactions on touch devices

### Step 14: Implement Accessibility Features
- Add proper ARIA labels to all interactive elements
- Ensure keyboard navigation works for all buttons
- Add focus indicators for keyboard users
- Use semantic HTML elements (nav, main, article, etc.)
- Test with screen reader for proper announcements
