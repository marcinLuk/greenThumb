# UI Architecture for GreenThumb

## 1. UI Structure Overview

GreenThumb follows a modal-based interaction pattern with a central calendar view as the primary interface. 
The application uses a single-page application approach for the authenticated experience, with separate pages for authentication flows.

### Core Architecture Principles:
- **Calendar-Centric Design**: The weekly calendar view serves as the primary workspace after authentication
- **Modal-Based Interactions**: All entry management (create, view, edit) and search functionality occur in modal overlays
- **Minimal Navigation**: Simple top navigation bar with search access and user menu
- **Weekly View Pattern**: List-based weekly display showing 7 days with entries grouped under each date
- **RESTful API Integration**: All data operations use the available API endpoints with Sanctum authentication
- **Responsive Design**: Desktop-first approach ensuring usability across all device sizes

### Application States:
1. **Unauthenticated State**: Registration, login, email verification, password reset flows
2. **Authenticated State**: Calendar view with modal interactions for entries and search
3. **Loading States**: Spinner indicators during API operations
4. **Error States**: Clear error messages with retry options

## 2. View List

### 2.1 Register View (already implemented by Laravel)

### 2.2 Login View (already implemented by Laravel)

### 2.3 Email Verification View (already implemented by Laravel)

### 2.4 Forgot Password View (already implemented by Laravel)

### 2.5 Reset Password View (already implemented by Laravel)

### 2.6 Calendar View

**Path**: `/calendar`
**Authentication**: Authenticated and verified users only

**Main Purpose**:
Primary application interface showing weekly calendar view of journal entries. 
Users can navigate between weeks, view entries, create new entries, and access search functionality.

**Key Information to Display**:
- Current week date range with week number
- 7 days of the week (Monday through Sunday) in list format
- All entry titles for each date (sorted by creation time, oldest first)
- "Add Entry" button for each date

**Key View Components**:

- **Top Navigation Bar Component**:
  - Application name (left)
  - Search button with "Ask AI to find..." text (center)
  - Login / Register buttons on right if unauthenticated

- **Welcome Message Component**:
  - Displays for not logged-in users: "Welcome to GreenThumb! Please log in or register to manage your journal entries."
  - Centered in main content area
  - Visible only when unauthenticated

- **Week Navigation Component**:
  - Visible only when authenticated
  - Previous week button (left)
  - Current week display: "Week 41: October 7-13, 2025" (center)
  - Next week button (right)
  - Layout: Horizontal flex container below main navigation

- **Calendar List Component**:
  - Visible only when authenticated
  - 7-day list, each day showing:
    - Date heading (left): "Monday, October 7"
    - "Add Entry" button (right, always visible)
    - Entry list below (if entries exist for that date):
      - Each entry title as clickable link
      - Full title displayed (no truncation)
      - Consistent spacing between entries (8-12px)
      - No visual separators (clean list)
  - Displays all 7 days even if no entries exist
  - Empty dates show only date heading and "Add Entry" button
  - Visible separation between days (16-24px spacing with bottom border)

- **Jump to Today Floating Button**:
  - Positioned bottom-right corner
  - Fixed position
  - Icon: Calendar or "Today" text
  - Clicking updates week range to current week

- **Loading States**:
  - Skeleton loading for initial calendar load
  - Spinner overlay during week navigation
  - Button loading states during API calls

**UX, Accessibility, and Security Considerations**:
- Keyboard navigation: Tab through dates, Enter to open modals
- Screen reader announces current week and entry counts
- Focus management: Return focus to triggering element after modal close
- Smooth loading transitions without jarring content shifts
- Maintain scroll position after modal interactions
- Clear visual hierarchy: date > entries > actions
- Touch-friendly targets (minimum 44x44px) for mobile
- Responsive layout: Single column on mobile, potentially multi-column on desktop
- Loading states prevent multiple simultaneous API calls
- Entry count updates in real-time after create/delete operations
- Auto-refresh calendar data on modal close
- User can only see and interact with their own entries (enforced by API)

**API Interactions**:
- GET `/api/journal-entries/date-range?start_date={date}&end_date={date}` - Load week's entries
- Calendar refreshes after entry create/update/delete operations
- Polling or event-based refresh on entry count changes

---

