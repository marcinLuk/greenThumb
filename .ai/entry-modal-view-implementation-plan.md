# View Implementation Plan: Entry Modal

## 1. Overview

The Entry Modal is a reusable Livewire component that handles creating, viewing, editing, and deleting journal entries. 
This modal serves as the primary interface for all journal entry CRUD operations and is triggered from calendar day rows. 
The modal supports three distinct modes: create (for new entries), view (for displaying existing entries), and edit (for modifying existing entries). 
The component integrates with the JournalEntryController API endpoints and enforces validation rules including the 50-entry limit and past/present date restrictions.

## 2. View Routing
The Entry Modal is not a standalone route but a component embedded within the calendar view. It is accessed through:
- Parent route: `/calendar` (where the CalendarView component is rendered)
- Modal trigger: Clicking "Add Entry" button on any calendar day row
- Modal trigger: Clicking on an existing entry in a calendar day row

## 3. Component Structure

```
EntryModal (Livewire Component)
├── Modal Container (flux:modal)
│   ├── Modal Header
│   │   ├── Title (dynamic: "New Entry" / "View Entry" / "Edit Entry")
│   │   └── Close Button
│   ├── Modal Body
│   │   ├── Entry Form (create/edit modes)
│   │   │   ├── Title Input Field (flux:input)
│   │   │   ├── Date Input Field
│   │   │   └── Content Textarea Field (flux:textarea)
│   │   ├── Entry Display (view mode)
│   │   │   ├── Title Display
│   │   │   ├── Date Display
│   │   │   └── Content Display
│   │   └── Error Messages Display
│   └── Modal Footer
│       ├── Action Buttons (conditional based on mode)
│       │   ├── Save Button (create/edit modes)
│       │   ├── Cancel Button (all modes)
│       │   ├── Edit Button (view mode)
│       │   └── Delete Button (view/edit modes)
│       └── Delete Confirmation Modal (nested)
```

## 4. Component Details

### EntryModal (Main Livewire Component)

**Component Description:**
A full-featured modal dialog component that manages all journal entry interactions.
Handles three operational modes (create, view, edit) with appropriate UI adaptations for each mode. 
Manages form state, validation, API communication, and user feedback through toast notifications.

**Main HTML Elements and Child Components:**
- `<flux:modal>` - Main modal container with dynamic name binding
- Form element (conditional on mode: create/edit)
- Display section (conditional on mode: view)
- Input fields: title (text), entry_date (date), content (textarea)
- Action buttons: Save, Cancel, Edit, Delete
- Nested delete confirmation modal
- Error message containers
- Loading state indicators

**Handled Events:**
- `openCreateModal(date)` - Opens modal in create mode for specified date
- `openViewModal(entryId)` - Opens modal in view mode displaying entry details
- `openEditModal(entryId)` - Opens modal in edit mode with populated form
- `save()` - Submits form data to create or update entry
- `delete()` - Triggers delete confirmation modal
- `confirmDelete()` - Executes entry deletion
- `closeModal()` - Closes modal and resets state
- `switchToEditMode()` - Transitions from view mode to edit mode

**Validation Conditions:**
- Title field: Required, string type, maximum 255 characters
- Content field: Required, string type, no maximum length enforced at frontend
- Entry Date field: Required, valid date format (YYYY-MM-DD), must be today or in the past (before_or_equal:today) , disabled and hidden field, auto-populated in create mode
- Frontend validation disables Save button when required fields are empty
- Backend validation messages displayed for: missing required fields, character limit exceeded
- 50-entry limit validated before save attempt, displays error message if limit reached

**Types:**
- `EntryData` - Object containing entry fields (id, title, content, entry_date, created_at, updated_at)
- `ModalMode` - Enum/string ('create' | 'view' | 'edit')
- `ValidationErrors` - Array of error messages keyed by field name

**Props:**
None - Component is self-contained and uses Livewire events for communication with parent CalendarView component

### Entry Form Section

**Component Description:**
Displays editable form fields for creating or editing journal entries. Shows in create and edit modes only. All fields are required and validated before submission.

**Main HTML Elements:**
- `<form wire:submit="save">` - Main form container
- `<flux:input wire:model="title">` - Title input field with validation
- `<flux:input type="date" wire:model="entry_date">` - Date picker with max="today" attribute, hidden and disabled, user cannot edit
- `<flux:textarea wire:model="content">` - Multi-line content input with adequate height

