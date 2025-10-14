# Product Requirements Document (PRD) - GreenThumb

## 1. Product Overview

GreenThumb is a web-based gardener's journal application designed to solve the critical problem of searching through historical gardening entries. 
The application enables gardeners to maintain a chronological record of their gardening activities and quickly retrieve specific information through AI-powered natural 
language search, eliminating the tedious process of manually browsing through months of entries.

Key differentiator: AI-powered natural language search that understands gardening-specific queries like "when did I plant tomatoes?" or "how often did I water the roses in July?"

## 2. User Problem

Gardeners who maintain journals face a significant usability challenge when trying to reference historical information. 
The current manual approach requires "clicking through" multiple months to find specific entries about plants, activities, or observations. This creates several pain points:

- Time-consuming navigation discourages consistent journal maintenance
- Users cannot quickly answer simple questions like "when did I last fertilize the tomatoes?"
- Historical patterns and trends are difficult to identify without extensive manual review
- The frustration of searching reduces the overall value of maintaining a journal

This problem directly impacts the likelihood of users maintaining a regular journaling habit, which in turn reduces the 
long-term value of the journal as a gardening reference tool.

## 3. Functional Requirements

### 3.1 User Authentication and Account Management

- Email and password-based registration system
- Email verification process for new account activation
- Password reset functionality via email
- Secure password storage
- Session management for authenticated users
- User data isolation ensuring users can only access their own entries

### 3.2 Journal Entry Management

- Create new journal entries with title and free-form text content
- Edit existing journal entries
- Delete journal entries
- Entry date selection restricted to past and present dates only
- Date storage without time components
- Maximum limit of 50 entries per user account
- Entry display on calendar interface
- Modal-based entry creation and editing interface

### 3.3 Calendar Interface

- Week-view calendar display with Monday as the start day
- Full-width calendar layout
- Current week highlight showing date range (e.g., "October 7-13, 2025")
- Previous and next week navigation buttons
- Visual indicators showing which dates have entries
- Direct access to entries by clicking calendar dates
- Button to add new entries on calendar dates
- Edit and delete options for existing entries

### 3.4 AI-Powered Search

- Natural language query processing using OpenRouter.ai API
- Search button in main navigation bar
- Modal popup interface for entering queries and displaying results
- Server-side AI processing to protect user data
- User data excluded from AI model training
- Loading spinner during AI query processing
- AI create summary of user query based on journal content
- Display list of relevant entries based on AI analysis
- Basic error handling with "Try again" message on failures
- Static "Ask AI to find..." placeholder text to promote feature usage

### 3.5 Data Privacy and Security

- Server-side only AI processing
- No transmission of user data for AI model training purposes
- Secure authentication flow
- User data encryption at rest
- Self-hosted deployment for complete data control

## 4. Product Boundaries

### 4.1 In Scope for MVP

- Text-only journal entries with title and content fields
- Single location per user (no multi-garden support)
- Web application only
- Email/password authentication
- AI search using general-purpose models via OpenRouter.ai
- Calendar-based weekly navigation
- Basic entry management (create, read, update, delete)
- 50 entry limit per user

### 4.2 Out of Scope for MVP

- Categories, tags, or entry classification systems
- Photo, video, or file attachments
- Sharing entries with other users
- Social features or collaboration capabilities
- External platform integrations (weather APIs, plant databases)
- Mobile native applications (iOS/Android)
- Data export functionality
- Future-dated entries or planning features
- Multiple garden or location support per user
- Advanced search filters or sorting options
- Entry limit warnings or upgrade paths
- Custom AI model training
- Rich text formatting in entries
- Entry versioning or history tracking

## 5. User Stories

### US-001: User Registration
As a new user, I want to register for an account using my email and password, so that I can start maintaining my gardening journal securely.

Acceptance Criteria:
- User can access a registration page with email and password fields
- Email field validates proper email format
- Password field has minimum length requirement (8 characters)
- Password field includes a confirmation input to prevent typos
- System prevents registration with already registered email addresses
- System displays clear error messages for validation failures
- Upon successful registration, user receives a verification email
- System creates a new user account in the database

### US-002: Email Verification
As a registered user, I want to verify my email address, so that I can activate my account and access the journal.

Acceptance Criteria:
- User receives an email with a verification link after registration
- Verification link is unique and time-limited
- Clicking verification link marks the account as verified
- Verified users can log in to the application
- System displays confirmation message after successful verification
- Expired verification links show appropriate error message
- User can request a new verification email if needed

### US-003: User Login
As a registered user, I want to log in with my email and password, so that I can access my private journal entries.

Acceptance Criteria:
- User can access a login page with email and password fields
- System authenticates credentials against database
- Successful login redirects to the calendar view
- Failed login displays clear error message
- System maintains user session after login
- System prevents access to journal features without authentication

