# GreenThumb - Product Requirements Document

## Conversation Summary

### Decisions

1. **Entry Content**: Journal entries will contain only observations and actions (watering, planting, etc.) in free-form text format
2. **AI Capabilities**: Natural language AI search without custom model training, using general-purpose AI
3. **Authentication**: Email/password authentication with email verification and password reset functionality
4. **Success Metrics**: 80% of users must perform at least 3 AI searches
5. **Data Limits**: 50 entries per user maximum; no data deletion functionality in MVP
6. **Media Support**: Text-only entries (no photos, videos, or rich media)
7. **Timeline & Budget**: 6-week development timeline with unlimited budget, using existing frameworks and libraries
8. **Navigation**: Calendar-based journal with one-week view for browsing entries
9. **Multi-location Support**: Single location per user only
10. **AI Privacy**: Server-side AI processing; user data will NOT be sent for AI model training
11. **Technology Stack**: Laravel + MySQL backend, Inertia.js, Tailwind CSS, React frontend, OpenRouter.ai for AI connectivity, self-hosted deployment
12. **UI Layout**: Full-width calendar view with search bar in navigation; search opens a popup modal for queries and results
13. **Entry Limit Behavior**: No action taken when users reach 50-entry limit in MVP
14. **Entry Fields**: Only title and text fields (no additional metadata like weather or mood)
15. **AI Search Promotion**: Static "Ask AI to find..." text in navigation
16. **Week Start Day**: Monday (fixed, no user preference option)
17. **Week Navigation**: Two buttons (previous/next) for cycling through weeks only
18. **Date Restrictions**: Users can only create entries for past or present dates (no future planning)
19. **Date/Time Handling**: Date component only, no time tracking
20. **Error Handling**: Simple "Try again" message for AI search failures
21. **Data Export**: No data export functionality in MVP

### Matched Recommendations

1. **Use general-purpose AI for natural language queries** - Accepted; MVP will use OpenRouter.ai without custom training to handle queries like "when did I plant tomatoes?"
2. **Implement email verification and password reset** - Accepted; these security features are included in MVP authentication
3. **Define clear success metrics** - Accepted; specified as 80% of users performing 3+ searches, enabling precise tracking
4. **Set reasonable entry limits** - Accepted; 50 entries per user balances MVP simplicity with usability
5. **Calendar-based navigation as fallback** - Accepted; week-view calendar provides chronological browsing when AI search isn't used
6. **Single location for MVP** - Accepted; simplifies data model while allowing future expansion
7. **Server-side AI processing with privacy controls** - Accepted; ensures data security and prevents training data usage
8. **Store dates without time components** - Accepted; appropriate for gardening activities that are date-based
9. **Simple error messaging for AI failures** - Accepted; "Try again" message provides basic user feedback
10. **Rapid development using existing frameworks** - Accepted; Laravel/React stack with Inertia.js enables 6-week timeline

## PRD Planning Summary

### Product Overview
GreenThumb is a web-based gardener's journal that solves the problem of searching through historical entries by implementing AI-powered natural language search. 
The core user pain point is the tedious "clicking through" multiple months to find specific information, which discourages journal maintenance.

### Core Functional Requirements

#### User Authentication
- Email/password registration and login
- Email verification for new accounts
- Password reset functionality
- Session management for authenticated users

#### Journal Entry Management
- Modal popup for creating/editing entries
- Title input field (required)
- Text area for free-form content (required)
- Save and Cancel buttons
- Maximum 50 entries per user account
- Entries restricted to past/present dates only (no future planning)
- Date-only storage (no time component)

#### Calendar Interface
- Week-view calendar display (Monday start)
- Full-width calendar layout
- Navigation wit Previous/next week buttons, and current week highlight ( October 7-13, 2025)
- Visual display of entries on corresponding dates
- Direct access to entries by clicking calendar dates
- Adding new entries via Button on calendar dates
- Edit/Delete options for existing entries

#### AI-Powered Search
- Natural language query processing via OpenRouter.ai
- Button for search in main navigation
- Modal popup interface for search queries and results
- Server-side AI processing
- User data excluded from AI model training
- Spinner/loading indicator during AI processing
- Display of relevant entries based on AI results
- Basic error handling with "Try again" message
- Static "Ask AI to find..." prompt text in navigation

### Key User Stories

1. **As a gardener**, I want to quickly find past entries about specific plants or activities without clicking through months, so I can reference important information efficiently.