**Handled Validation:**
- Title: Empty check, 255 character limit, displays inline error if validation fails
- Entry Date: No validation needed on frontend since it's auto-populated and disabled, only backend validation
- Content: Empty check, displays inline error if validation fails
- Real-time validation feedback using wire:model.blur for better UX

### Entry Display Section

**Component Description:**
Read-only display of journal entry details shown in view mode. Provides clear presentation of entry information with proper formatting.

**Main HTML Elements:**
- `<div class="entry-display">` - Container for entry details
- `<flux:heading>` - Entry title display
- `<flux:text>` - Entry date display (formatted as readable date)
- `<flux:text>` - Entry content display (preserves line breaks)

**Handled Validation:**
None - Display only component

### Delete Confirmation Modal

**Component Description:**
Nested modal that appears when user attempts to delete an entry. Provides explicit confirmation step to prevent accidental deletions.

**Main HTML Elements:**
- `<flux:modal name="confirm-delete">` - Nested modal container
- Confirmation message text
- Confirm Delete button (danger variant)
- Cancel button

**Handled Events:**
- Confirm button click triggers `confirmDelete()` method
- Cancel button closes confirmation modal only

**Handled Validation:**
None - Confirmation action only

### Action Buttons Section

**Component Description:**
Dynamic button group that changes based on modal mode. Handles all primary actions for entry management.

**Main HTML Elements:**
- `<flux:button type="submit" variant="primary">` - Save button (create/edit modes)
- `<flux:button wire:click="switchToEditMode">` - Edit button (view mode)
- `<flux:button variant="danger" wire:click="delete">` - Delete button (view/edit modes)
- `<flux:modal.close>` - Cancel/Close button (all modes)

**Handled Validation:**
- Save button disabled state when form is invalid (empty required fields)
- Loading state during API calls prevents duplicate submissions
- Delete button only shows for existing entries (not in create mode)

## 5. Types

### EntryData Type
Represents a complete journal entry as returned by the API.

**Fields:**
- `id`: number - Unique identifier for the entry
- `title`: string - Entry title (max 255 characters)
- `content`: string - Full text content of the entry
- `entry_date`: string - Date in YYYY-MM-DD format
- `created_at`: string - ISO 8601 timestamp of creation
- `updated_at`: string - ISO 8601 timestamp of last update

### EntryFormData Type
Represents form data for creating or updating entries (subset of EntryData).

**Fields:**
- `title`: string - Entry title (required, max 255 chars)
- `content`: string - Entry content (required)
- `entry_date`: string - Date in YYYY-MM-DD format (required, must be today or past)

### ValidationError Type
Represents validation errors returned from the API.

**Fields:**
- `message`: string - General error message
- `errors`: object - Field-specific errors keyed by field name
  - `title`: array of string messages
  - `content`: array of string messages
  - `entry_date`: array of string messages

### ModalMode Type
String literal type representing the current operational mode.

**Values:**
- `'create'` - Creating new entry
- `'view'` - Viewing existing entry (read-only)
- `'edit'` - Editing existing entry

## 6. State Management

The Entry Modal uses Livewire's built-in state management with public properties. No custom hooks or additional state management libraries are required.

**Component State Properties:**

- `$modalMode` (string) - Current mode: 'create', 'view', or 'edit'
- `$isOpen` (boolean) - Controls modal visibility
- `$entryId` (integer|null) - ID of current entry (null for create mode)
- `$title` (string) - Form field for entry title
- `$content` (string) - Form field for entry content
- `$entry_date` (string) - Form field for entry date
- `$isLoading` (boolean) - Indicates API call in progress
- `$showDeleteConfirmation` (boolean) - Controls delete confirmation modal visibility
- `$validationErrors` (array) - Stores field-specific validation errors

**State Management Pattern:**

Livewire automatically synchronizes component properties between frontend and backend using wire:model directives. 
Form fields are bound using `wire:model.defer` for better performance (updates sent on form submission rather than every keystroke). 
The component listens for custom events dispatched from the CalendarView parent component to open the modal in various modes.

**State Reset:**

When the modal closes, all form fields and state properties reset to default values to ensure clean state for the next opening. This is handled in the `resetState()` method called during modal close events.