### 2.7 Entry Modal (Unified Create/View/Edit)

**Path**: Modal overlay, reusable blade component, Flux component 
**Triggers**:
- Create: Click "Add Entry" button on any date
- View: Click entry title in calendar
- Edit: Click "Edit" button in view mode
- Delete: Click "Delete" button in view or edit mode
- 
**Main Purpose**:
Single modal component that handles three states: creating new entries, viewing existing entries, and editing existing entries. 
The modal transforms between states based on user actions.

**Key Information to Display**:
- Entry title
- Entry content
- Action buttons based on current state

**Key View Components**:

- **Create Mode**:
  - Modal title: "New Entry for [Date]"
  - Title input field (required, focused on open)
  - Content textarea (required, expandable)
  - "Save" button (disabled until both fields filled, shows loading spinner)
  - "Cancel" button
  - Validation error messages as inline text below fields or buttons

- **View Mode**:
  - Modal title: Entry title
  - Title display (read-only, non-editable appearance)
  - Content display (read-only, non-editable appearance)
  - "Edit" button
  - "Delete" button
  - "Close" button

- **Edit Mode** (transforms from View mode):
  - Modal title: "Edit Entry"
  - Title input field (pre-filled, enabled)
  - Content textarea (pre-filled, enabled)
  - "Save" button (shows loading spinner during save)
  - "Cancel" button
  - "Delete" button (still visible)
  - Edit button hidden, Save button shown

- **Delete Confirmation Component** (inline message in view/edit mode):
  - Warning message: "Are you sure you want to delete this entry? This action cannot be undone, click 'Delete' to confirm."

- **Entry Limit Reached State** (when at 50 entries):
  - Modal appears when clicking "Add Entry" at limit
  - Message: "You've reached the maximum limit of 50 journal entries. Delete existing entries to create new ones."
  - "Close" button only

**UX, Accessibility, and Security Considerations**:
- Modal opens with focus on first input field (create/edit) or close button (view)
- Escape key closes modal
- Click outside modal closes it (with confirmation if unsaved changes)
- Smooth transitions between view and edit modes
- Required field indicators (asterisks)
- Character count not displayed (MVP decision)
- Date cannot be changed once entry created (must delete and recreate)
- Delete requires explicit confirmation
- Loading states disable all interactive elements during save/delete
- Error messages are specific and actionable
- Modal is fully responsive, adapts to mobile screens
- Touch-friendly buttons on mobile
- Autosave draft to localStorage (optional enhancement)
- ARIA dialog role, proper focus trap
- Screen reader announces modal state changes

**API Interactions**:
- **Create**: POST `/api/journal-entries` with title, content, entry_date
  - Success (201): Close modal, refresh calendar
  - Error (403): Show "maximum limit reached" message
  - Error (422): Show validation errors
  - Error (500): Show generic error with retry option

- **View**: GET `/api/journal-entries/{id}`
  - Success (200): Display entry data
  - Error (404): Entry not found message

- **Update**: PUT `/api/journal-entries/{id}` with title, content
  - Success (200): Close modal, refresh calendar
  - Error (404): Entry not found
  - Error (422): Show validation errors
  - Error (500): Show generic error with retry

- **Delete**: DELETE `/api/journal-entries/{id}`
  - Success (200): Close modal, refresh calendar
  - Error (404): Entry not found
  - Error (500): Show generic error with retry

---

### 2.8 Search Modal

**Path**: Modal overlay (not a separate route)
**Trigger**: Click search button in top navigation

**Main Purpose**:
Allow users to search their journal entries using natural language queries. 
Display AI-powered search results with relevant entries, showing title, date, and content snippets with highlighted keywords.

**Key Information to Display**:
- Search input field
- Search results: entry title, date, content snippet with highlighted keywords
- Results count
- No results message when applicable

**Key View Components**:

- **Search Input Component**:
  - Text input with placeholder: "Ask AI to find..."
  - Character limit: 3 minimum, 200 maximum
  - Helper text: "Enter at least 3 characters to search"
  - "Search" button (disabled until 3+ characters entered, shows loading spinner during search)
  - "Close" button (X icon, top-right corner)