2. **As a user**, I want to ask natural questions like "when did I water the tomatoes?" and get relevant entry results, so I don't need to remember exact dates.

3. **As a journal keeper**, I want to view my entries in a weekly calendar format, so I can see my gardening activities in chronological context.

4. **As a new user**, I want to create an account with email verification, so my journal data is secure and accessible only to me.

5. **As a regular user**, I want to add, edit, and delete journal entries with titles and observations, so I can maintain an accurate gardening record.

### Technical Architecture

**Backend**: Laravel framework with MySQL database
**Frontend**: React with Inertia.js for seamless SPA experience
**Styling**: Tailwind CSS for responsive design
**AI Integration**: OpenRouter.ai API for natural language processing
**Deployment**: Self-hosted infrastructure
**Timeline**: 6-week development cycle

### Database Schema (High-Level)

#### Users Table
- id, name, email, password, email_verified_at, timestamps

#### Entries Table
- id, user_id, title, content (text), entry_date (date), created_at, updated_at
- Foreign key to users table
- Index on user_id and entry_date for query performance

#### Search Analytics Table (for tracking success metrics)
- id, user_id, query, results_count, created_at
- Tracks each AI search to measure 80% / 3+ searches goal

### Success Criteria & Measurement

**Primary Metric**: 80% of users perform at least 3 AI searches
- Track search queries per user in database
- Dashboard/analytics to monitor adoption rates
- Daily/weekly reporting on search feature usage

**Secondary Metrics** (implied):
- User registration and retention rates
- Average entries per active user
- Search success rate (queries returning results vs. failures)
- Entry creation frequency

### UI/UX Specifications

#### Navigation Bar
- Search bar with "Ask AI to find..." placeholder text
- User account menu
- Responsive design for various screen sizes

#### Calendar View
- Monday-start week grid
- Previous/Next week buttons
- Visual indicators for dates with entries
- Click date to view/create entries

#### Search Modal
- Triggered by clicking search bar
- Input field for natural language queries
- Results display within modal
- "Try again" message on AI failures
- Close button to return to calendar

#### Entry Form
- Title field (required)
- Text area for free-form content (required)
- Date selector (past/present dates only)
- Save/Cancel buttons
- Edit and Delete options for existing entries

### Privacy & Security
- Server-side AI processing only
- No user data sent for model training purposes
- Email verification required
- Secure password storage (Laravel hashing)
- User data isolation (users can only access their own entries)
- Self-hosted deployment for data control

### Out of Scope for MVP
- Categories, tags, or entry classification
- Photo/video/file attachments
- Sharing entries with other users
- Social features or collaboration
- External platform integrations (weather APIs, gardening databases)
- Mobile native applications
- Data export functionality
- Future-dated entries or planning features
- Multiple garden/location support per user
- Advanced search filters or sorting
- Entry limit warnings or upgrade paths

## Unresolved Issues

1. **Entry Limit UX**: When users reach 50 entries, the system has no defined behavior. Should it prevent new entries, show a warning, or require manual deletion of old entries? This will cause user friction and should be addressed before launch.

2. **AI Search Result Format**: How should search results be displayed in the modal? As a list of entry titles with dates? Full entry previews? Highlighted excerpts? This affects both UX and implementation complexity.

3. **Search Result Interaction**: Can users click search results to jump directly to those entries in the calendar? This would improve usability but requires additional navigation logic.

4. **Entry Limit Tracking**: Should users see their current entry count (e.g., "23/50 entries")? This provides transparency but wasn't specified.

5. **OpenRouter.ai Model Selection**: Which specific AI model will be used via OpenRouter.ai? Different models have varying costs, capabilities, and response times that impact both budget and UX.

6. **Email Verification Flow**: What happens if users don't verify their email? Can they still use the journal, or is access blocked? This affects onboarding friction.

7. **Search Analytics Implementation**: How will the system track "unique users performing 3+ searches"? Should this be a lifetime count or rolling time period (weekly/monthly)?

8. **Calendar Empty States**: What should users see on the calendar for weeks with no entries? How should the system encourage first-time users to create their first entry?

9. **Responsive Design Details**: How should the full-width calendar adapt to mobile browsers (since mobile apps are out of scope, but mobile web access is likely)?

10. **Error Logging**: Beyond showing "Try again" to users, should AI search failures be logged for monitoring and debugging? This affects reliability tracking.