## 7. API Integration

The Entry Modal integrates with the JournalEntryController API endpoints through Livewire's HTTP client wrapper.

**API Endpoints Used:**

### Create Entry
- **Endpoint:** `POST /api/journal-entries`
- **Request Body:** EntryFormData (title, content, entry_date)
- **Success Response:** 201 Created with JournalEntryResource
- **Error Responses:**
  - 403 Forbidden - 50-entry limit reached
  - 422 Unprocessable Entity - Validation errors
  - 500 Internal Server Error - Server failure
- **Frontend Action:** After successful creation, dispatch event to parent to reload calendar data, show success toast, close modal

### Show Entry
- **Endpoint:** `GET /api/journal-entries/{id}`
- **Success Response:** 200 OK with JournalEntryResource
- **Error Responses:**
  - 400 Bad Request - Invalid ID format
  - 404 Not Found - Entry doesn't exist or unauthorized
- **Frontend Action:** Populate modal with entry data for viewing/editing

### Update Entry
- **Endpoint:** `PUT /api/journal-entries/{id}`
- **Request Body:** EntryFormData (title, content, entry_date)
- **Success Response:** 200 OK with updated JournalEntryResource
- **Error Responses:**
  - 400 Bad Request - Invalid ID format
  - 404 Not Found - Entry doesn't exist or unauthorized
  - 422 Unprocessable Entity - Validation errors
  - 500 Internal Server Error - Server failure
- **Frontend Action:** After successful update, dispatch event to parent to reload calendar data, show success toast, close modal

### Delete Entry
- **Endpoint:** `DELETE /api/journal-entries/{id}`
- **Success Response:** 200 OK with success message
- **Error Responses:**
  - 400 Bad Request - Invalid ID format
  - 404 Not Found - Entry doesn't exist or unauthorized
  - 500 Internal Server Error - Server failure
- **Frontend Action:** After successful deletion, dispatch event to parent to reload calendar data, show success toast, close modal

**API Communication Pattern:**

All API calls are made from Livewire component methods using Illuminate\Support\Facades\Http facade. Requests include Sanctum authentication token automatically via middleware. Loading states are set before API calls and cleared in finally blocks. Errors are caught and displayed as toast notifications using Flux::toast().

## 8. User Interactions

### Opening Modal for New Entry

**User Action:** User clicks "Add Entry" button on a calendar day row

**System Response:**
1. Calendar component dispatches `openCreateModal` event with date parameter
2. Entry Modal component receives event and sets mode to 'create'
3. Modal opens with empty form fields
4. Entry date field pre-populated with selected calendar date
5. Title field receives focus automatically
6. Save button is disabled until all required fields are filled

### Viewing Existing Entry

**User Action:** User clicks on an existing entry card in calendar day row

**System Response:**
1. Calendar component dispatches `openViewModal` event with entry ID
2. Entry Modal fetches entry data via GET API call
3. Loading spinner displays during fetch
4. Modal opens in view mode displaying entry details
5. Entry information shown in read-only format
6. Edit and Delete buttons available at bottom
7. User can click Edit button to switch to edit mode

### Editing Existing Entry

**User Action:** User clicks "Edit" button while viewing an entry

**System Response:**
1. Modal transitions from view mode to edit mode
2. Form fields populate with current entry data
3. All fields become editable
4. Save button enabled (fields already valid)
5. User can modify any field
6. Real-time validation feedback on field blur
7. User clicks Save to submit changes

### Saving Entry (Create or Update)

**User Action:** User clicks "Save" button with valid form data

**System Response:**
1. Save button shows loading state and becomes disabled
2. Form data submitted to appropriate API endpoint (POST for create, PUT for update)
3. If validation errors occur, display error messages below respective fields
4. If 50-entry limit reached, display error toast notification
5. If successful, display success toast ("Entry created" or "Entry updated")
6. Dispatch event to parent CalendarView to reload entries
7. Close modal and reset all form fields
8. Calendar view updates to show new/updated entry

### Deleting Entry

**User Action:** User clicks "Delete" button (available in view or edit mode)

**System Response:**
1. Delete confirmation modal opens
2. User sees warning: "Are you sure you want to delete this entry? This action cannot be undone."
3. User clicks "Confirm Delete" or "Cancel"
4. If confirmed, DELETE API call executes
5. Loading state shown during deletion
6. If successful, success toast appears ("Entry deleted successfully")
7. Dispatch event to parent CalendarView to reload entries
8. Modal closes
9. Calendar view updates showing entry removed