### US-004: Password Reset Request
As a user who forgot their password, I want to request a password reset, so that I can regain access to my account.

Acceptance Criteria:
- User can access a "Forgot Password" link from the login page
- User can enter their email address to request reset
- System sends password reset email to valid email addresses
- System does not reveal whether email exists in database (security)
- Reset email contains a unique, time-limited reset link
- System displays confirmation message after sending reset email

### US-005: Password Reset Completion
As a user with a reset link, I want to create a new password, so that I can access my account again.

Acceptance Criteria:
- Reset link directs user to password reset form
- Form includes new password and confirmation fields
- Password meets minimum requirements (8 characters)
- System validates that both password fields match
- Successful reset updates password in database
- System invalidates the reset link after use
- Expired reset links display appropriate error message
- User is redirected to login page after successful reset

### US-006: User Logout
As a logged-in user, I want to log out of my account, so that I can secure my data when finished.

Acceptance Criteria:
- User can access logout option from navigation or user menu
- System terminates user session upon logout
- User is redirected to login page after logout
- Attempting to access protected pages after logout redirects to login

### US-007: View Weekly Calendar
As a logged-in user, I want to view my journal entries in a weekly calendar format, so that I can see my gardening activities in chronological context.

Acceptance Criteria:
- Calendar displays current week by default with Monday as first day
- Calendar shows 7 days (Monday through Sunday) in full-width layout
- Current week date range is displayed (e.g., "October 7-13, 2025")
- Dates with existing entries have visual indicators
- Calendar is responsive and displays properly on different screen sizes
- User can see entry titles or indicators on corresponding dates

### US-008: Navigate Calendar Weeks
As a user viewing the calendar, I want to navigate between weeks using previous and next buttons, so that I can browse my historical entries.

Acceptance Criteria:
- Previous week button is visible and clickable
- Next week button is visible and clickable
- Clicking previous button loads the previous week's entries
- Clicking next button loads the next week's entries
- Current week display updates to reflect the selected week
- Navigation maintains user session and state
- Calendar updates smoothly without full page reload

### US-009: Create New Journal Entry
As a user, I want to create a new journal entry with a title and description, so that I can record my gardening activities and observations.

Acceptance Criteria:
- User can click a button on a calendar date to create new entry
- System opens a modal dialog for entry creation
- Modal includes a title field (required)
- Modal includes a text area for content (required)
- Date selector restricts selection to past and present dates only
- Date selector does not include time component
- Modal includes Save and Cancel buttons
- Save button is disabled until required fields are completed
- Clicking Save creates entry and closes modal
- Clicking Cancel closes modal without saving
- New entry appears on calendar after creation
- System enforces 50 entry maximum per user

### US-010: View Existing Journal Entry
As a user, I want to view the full details of my journal entries, so that I can read my past observations and activities.

Acceptance Criteria:
- User can click on a date with entries to view them
- System displays entry title and full content
- Entry displays the associated date
- Multiple entries on the same date are all accessible
- Entry display is clear and readable
- User can close the entry view to return to calendar

### US-011: Edit Existing Journal Entry
As a user, I want to edit my existing journal entries, so that I can correct mistakes or add additional information.

Acceptance Criteria:
- User can access an Edit option for existing entries
- System opens the entry in an editable modal
- Modal pre-populates with current title and content
- User can modify title and content fields
- Save button updates the entry in the database
- Calendar reflects the updated entry information
- Cancel button discards changes and closes modal
- Validation ensures required fields remain filled

### US-012: Delete Journal Entry
As a user, I want to delete journal entries, so that I can remove entries I no longer want to keep.

Acceptance Criteria:
- User can access a Delete option for existing entries
- System prompts for confirmation before deletion
- Confirming deletion removes entry from database
- Deleted entry no longer appears on calendar
- Canceling deletion preserves the entry
- Deletion action cannot be undone (in MVP scope)
- Calendar updates immediately after deletion

### US-013: Access AI Search Interface
As a user, I want to access the AI search feature from the navigation, so that I can query my journal entries using natural language.

Acceptance Criteria:
- Search button or bar is visible in main navigation
- Navigation displays "Ask AI to find..." static text as a prompt
- Clicking the search interface opens a modal dialog
- Modal includes an input field for search queries
- Modal remains open until user closes it or submits a search
- User can close the modal without performing a search

### US-014: Perform AI Natural Language Search
As a user, I want to search my journal entries using natural language queries, so that I can quickly find specific information without browsing through months of entries.

Acceptance Criteria:
- User can enter a natural language query (e.g., "when did I water the tomatoes?")
- Submit button triggers AI search processing
- System displays a loading spinner during AI processing
- AI query is processed server-side via OpenRouter.ai
- User data is not sent for AI model training
- System returns relevant journal entries based on query
- AI-generated answer to user question displayed
- Results display within the search modal
- User can perform multiple searches in one session
- Each search query is logged for analytics purposes