- **Search Results Component**:
  - Results header: AI-generated summary or "Found X entries matching your query"
  - Results list:
    - Each result displays:
      - Entry title (clickable, opens Entry Modal in view mode)
    - Clicking an entry title opens the Entry Modal in view mode
  - Results are sorted by relevance

- **No Results State**:
  - Message: "No results found matching your query: '[query text]'"
  - "New Search" button to clear and try again

- **Loading State**:
  - Loading spinner displays on "Search" button
  - Button disabled during search
  - Results area shows loading text: "Searching your journal..."

- **Error State**:
  - Inline Message: "An error occurred while processing your search. Please try again."
  - "Try Again" button to retry the same query
  - "Close" button

**UX, Accessibility, and Security Considerations**:
- Search input focused on modal open
- Enter key triggers search
- Escape key closes modal
- Click outside modal closes it
- Minimum/maximum character validation before allowing search
- Real-time character counter (optional)
- Keyword highlighting uses background color or bold text for visibility
- Results are scrollable within modal if many results
- Search button shows loading state, preventing duplicate submissions
- Clear error messages with actionable next steps
- Search history not stored in UI (privacy)
- Clicking entry from results closes search modal and opens entry modal
- Modal is fully responsive
- ARIA live region announces search results count
- Screen reader compatible with keyboard navigation through results

**API Interactions**:
- **Search**: POST `/api/search` with query string
  - Request body: `{ "query": "user search query" }`
  - Success (200): Returns `{ "success": true, "data": { "summary": "...", "entries": [...], "results_count": N } }`
  - Error (500): Show error message with retry option
  - Error (422): Show validation error
  - Throttled (429): Show rate limit message

- **Open Entry from Results**: Same as Entry Modal view mode
  - GET `/api/journal-entries/{id}`

**Search Analytics**:
- All searches are automatically logged via API
- No user-visible analytics in MVP
- Logged data: user_id, query, results_count, timestamp

---

### 2.9 Entry Limit Modal (reusable blade component, Flux component)

**Path**: Modal overlay (not a separate route)
**Trigger**: Click "Add Entry" button when user has 50 entries

**Main Purpose**:
Inform users they have reached the maximum entry limit and cannot create new entries until they delete existing ones.

**Key Information to Display**:
- Clear explanation of the limit
- Instructions for creating space (delete existing entries)

**Key View Components**:

- **Limit Reached Message Component**:
  - Heading: "Entry Limit Reached"
  - Message: "You've reached the maximum limit of 50 journal entries. Delete existing entries to create new ones."
  - "Close" button

**UX, Accessibility, and Security Considerations**:
- Modal is small, centered, not full-screen
- Clear, friendly tone (not error language)
- Escape key closes modal
- Click outside closes modal
- Focus on "Close" button when opened
- ARIA dialog role
- Message is concise and actionable
- No negative emotional language
- Entry count is always validated server-side to prevent bypassing limit

**API Interactions**:
- No direct API calls from this modal

---

## 3. User Journey Map

### 3.1 New User Registration Flow (already implemented by Laravel)

### 3.2 Returning User Login Flow (already implemented by Laravel)

### 3.3 Password Reset Flow (already implemented by Laravel)

### 3.4 Creating a Journal Entry Flow 

1. **Calendar View** → User viewing `/calendar`
2. **Select Date** → User identifies date for new entry
3. **Add Entry** → User clicks "Add Entry" button for that date
4. **Modal Opens** → Entry Modal opens in Create mode
5. **Date Pre-Filled** → Modal add selected date (hidden input, user cannot change)
6. **Enter Title** → User types entry title
7. **Enter Content** → User types entry content in textarea
8. **Save** → User clicks "Save" button
9. **API Call** → POST request to create entry
10. **Success** → Modal closes, calendar refreshes, new entry appears
11. **Confirmation** → Entry visible in calendar under selected date

**Alternative Paths**:
- **Missing Required Fields**: Save button remains disabled
- **Validation Error**: Error message shown, user corrects and retries
- **Entry Limit Reached**: Entry Limit Modal appears instead of Entry Modal
- **Cancel**: User clicks "Cancel", modal closes, no changes saved
- **API Error**: Error message shown with retry option

---

### 3.5 Viewing and Editing an Entry Flow