### Canceling Operation

**User Action:** User clicks "Cancel" or modal close button

**System Response:**
1. If in create mode, modal closes immediately
2. If in edit mode with unsaved changes, could optionally show confirmation (not in MVP)
3. All form state resets to defaults
4. Modal closes with smooth transition
5. No API calls made
6. Calendar view remains unchanged

### Handling Validation Errors

**User Action:** User attempts to save entry with invalid data

**System Response:**
1. Frontend validation prevents submission if required fields empty
2. If backend validation fails (e.g., future date submitted):
   - Error messages display below respective fields in red text
   - Save button remains enabled for retry
   - Field with error receives focus
   - User corrects error and resubmits

### Handling Entry Limit

**User Action:** User with 50 existing entries clicks "Add Entry"

**System Response:**
1. Modal opens normally (limit checked on save attempt)
2. User fills form and clicks Save
3. API returns 403 Forbidden with limit message
4. Error toast displays: "You have reached the maximum limit of 50 journal entries."
5. Save button returns to normal state
6. Modal remains open for user to acknowledge message
7. User must close modal manually

### Handling Network Errors

**User Action:** User attempts any operation while offline or during server error

**System Response:**
1. Loading state shows while request times out
2. Error toast displays: "Failed to [create/update/delete] entry. Please try again."
3. Modal remains open for user to retry
4. User can close modal or attempt operation again when connection restored

## 9. Conditions and Validation

### Title Field Validation

**Conditions:**
- Required: Field must not be empty
- Type: Must be string
- Maximum length: 255 characters

**Component Affected:** Title input field in Entry Form Section

**Interface State Effects:**
- Empty state: Save button disabled, no error shown until user attempts save or blur
- Valid state: Green checkmark icon (optional), Save button enabled (if other fields valid)
- Invalid state: Red border, error message below field, Save button disabled
- Error messages: "The title field is required." | "The title cannot exceed 255 characters."

### Content Field Validation

**Conditions:**
- Required: Field must not be empty
- Type: Must be string
- No maximum length constraint

**Component Affected:** Content textarea in Entry Form Section

**Interface State Effects:**
- Empty state: Save button disabled, no error shown until user attempts save or blur
- Valid state: Save button enabled (if other fields valid)
- Invalid state: Red border, error message below field, Save button disabled
- Error message: "The content field is required."

### Entry Date Field Validation

**Conditions:**
- Required: Field must not be empty
- Format: Must be valid date in YYYY-MM-DD format
- Temporal constraint: Must be today or in the past (before_or_equal:today)

**Component Affected:** Date input field in Entry Form Section

**Interface State Effects:**
- Date picker max attribute set to today's date (prevents selecting future dates in UI)
- Empty state: Save button disabled
- Valid state: Save button enabled (if other fields valid)
- Invalid state (future date): Red border, error message below field, Save button disabled
- Error messages: "The entry date field is required." | "Entry date must be today or in the past." | "The entry date must be in YYYY-MM-DD format."

### Entry Limit Validation

**Conditions:**
- User has created fewer than 50 entries: Can create new entries
- User has created exactly 50 entries: Cannot create new entries

**Component Affected:** Entry Modal save operation

**Interface State Effects:**
- Under limit: Save button functions normally, entry creation succeeds
- At limit: API returns 403 error, danger toast displays: "You have reached the maximum limit of 50 journal entries."
- "Add Entry" button in calendar remains enabled (limit checked on save, not on open)

### Authorization Validation

**Conditions:**
- User must own the entry to view, edit, or delete it
- Handled by Laravel policies and controller authorization

**Component Affected:** All modal operations for existing entries

**Interface State Effects:**
- Unauthorized access: API returns 404 (to prevent entry ID enumeration)
- Error toast displays: "Journal entry not found"
- Modal closes automatically

### Modal Mode Validation

**Conditions:**
- Create mode: No entry ID, form empty, only Save and Cancel buttons
- View mode: Entry ID exists, data displayed read-only, Edit and Delete buttons shown
- Edit mode: Entry ID exists, form populated, Save, Cancel, and Delete buttons shown