### US-015: View AI Search Results
As a user who performed an AI search, I want to see the relevant journal entries that match my query, so that I can quickly access the information I'm looking for.

Acceptance Criteria:
- Search results display within the search modal
- Each result shows at minimum the entry title and date
- Results are clearly distinguishable from each other
- Empty results display an appropriate message
- User can read result content without leaving the modal
- Results are relevant to the submitted query

### US-016: Handle AI Search Errors
As a user experiencing an AI search failure, I want to see a clear error message, so that I understand something went wrong and can try again.

Acceptance Criteria:
- System detects when AI search fails or times out
- Error message displays "Try again" text
- User can close the error message
- User can submit a new search query after an error
- Search modal remains open after error
- System handles API connectivity issues gracefully

### US-017: Entry Date Restriction Enforcement
As a user, I want the system to prevent me from creating future-dated entries, so that my journal remains focused on actual observations rather than planning.

Acceptance Criteria:
- Date selector allows selection of today's date
- Date selector allows selection of any past date
- System validates dates as client-side
- Error message clearly explains the date restriction

### US-019: Session Management
As a logged-in user, I want my session to persist while I'm actively using the application, so that I don't have to repeatedly log in during normal use.

Acceptance Criteria:
- User session persists across page navigation
- Session expires after a reasonable period of inactivity
- Expired session redirects user to login page
- User receives clear notification of session expiration
- Active usage extends session timeout
- Session data is stored securely

### US-020: Calendar Empty States
As a new user with no entries, I want to see helpful guidance on the calendar, so that I understand how to create my first entry.

Acceptance Criteria:
- Empty calendar displays clear visual state
- User can identify which dates have no entries
- Interface clearly shows how to add entries
- Empty state doesn't obstruct calendar functionality
- First-time users can easily understand the interface

### US-021: Data Privacy Compliance
As a user concerned about privacy, I want assurance that my journal data is processed securely and not used for AI training, so that I can trust the application with my personal gardening information.

Acceptance Criteria:
- There is information about data privacy in the application
- Users are informed that AI processing is server-side only
- AI processing occurs server-side only
- User data is never transmitted to AI providers for model training
- Application clearly documents data privacy practices
- Data is stored on self-hosted infrastructure
- User passwords are securely hashed
- User data is isolated (users cannot access other users' entries)

### US-022: Mobile Web Access
As a user accessing the application from a mobile browser, I want the interface to be usable on smaller screens, so that I can maintain my journal from any device.

Acceptance Criteria:
- Calendar view is responsive on mobile screen sizes
- Navigation elements are accessible on mobile devices
- Modal dialogs display properly on small screens
- Text input fields are usable on mobile keyboards
- Search functionality works on mobile devices
- Week navigation is accessible on mobile
- Touch interactions work properly for all clickable elements

### US-023: Entry Content Validation
As a user creating an entry, I want clear validation feedback, so that I understand what information is required before saving.

Acceptance Criteria:
- Title field shows required indicator
- Content field shows required indicator
- Save button is disabled when required fields are empty
- System displays validation messages for empty required fields
- Validation occurs before submission attempt
- Error messages are clear and actionable
- User can correct validation errors without losing entered data

## 6. Success Metrics

### Primary Success Metric

80% of registered users perform at least 3 AI searches

Measurement approach:
- Track each AI search query in search_analytics table
- Link searches to user_id
- Calculate unique users performing 3+ searches
- Calculate percentage against total registered users
- Report metrics weekly and monthly

### Secondary Success Metrics

User Adoption and Retention:
- Weekly active users (WAU)
- Monthly active users (MAU)
- User registration completion rate (including email verification)
- Average time from registration to first entry creation

Feature Engagement:
- Average number of AI searches per active user
- Search success rate (queries returning results vs. errors)
- Percentage of AI searches vs. calendar-based entry access
- Average entries created per user per week

Journal Usage Patterns:
- Average entries per active user
- Entry creation frequency (entries per day/week)
- Edit and delete rates for entries
- Calendar navigation patterns (weeks browsed per session)

Technical Performance:
- AI search response time (average and 95th percentile)
- AI search failure rate
- Application uptime and availability
- Page load times for calendar and modals

Data Collection Implementation:
- Search analytics table records: user_id, query text, results_count, timestamp
- User activity logs for feature usage tracking
- Performance monitoring for AI API calls
- Error logging for debugging and reliability tracking

Success Timeline:
- Week 1-2 post-launch: Focus on registration completion and first entry creation rates
- Week 3-4: Monitor AI search adoption and usage patterns
- Week 5-6: Evaluate 3+ searches metric and overall feature engagement
- Ongoing: Track retention, search quality, and technical performance

The primary metric (80% performing 3+ searches) directly validates whether the AI search feature solves the core user problem of finding information without tedious navigation. This metric will determine MVP success and inform future feature prioritization.