1. **Calendar View** → User viewing `/calendar`
2. **Select Entry** → User clicks entry title in calendar
3. **Modal Opens** → Entry Modal opens in View mode
4. **Read Entry** → User reads entry title and content
5. **Edit Decision** → User clicks "Edit" button
6. **Edit Mode** → Modal transforms to Edit mode (inputs enabled)
7. **Modify Content** → User updates title and/or content
8. **Save** → User clicks "Save" button
9. **API Call** → PUT request to update entry
10. **Success** → Modal closes, calendar refreshes, updated entry visible
11. **Confirmation** → Changes reflected in calendar

**Alternative Paths**:
- **View Only**: User clicks "Close" after viewing, modal closes
- **Cancel Edit**: User clicks "Cancel" in edit mode, modal returns to view mode
- **Delete Instead**: User clicks "Delete" → Delete Confirmation Flow
- **Validation Error**: Error message shown, user corrects and retries
- **API Error**: Error message shown with retry option

---

### 3.6 Deleting an Entry Flow

1. **View Entry** → User viewing entry in Entry Modal (view mode)
2. **Delete Decision** → User clicks "Delete" button
3. **Confirmation** → Confirmation message appears: "Are you sure you want to delete this entry? This action cannot be undone."
4. **Confirm** → User clicks "Delete" (danger button)
5. **API Call** → DELETE request to remove entry
6. **Success** → Modal closes, calendar refreshes, entry removed
7. **Confirmation** → Entry no longer visible in calendar

**Alternative Paths**:
- **Cancel Delete**: User clicks "Cancel", returns to view mode
- **API Error**: Error message shown, modal remains open, retry option available

---

### 3.7 AI Search Flow

1. **Calendar View** → User viewing `/calendar`
2. **Search Access** → User clicks search button in navigation
3. **Modal Opens** → Search Modal opens with input focused
4. **Enter Query** → User types natural language query (e.g., "when did I plant tomatoes?")
5. **Character Validation** → Search button enabled after 3+ characters
6. **Submit** → User clicks "Search" or presses Enter
7. **Loading** → Loading spinner appears, button disabled
8. **API Processing** → POST request to search endpoint
9. **Results Display** → Results appear with AI summary and entry list
10. **Browse Results** → User reads snippets with highlighted keywords
11. **Select Entry** → User clicks entry title from results
12. **Entry Modal Opens** → Search modal closes, Entry Modal opens in view mode
13. **Read Entry** → User views full entry details

**Alternative Paths**:
- **No Results**: "No results found" message with suggestions
- **Search Error**: Error message with "Try Again" option
- **New Search**: User clears and enters new query
- **Close Without Selection**: User closes modal, returns to calendar
- **Character Limit**: User exceeds 200 characters, validation prevents submission

---

### 3.8 Week Navigation Flow

1. **Calendar View** → User viewing current week at `/calendar`
2. **Navigate Back** → User clicks "Previous Week" button
3. **Week Changes** → Calendar updates to show previous week's entries
4. **Date Range Updates** → Week header shows new date range and week number
5. **Float Button Appears** → "Jump to Today" button becomes visible
6. **Continue Browsing** → User continues clicking previous/next as needed
7. **Return to Current** → User clicks "Jump to Today" floating button
8. **Current Week** → Calendar returns to current week
9. **Float Button Hides** → "Jump to Today" button disappears

**Alternative Paths**:
- **Navigate Forward**: Same flow using "Next Week" button
- **Direct Navigation**: User jumps to today from any past/future week
- **No Entries in Week**: Calendar shows all 7 days with "Add Entry" buttons, no entries listed

---

### 3.9 Session Timeout Flow (already handled by Laravel)

---

## 4. Layout and Navigation Structure

### 4.1 Global Navigation Structure

**Unauthenticated Views** (Register, Login):
- Minimal navigation
- Logo/app name links to login page

**Authenticated Views** (Dashboard/Calendar):
- **Top Navigation Bar** (fixed position, always visible):
  - Left: Logo/app name (links to dashboard)
  - Center: Search button with "Ask AI to find..." text
  - Right: Entry counter "X/50 entries" | User menu dropdown
    - User menu options:
      - User email display
      - Logout

- **Week Navigation Bar** (below top navigation):
  - Previous Week button | Week display "Week X: [Date Range]" | Next Week button
  - Full-width, horizontally centered