**Component Affected:** Entire Entry Modal component

**Interface State Effects:**
- Form fields conditionally rendered based on mode
- Action buttons conditionally rendered based on mode
- Modal title changes based on mode ("New Entry" | "View Entry" | "Edit Entry")

## 10. Error Handling

### Validation Errors (422 Unprocessable Entity)

**Scenario:** User submits form with data that fails backend validation

**Handling Strategy:**
- Parse validation error response from API
- Display field-specific error messages below corresponding input fields
- Keep modal open to allow user to correct errors
- Focus first field with error
- Maintain user's entered data (don't clear form)
- Remove error messages when user begins correcting the field (wire:model.blur)

**User Experience:**
- Clear indication of which fields have errors
- Specific error messages guide user to correct fix
- No data loss on validation failure

### Entry Limit Error (403 Forbidden)

**Scenario:** User with 50 existing entries attempts to create a new entry

**Handling Strategy:**
- Catch 403 error response from store endpoint
- Display danger variant toast with message: "You have reached the maximum limit of 50 journal entries."
- Keep modal open for user to acknowledge
- Do not clear form data (user may want to copy content elsewhere)
- Provide visual feedback that operation was rejected

**User Experience:**
- Clear explanation of why entry cannot be created
- Toast notification ensures message is seen
- User maintains control of modal closure

### Not Found Error (404 Not Found)

**Scenario:** User attempts to view/edit/delete an entry that doesn't exist or they don't own

**Handling Strategy:**
- Catch 404 error from API
- Display error toast: "Journal entry not found"
- Automatically close modal
- Dispatch event to parent to reload calendar (entry may have been deleted elsewhere)
- Reset modal state completely

**User Experience:**
- Brief error notification explains issue
- Automatic cleanup prevents further failed attempts
- Calendar refreshes to show current state

### Network/Server Errors (500 Internal Server Error or network timeout)

**Scenario:** API request fails due to server error, network issue, or timeout

**Handling Strategy:**
- Catch generic exceptions in try-catch blocks
- Display error toast with retry message: "Failed to [create/update/delete] entry. Please try again."
- Keep modal open to allow retry
- Reset loading states to allow resubmission
- Log error to console for debugging (in development)

**User Experience:**
- User is informed something went wrong
- User can retry operation immediately
- User can close modal and try again later
- Form data preserved for retry attempts

### Invalid Entry ID Format (400 Bad Request)

**Scenario:** Non-numeric entry ID passed to API endpoints

**Handling Strategy:**
- This is primarily a defensive backend check
- Frontend should always pass numeric IDs
- If encountered, treat as general error
- Display error toast: "An error occurred. Please try again."
- Close modal and refresh calendar

**User Experience:**
- Rare error, treated as unexpected system issue
- Clean recovery by closing modal and refreshing data

### Missing Data During Load

**Scenario:** API returns unexpected null/empty data when loading entry for view/edit

**Handling Strategy:**
- Check API response for expected data structure
- If data missing or malformed, display error toast
- Close modal automatically
- Log issue for debugging

**User Experience:**
- Brief error notification
- Automatic closure prevents broken UI state

### Delete Confirmation Cancellation

**Scenario:** User opens delete confirmation modal then clicks Cancel

**Handling Strategy:**
- Close confirmation modal only (not parent entry modal)
- Maintain entry modal in current state (view or edit)
- No API calls made
- No state changes

**User Experience:**
- Safe cancellation mechanism
- User remains in entry modal to continue viewing/editing

### Concurrent Modification

**Scenario:** Entry is modified or deleted by another session while user has it open

**Handling Strategy:**
- Not explicitly detected in MVP scope
- Update/delete will fail with 404 if entry was deleted
- Update will succeed with new data if entry was modified (last write wins)
- Future enhancement: add optimistic locking with updated_at timestamp

**User Experience:**
- If entry deleted elsewhere: Treated as "Not Found" error
- If entry modified elsewhere: User's changes overwrite (standard web behavior)

### Form Abandonment

**Scenario:** User opens modal, enters data, then closes without saving

**Handling Strategy:**
- In MVP scope: Allow immediate closure without confirmation
- Future enhancement: Detect unsaved changes and show confirmation dialog
- On closure, reset all form state to defaults

**User Experience:**
- Quick escape mechanism for accidental opens
- Trade-off: Risk of accidental data loss (acceptable for MVP)