- **Main Content Area**:
  - Calendar list view (7 days)
  - Scrollable content

- **Floating Elements**:
  - "Jump to Today" button (bottom-right, only visible when not on current week)

### 4.2 Layout Hierarchy

```
Unauthenticated Layout:
├── Simple Header (logo/name)
├── Main Content (centered form)

Authenticated Layout:
├── Top Navigation Bar (fixed)
│   ├── Logo
│   ├── Search Button
│   ├── Entry Counter
│   └── User Menu
├── Week Navigation Bar
│   ├── Previous Button
│   ├── Week Display
│   └── Next Button
├── Main Content (scrollable)
│   └── Calendar List (7 days)
│       ├── Day 1 (Date | Add Entry button | Entry list)
│       ├── Day 2 (Date | Add Entry button | Entry list)
│       └── ... Day 7
└── Floating Button (Jump to Today)

Modal Overlays (rendered above all content):
├── Entry Modal (create/view/edit states)
├── Search Modal
└── Entry Limit Modal
```

### 4.3 Navigation Patterns

**Primary Navigation**:
- Logo always returns to dashboard (calendar view)
- Search button opens search modal
- User menu provides logout and settings access

**Secondary Navigation**:
- Week navigation (previous/next buttons)
- Jump to Today floating button

**Contextual Navigation**:
- Entry titles clickable to open view modal
- "Add Entry" buttons for each date
- Modal close buttons and overlay clicks

### 4.4 Responsive Breakpoints

- **Mobile** (< 640px): Single column, stacked elements, simplified navigation
- **Tablet** (640px - 1024px): Optimized spacing, potentially side-by-side elements
- **Desktop** (> 1024px): Full layout with optimal spacing

---

## 5. Key Components

### 5.1 Form Components

#### Input Field Component
**Purpose**: Reusable text input with consistent styling and validation

**Props**:
- `label` (string): Field label
- `type` (string): Input type (text, email, password)
- `value` (string): Current value
- `placeholder` (string): Placeholder text
- `required` (boolean): Required field indicator
- `error` (string): Validation error message
- `autocomplete` (string): Autocomplete attribute
- `disabled` (boolean): Disabled state

**Features**:
- Shows required indicator (*)
- Displays validation errors inline
- Focus and blur states
- Accessible with proper ARIA labels

#### Textarea Component
**Purpose**: Multi-line text input for entry content

**Props**:
- `label` (string): Field label
- `value` (string): Current value
- `placeholder` (string): Placeholder text
- `required` (boolean): Required field indicator
- `error` (string): Validation error message
- `rows` (number): Initial height in rows
- `disabled` (boolean): Disabled state

**Features**:
- Auto-expanding height
- Shows required indicator
- Validation error display
- Accessible with proper ARIA

#### Button Component
**Purpose**: Consistent button styling across application

**Props**:
- `label` (string): Button text
- `type` (string): Button type (button, submit)
- `variant` (string): Style variant (primary, secondary, danger)
- `loading` (boolean): Loading state
- `disabled` (boolean): Disabled state
- `onClick` (function): Click handler

**Features**:
- Loading spinner when loading=true
- Disabled state styling
- Focus states
- Consistent sizing for accessibility

---

### 5.2 Calendar Components

#### Calendar Day Component
**Purpose**: Display single day with date, entries, and add button

**Props**:
- `date` (Date): The date for this day
- `entries` (Array): List of entries for this date
- `onAddEntry` (function): Handler for add entry button
- `onEntryClick` (function): Handler for entry title clicks

**Features**:
- Date heading display
- "Add Entry" button (always visible)
- Entry list (sorted by creation time)
- Clickable entry titles
- Empty state (no entries)

#### Week Display Component
**Purpose**: Show current week range and week number

**Props**:
- `startDate` (Date): First day of week
- `endDate` (Date): Last day of week
- `weekNumber` (number): Week number

**Features**:
- Formatted display: "Week 41: October 7-13, 2025"
- Responsive text sizing

#### Week Navigation Component
**Purpose**: Previous/next week navigation controls

**Props**:
- `onPrevious` (function): Previous week handler
- `onNext` (function): Next week handler
- `currentWeek` (Date): Current week start date

**Features**:
- Previous/next buttons with icons
- Loading states during navigation
- Keyboard accessible