## 11. Implementation Steps

1. **Create Livewire Component Structure**
   - Generate Livewire component: `php artisan make:livewire EntryModal`
   - Define public properties for modal state, form fields, and entry data
   - Implement mount method (minimal initialization, modal starts closed)

2. **Implement Modal Opening Logic**
   - Create `openCreateModal($date)` method that sets mode to 'create' and pre-fills date
   - Create `openViewModal($entryId)` method that fetches entry data and sets mode to 'view'
   - Create `openEditModal($entryId)` method that fetches entry data and sets mode to 'edit'
   - Implement Livewire listeners for these methods to receive events from CalendarView

3. **Build Modal Template Structure**
   - Create flux:modal container in blade template
   - Implement conditional rendering based on $modalMode property
   - Add modal header with dynamic title based on mode
   - Create form section for create/edit modes
   - Create display section for view mode

4. **Implement Form Fields**
   - Add title input with wire:model.defer="title" and validation attributes
   - Add date input with wire:model.defer="entry_date", type="date", max="today"
   - Add content textarea with wire:model.defer="content" and adequate rows
   - Implement validation error display below each field
   - Add required field indicators (asterisks or labels)

5. **Implement View Mode Display**
   - Create read-only display section for entry data
   - Format entry_date for readable display
   - Display title as heading, content preserving line breaks
   - Add metadata like created/updated timestamps (optional for MVP)

6. **Implement Save Functionality**
   - Create `save()` method in component class
   - Implement conditional logic: if $entryId exists, call update API, else call create API
   - Use Illuminate\Support\Facades\Http to make API requests with authentication
   - Handle successful responses: dispatch refresh event to parent, show success toast, close modal
   - Handle error responses: display validation errors, show error toasts for limits/failures
   - Implement loading state management during API calls

7. **Implement Delete Functionality**
   - Create delete confirmation modal (nested within entry modal)
   - Add `delete()` method that opens confirmation modal
   - Add `confirmDelete()` method that executes DELETE API call
   - Handle success: dispatch refresh event, show success toast, close both modals
   - Handle errors: show error toast, keep modals open for retry

8. **Implement Mode Switching**
   - Create `switchToEditMode()` method that transitions view mode to edit mode
   - Ensure entry data persists during mode switch
   - Update modal title and action buttons based on mode

9. **Implement Modal Close and State Reset**
   - Create `closeModal()` method that resets all properties to defaults
   - Implement `resetState()` helper method to clear form fields, errors, entry data
   - Ensure modal closure triggers state reset to prevent data leakage between opens

10. **Add Client-Side Validation**
    - Implement wire:model.blur for real-time validation feedback
    - Add computed property for save button disabled state (checks all required fields)
    - Implement character counter for title field showing remaining characters
    - Add JavaScript date validation to prevent future date selection in date picker

11. **Integrate with CalendarView Parent Component**
    - Add EntryModal component to CalendarView blade template
    - Update day-row component "Add Entry" button to dispatch openCreateModal event with date
    - Update day-row entry card click handler to dispatch openViewModal event with entry ID
    - Implement event listener in CalendarView to reload entries when entry modal dispatches refresh event

12. **Implement Toast Notifications**
    - Use Flux::toast() for all success and error notifications
    - Configure appropriate variants (success, danger, warning)
    - Ensure toast messages are clear and actionable
    - Implement toast for entry created, updated, deleted, and all error scenarios

13. **Add Loading States and Spinners**
    - Show loading spinner in modal body during entry fetch operations
    - Disable and show loading state on Save button during API calls
    - Disable and show loading state on Delete button during deletion
    - Prevent modal closure during active API operations

15. **Implement Accessibility Features**
    - Ensure modal has proper ARIA labels and roles (Flux handles most of this)
    - Implement focus management (focus title field on create/edit open)
    - Ensure keyboard navigation works (Tab, Enter, Escape)
    - Add focus trap within modal when open
    - Ensure error messages are associated with form fields for screen readers

16. **Style and Polish**
    - Apply consistent spacing and sizing using Tailwind utilities
    - Ensure modal is responsive on mobile devices
    - Test dark mode appearance (component should respect dark mode)
    - Add smooth transitions for modal open/close
    - Ensure action button styling matches mode (danger for delete, primary for save)