---

### 5.3 Modal Components

#### Modal Container Component
**Purpose**: Base modal overlay with consistent behavior

**Props**:
- `isOpen` (boolean): Modal visibility
- `onClose` (function): Close handler
- `title` (string): Modal title
- `size` (string): Modal size (small, medium, large)
- `children` (component): Modal content

**Features**:
- Overlay backdrop
- Close on Escape key
- Close on outside click (optional)
- Focus trap
- Smooth open/close transitions
- Responsive sizing
- ARIA dialog role

#### Entry Modal Component
**Purpose**: Unified modal for create/view/edit entry operations

**State**:
- `mode` (string): create | view | edit
- `entry` (object): Entry data
- `date` (Date): Entry date
- `loading` (boolean): API operation in progress

**Features**:
- Transforms between modes without closing
- Pre-populated date (read-only)
- Form validation
- Delete confirmation
- Loading states
- Error handling

#### Search Modal Component
**Purpose**: Search interface with results display

**State**:
- `query` (string): Search query
- `results` (Array): Search results
- `loading` (boolean): Search in progress
- `error` (string): Error message

**Features**:
- Character validation (3-200)
- Results display with highlighted keywords
- Clickable results
- No results state
- Error state with retry
- Loading state

---

### 5.4 Display Components

#### Entry Card Component (for calendar and search results)
**Purpose**: Display entry information in list format

**Props**:
- `title` (string): Entry title
- `date` (Date): Entry date
- `snippet` (string, optional): Content snippet (for search results)
- `onClick` (function): Click handler

**Features**:
- Clickable title
- Date formatting
- Hover states
- Responsive layout

#### Loading Spinner Component
**Purpose**: Consistent loading indicator

**Props**:
- `size` (string): Spinner size (small, medium, large)
- `centered` (boolean): Center in container

**Features**:
- Animated spinner
- Accessible (ARIA live region)
- Consistent across application

---

### 5.5 Utility Components

### 5.6 Layout Components

#### Page Container Component (already implemented by Laravel)

## 6. Data Flow and State Management

### 6.1 Authentication State (already implemented by Laravel)

### 6.2 Calendar State
- Current week date range (start/end)
- Entries for current week (Array)
- Loading state
- Entry count (current/max)

### 6.3 Modal State
- Entry modal: open/closed, mode (create/view/edit), current entry
- Search modal: open/closed, query, results, loading, error
- Entry limit modal: open/closed

### 6.4 API Response Handling
- Success: Update state, close modals, refresh data
- Validation errors (422): Display inline errors
- Authentication errors (401): Redirect to login
- Rate limiting (429): Show rate limit message
- Server errors (500): Show generic error with retry
- Not found (404): Show not found message

---

## 7. Accessibility Requirements

### 7.1 Keyboard Navigation
- All interactive elements accessible via Tab
- Logical tab order
- Enter activates buttons and links
- Escape closes modals
- Focus visible indicators

### 7.2 Screen Reader Support
- Proper heading hierarchy (h1 → h2 → h3)
- ARIA labels on all interactive elements
- ARIA live regions for dynamic content
- ARIA dialog role for modals
- Alt text for any images (logo, icons)

### 7.3 Visual Accessibility
- Minimum 4.5:1 color contrast for text
- Focus indicators clearly visible
- Error messages in color + text (not color alone)
- Text resizable up to 200%
- Touch targets minimum 44x44px

### 7.4 Form Accessibility
- Label associated with each input
- Required fields indicated
- Error messages linked to fields
- Autocomplete attributes for personal data

---

## 8. Security Considerations

### 8.1 Authentication Security
- CSRF protection on all forms
- Sanctum token authentication
- HttpOnly cookies for session storage
- Secure password requirements (8+ characters)
- Rate limiting on auth endpoints
- Generic error messages to prevent user enumeration

### 8.2 Data Security
- User data isolation (API enforces user_id filtering)
- Authorization checks on all entry operations
- No entry ID enumeration (404 for unauthorized access)
- XSS prevention (escaped output)
- SQL injection prevention (parameterized queries via Eloquent)

### 8.3 Privacy Security
- AI processing server-side only
- User data not sent for AI model training
- Search analytics stored securely
- No third-party tracking (MVP scope)
- Self-hosted deployment

---

## 9. Performance Considerations

### 9.1 Initial Load
- Minimize bundle size
- Lazy load modals
- Optimize assets (compress images, minify CSS/JS)
- Use CDN for static assets (if applicable)

### 9.2 API Performance
- Paginated responses for large data sets
- Date range queries for weekly calendar (only load needed entries)
- Debounce search input (optional)
- Cache frequently accessed data (entry count)

### 9.3 Rendering Performance
- Lazy render calendar days
- Virtual scrolling for large entry lists (if needed)
- Optimize re-renders (React.memo, Vue computed, etc.)
- Smooth transitions without blocking UI

---

## 10. User Story Mapping to UI Elements

### Authentication Stories (US-001 to US-006)
- **US-001 (Registration)**: Register View (2.1)
- **US-002 (Email Verification)**: Email Verification View (2.3)
- **US-003 (Login)**: Login View (2.2)
- **US-004 (Password Reset Request)**: Forgot Password View (2.4)
- **US-005 (Password Reset Completion)**: Reset Password View (2.5)
- **US-006 (Logout)**: User menu in Navigation Bar Component

### Calendar Stories (US-007 to US-008)
- **US-007 (View Weekly Calendar)**: Dashboard/Calendar View (2.6), Calendar List Component, Calendar Day Component
- **US-008 (Navigate Calendar Weeks)**: Week Navigation Component, Jump to Today Button

### Entry Management Stories (US-009 to US-012)
- **US-009 (Create New Journal Entry)**: Entry Modal Component (Create mode), "Add Entry" buttons in Calendar Day Component
- **US-010 (View Existing Journal Entry)**: Entry Modal Component (View mode), Clickable entry titles
- **US-011 (Edit Existing Journal Entry)**: Entry Modal Component (Edit mode), Edit button in view mode
- **US-012 (Delete Journal Entry)**: Delete button in Entry Modal, Confirmation Dialog Component

### Search Stories (US-013 to US-016)
- **US-013 (Access AI Search Interface)**: Search button in Navigation Bar, Search Modal Component
- **US-014 (Perform AI Natural Language Search)**: Search input and submit in Search Modal
- **US-015 (View AI Search Results)**: Search results display in Search Modal, Entry Card Component with highlighted keywords
- **US-016 (Handle AI Search Errors)**: Error state in Search Modal, Error Message Component

---

## 11. Edge Cases and Error Handling

### 11.1 Network Errors
- **Scenario**: User loses internet connection during operation
- **Handling**: Show error message "Connection lost. Please check your internet and try again." with retry button

### 11.2 API Timeouts
- **Scenario**: API takes too long to respond
- **Handling**: Show timeout message after 30 seconds, provide retry option

### 11.3 Concurrent Entry Deletion
- **Scenario**: User deletes an entry in one browser tab while viewing it in another
- **Handling**: When attempting to view/edit, show "Entry not found" message

### 11.4 Entry Count Sync Issues
- **Scenario**: Entry count displayed differs from actual count
- **Handling**: Refresh entry count after every create/delete operation, use authoritative API count

### 11.5 Modal State on Browser Back
- **Scenario**: User opens modal, then clicks browser back button
- **Handling**: Close modal, maintain calendar state (requires history state management)

### 11.6 Form Abandonment
- **Scenario**: User starts creating/editing entry but navigates away
- **Handling**: Show confirmation dialog: "You have unsaved changes. Are you sure you want to leave?"

### 11.7 Week Navigation Beyond Data Range
- **Scenario**: User navigates to weeks with no data
- **Handling**: Display empty calendar with all 7 days, "Add Entry" buttons available

### 11.8 Search with Special Characters
- **Scenario**: User enters query with special characters or HTML
- **Handling**: Escape and sanitize input, process safely, display escaped characters in results

### 11.9 Very Long Entry Titles/Content
- **Scenario**: User creates entry with extremely long title or content
- **Handling**: API validation limits (e.g., 255 chars for title, 10000 for content), UI shows character limits if implemented

### 11.10 Multiple Rapid Clicks
- **Scenario**: User rapidly clicks submit button multiple times
- **Handling**: Disable button on first click, show loading state, prevent duplicate submissions
