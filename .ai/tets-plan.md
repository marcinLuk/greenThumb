1. Executive Summary

This comprehensive test plan covers all testing activities for the GreenThumb MVP, a Laravel 12-based gardener's journal application with AI-powered search capabilities. The plan addresses all functional requirements, user stories,
and acceptance criteria defined in the PRD, utilizing Laravel's built-in testing framework with PHPUnit.

Testing Timeline: Aligned with development sprints, with continuous testing throughout development and comprehensive regression testing before release.

Primary Success Criteria:
- 95%+ code coverage for critical business logic
- All user stories pass acceptance criteria
- Zero critical/high-severity bugs at launch
- AI search response time <3 seconds (95th percentile)

  ---
2. Test Environment Setup

2.1 Testing Stack

- Framework: PHPUnit (Laravel's default testing framework)
- Database: In-memory SQLite for unit/feature tests, MySQL for integration tests
- Browser Testing: Laravel Dusk (for end-to-end testing)
- API Testing: Laravel HTTP testing utilities
- Mocking: Mockery (included with Laravel)
- Factories & Seeders: Laravel Factories for test data generation

2.2 Test Data Strategy

// Required Factories
- UserFactory (with email verified/unverified states)
- JournalEntryFactory (with past/present dates)
- SearchAnalyticsFactory (for metrics testing)

// Database Seeders
- TestUserSeeder (creates users with various states)
- JournalEntrySeeder (creates entries for calendar testing)

2.3 Environment Configuration

Test environment variables should be configured for SQLite testing, synchronous queue processing, log-based mail handling, and mock OpenRouter API key.

  ---
3. Test Organization Structure

Tests are organized into the following categories:
- **Unit/**: Model tests, Service tests, Helper tests
- **Feature/**: Auth tests, JournalEntry tests, Calendar tests, Search tests, Livewire component tests
- **Integration/**: OpenRouter integration tests, Email delivery tests, End-to-end journey tests
- **Browser/**: Registration flow tests, Journal entry flow tests, AI search flow tests, Mobile responsiveness tests

  ---
4. Unit Tests

4.1 Model Tests

4.1.1 User Model Test (tests/Unit/Models/UserTest.php)

Purpose: Verify User model behavior, relationships, and business logic

Test Cases:

| Test Case ID | Test Description                           | Expected Outcome                    |
  |--------------|--------------------------------------------|-------------------------------------|
| UT-USER-001  | User can be created with valid attributes  | User instance created successfully  |
| UT-USER-002  | Password is hashed on creation             | Password stored as bcrypt hash      |
| UT-USER-003  | User has many journal entries relationship | Returns JournalEntry collection     |
| UT-USER-004  | User can check email verification status   | Returns correct boolean value       |
| UT-USER-005  | User can check entry count limit (50 max)  | Returns true/false based on count   |
| UT-USER-006  | Soft deletes work correctly                | User marked as deleted, not removed |
| UT-USER-007  | User search analytics relationship         | Returns SearchAnalytics collection  |

4.1.2 JournalEntry Model Test (tests/Unit/Models/JournalEntryTest.php)

Test Cases:

| Test Case ID | Test Description                            | Expected Outcome                  |
  |--------------|---------------------------------------------|-----------------------------------|
| UT-ENTRY-001 | Entry can be created with valid data        | Entry created successfully        |
| UT-ENTRY-002 | Entry belongs to user relationship          | Returns User instance             |
| UT-ENTRY-003 | Entry date is cast to Carbon instance       | Date is Carbon object             |
| UT-ENTRY-004 | Entry date has no time component            | Time is 00:00:00                  |
| UT-ENTRY-005 | Entry validation prevents future dates      | Validation fails for future dates |
| UT-ENTRY-006 | Entry title is required                     | Validation fails without title    |
| UT-ENTRY-007 | Entry content is required                   | Validation fails without content  |
| UT-ENTRY-008 | Entry scope for date range works            | Returns filtered entries          |
| UT-ENTRY-009 | Entry scope for user isolation works        | Only returns user's entries       |
| UT-ENTRY-010 | Entry formatted date returns correct format | Returns "October 7, 2025" format  |

4.1.3 SearchAnalytics Model Test (tests/Unit/Models/SearchAnalyticsTest.php)

Test Cases:

| Test Case ID     | Test Description                   | Expected Outcome          |
  |------------------|------------------------------------|---------------------------|
| UT-ANALYTICS-001 | Analytics record created on search | Record saved to database  |
| UT-ANALYTICS-002 | Analytics belongs to user          | Returns User instance     |
| UT-ANALYTICS-003 | Query text is stored correctly     | Query text matches input  |
| UT-ANALYTICS-004 | Results count is stored correctly  | Results count matches     |
| UT-ANALYTICS-005 | Timestamp is auto-generated        | Created_at populated      |
| UT-ANALYTICS-006 | Scope for user searches works      | Returns user's searches   |
| UT-ANALYTICS-007 | Scope for date range works         | Returns searches in range |

4.2 Service Tests

4.2.1 OpenRouterService Test (tests/Unit/Services/OpenRouterServiceTest.php)

● Purpose: Verify OpenRouter API integration service logic

Test Cases:

| Test Case ID   | Test Description                         | Expected Outcome                  | Mocking Required  |
  |----------------|------------------------------------------|-----------------------------------|-------------------|
| UT-SERVICE-001 | Service constructs API request correctly | Request has correct headers, body | Yes - HTTP Client |
| UT-SERVICE-002 | Service handles successful API response  | Returns parsed response data      | Yes - HTTP Client |
| UT-SERVICE-003 | Service handles API timeout              | Throws appropriate exception      | Yes - HTTP Client |
| UT-SERVICE-004 | Service handles 4xx errors               | Throws appropriate exception      | Yes - HTTP Client |
| UT-SERVICE-005 | Service handles 5xx errors               | Throws appropriate exception      | Yes - HTTP Client |
| UT-SERVICE-006 | Service includes user data in context    | Request body contains entries     | Yes - HTTP Client |
| UT-SERVICE-007 | Service excludes training flag           | Request includes training opt-out | Yes - HTTP Client |
| UT-SERVICE-008 | Service logs requests for analytics      | Analytics record created          | No                |
| UT-SERVICE-009 | Service validates API key presence       | Throws exception if missing       | No                |
| UT-SERVICE-010 | Service formats entries for context      | Entries formatted as expected     | No                |

4.2.2 SearchService Test (tests/Unit/Services/SearchServiceTest.php)

Test Cases:

| Test Case ID  | Test Description                     | Expected Outcome                     |
  |---------------|--------------------------------------|--------------------------------------|
| UT-SEARCH-001 | Service orchestrates search flow     | Calls OpenRouter and returns results |
| UT-SEARCH-002 | Service filters relevant entries     | Returns only matching entries        |
| UT-SEARCH-003 | Service creates analytics record     | SearchAnalytics record exists        |
| UT-SEARCH-004 | Service handles empty results        | Returns empty array gracefully       |
| UT-SEARCH-005 | Service validates query length       | Rejects empty/too short queries      |
| UT-SEARCH-006 | Service sanitizes user input         | Removes malicious characters         |
| UT-SEARCH-007 | Service respects user data isolation | Only searches user's entries         |

  ---
5. Feature Tests

5.1 Authentication Tests

5.1.1 Registration Test (tests/Feature/Auth/RegistrationTest.php)

User Story: US-001 - User RegistrationPriority: Critical

Test Cases:

| Test Case ID | Test Description                              | Test Data                     | Expected Outcome                      | HTTP Status    |
  |--------------|-----------------------------------------------|-------------------------------|---------------------------------------|----------------|
| FT-REG-001   | User can register with valid data             | Valid email, 8+ char password | User created, verification email sent | 302 (redirect) |
| FT-REG-002   | Registration validates email format           | Invalid email format          | Validation error displayed            | 422            |
| FT-REG-003   | Registration requires password min length     | 7 character password          | Validation error displayed            | 422            |
| FT-REG-004   | Registration requires password confirmation   | Mismatched passwords          | Validation error displayed            | 422            |
| FT-REG-005   | Registration prevents duplicate emails        | Existing email address        | Error message displayed               | 422            |
| FT-REG-006   | Registration creates unverified user          | Valid registration            | User.email_verified_at is null        | 302            |
| FT-REG-007   | Registration sends verification email         | Valid registration            | Email queued/sent                     | 302            |
| FT-REG-008   | Registration redirects to verification notice | Valid registration            | Redirects to verify notice page       | 302            |

5.1.2 Email Verification Test (tests/Feature/Auth/EmailVerificationTest.php)

User Story: US-002 - Email VerificationPriority: Critical

Test Cases:

| Test Case ID  | Test Description                        | Expected Outcome                       | HTTP Status |
  |---------------|-----------------------------------------|----------------------------------------|-------------|
| FT-VERIFY-001 | User can verify email with valid link   | Email verified, redirect to dashboard  | 302         |
| FT-VERIFY-002 | Verification marks user as verified     | User.email_verified_at populated       | 200         |
| FT-VERIFY-003 | Expired verification link shows error   | Error message, redirect to resend page | 403         |
| FT-VERIFY-004 | Invalid verification link shows error   | Error message displayed                | 403         |
| FT-VERIFY-005 | User can request new verification email | New email sent                         | 302         |
| FT-VERIFY-006 | Already verified user cannot reverify   | Redirect to dashboard                  | 302         |
| FT-VERIFY-007 | Unverified user cannot access dashboard | Redirect to verification notice        | 302         |

5.1.3 Login Test (tests/Feature/Auth/LoginTest.php)

User Story: US-003 - User LoginPriority: Critical

Test Cases:

| Test Case ID | Test Description                               | Test Data              | Expected Outcome                       | HTTP Status |
  |--------------|------------------------------------------------|------------------------|----------------------------------------|-------------|
| FT-LOGIN-001 | Verified user can login with valid credentials | Correct email/password | Session created, redirect to dashboard | 302         |
| FT-LOGIN-002 | Login fails with incorrect password            | Wrong password         | Error message, no session              | 422         |
| FT-LOGIN-003 | Login fails with non-existent email            | Non-existent email     | Error message, no session              | 422         |
| FT-LOGIN-004 | Unverified user cannot login                   | Unverified account     | Redirect to verification notice        | 302         |
| FT-LOGIN-005 | Login creates user session                     | Valid credentials      | Auth session exists                    | 302         |
| FT-LOGIN-006 | Login rate limiting works                      | 6+ failed attempts     | Too many attempts error                | 429         |
| FT-LOGIN-007 | Remember me functionality works                | Remember me checked    | Remember token created                 | 302         |

5.1.4 Password Reset Test (tests/Feature/Auth/PasswordResetTest.php)

User Stories: US-004, US-005 - Password ResetPriority: High

Test Cases:

| Test Case ID | Test Description                            | Expected Outcome                    | HTTP Status |
  |--------------|---------------------------------------------|-------------------------------------|-------------|
| FT-RESET-001 | User can request password reset             | Email sent with reset link          | 302         |
| FT-RESET-002 | Reset request doesn't reveal user existence | Generic success message             | 302         |
| FT-RESET-003 | Reset link is time-limited (60 minutes)     | Expired link shows error            | 422         |
| FT-RESET-004 | User can reset password with valid link     | Password updated, redirect to login | 302         |
| FT-RESET-005 | Reset validates password minimum length     | Error for short password            | 422         |
| FT-RESET-006 | Reset requires password confirmation        | Error for mismatch                  | 422         |
| FT-RESET-007 | Reset link is single-use only               | Second use shows error              | 422         |
| FT-RESET-008 | User can login with new password            | Login successful                    | 302         |

5.1.5 Logout Test (tests/Feature/Auth/LogoutTest.php)

User Story: US-006 - User LogoutPriority: Medium

Test Cases:

| Test Case ID  | Test Description              | Expected Outcome                     | HTTP Status |
  |---------------|-------------------------------|--------------------------------------|-------------|
| FT-LOGOUT-001 | Authenticated user can logout | Session destroyed, redirect to login | 302         |
| FT-LOGOUT-002 | Logout invalidates session    | Cannot access protected routes       | 401         |
| FT-LOGOUT-003 | Logout removes remember token | Remember token cleared               | 302         |

5.2 Journal Entry Management Tests

5.2.1 Create Entry Test (tests/Feature/JournalEntry/CreateEntryTest.php)

User Story: US-009 - Create New Journal EntryPriority: Critical

Test Cases:

| Test Case ID  | Test Description                         | Test Data                 | Expected Outcome                 | HTTP Status |
  |---------------|------------------------------------------|---------------------------|----------------------------------|-------------|
| FT-CREATE-001 | User can create entry with valid data    | Title, content, past date | Entry saved, appears on calendar | 201         |
| FT-CREATE-002 | Entry creation requires title            | Missing title             | Validation error                 | 422         |
| FT-CREATE-003 | Entry creation requires content          | Missing content           | Validation error                 | 422         |
| FT-CREATE-004 | Entry creation prevents future dates     | Future date               | Validation error                 | 422         |
| FT-CREATE-005 | Entry allows today's date                | Today's date              | Entry created                    | 201         |
| FT-CREATE-006 | Entry allows past dates                  | Past date                 | Entry created                    | 201         |
| FT-CREATE-007 | Entry enforces 50 entry limit            | 50th entry already exists | Error message                    | 422         |
| FT-CREATE-008 | Entry date has no time component         | Any valid date            | Time is 00:00:00                 | 201         |
| FT-CREATE-009 | Unauthenticated user cannot create entry | N/A                       | Redirect to login                | 401         |
| FT-CREATE-010 | Entry is isolated to user                | User A creates entry      | User B cannot see it             | 201         |

5.2.2 Update Entry Test (tests/Feature/JournalEntry/UpdateEntryTest.php)

User Story: US-011 - Edit Existing Journal EntryPriority: High

Test Cases:

| Test Case ID  | Test Description                        | Expected Outcome          | HTTP Status |
  |---------------|-----------------------------------------|---------------------------|-------------|
| FT-UPDATE-001 | User can update own entry               | Entry updated in database | 200         |
| FT-UPDATE-002 | Update validates required title         | Validation error          | 422         |
| FT-UPDATE-003 | Update validates required content       | Validation error          | 422         |
| FT-UPDATE-004 | User cannot update another user's entry | Forbidden error           | 403         |
| FT-UPDATE-005 | Update prevents future dates            | Validation error          | 422         |
| FT-UPDATE-006 | Update allows changing date to past     | Entry date updated        | 200         |
| FT-UPDATE-007 | Unauthenticated user cannot update      | Redirect to login         | 401         |

5.2.3 Delete Entry Test (tests/Feature/JournalEntry/DeleteEntryTest.php)

User Story: US-012 - Delete Journal EntryPriority: High

Test Cases:

| Test Case ID  | Test Description                         | Expected Outcome            | HTTP Status |
  |---------------|------------------------------------------|-----------------------------|-------------|
| FT-DELETE-001 | User can delete own entry                | Entry removed from database | 204         |
| FT-DELETE-002 | User cannot delete another user's entry  | Forbidden error             | 403         |
| FT-DELETE-003 | Deleted entry doesn't appear on calendar | Entry not in calendar view  | 204         |
| FT-DELETE-004 | Unauthenticated user cannot delete       | Redirect to login           | 401         |
| FT-DELETE-005 | Delete is permanent (no soft delete)     | Entry not in database       | 204         |

5.2.4 View Entry Test (tests/Feature/JournalEntry/ViewEntryTest.php)

User Story: US-010 - View Existing Journal EntryPriority: High

Test Cases:

| Test Case ID | Test Description                             | Expected Outcome     | HTTP Status |
  |--------------|----------------------------------------------|----------------------|-------------|
| FT-VIEW-001  | User can view own entry details              | Entry data displayed | 200         |
| FT-VIEW-002  | User cannot view another user's entry        | Forbidden error      | 403         |
| FT-VIEW-003  | View displays title, content, and date       | All fields shown     | 200         |
| FT-VIEW-004  | Multiple entries on same date are accessible | All entries shown    | 200         |
| FT-VIEW-005  | Unauthenticated user cannot view entries     | Redirect to login    | 401         |

5.3 Calendar Interface Tests

5.3.1 Weekly View Test (tests/Feature/Calendar/WeeklyViewTest.php)

User Story: US-007 - View Weekly CalendarPriority: Critical

Test Cases:

| Test Case ID | Test Description                          | Expected Outcome             | HTTP Status |
  |--------------|-------------------------------------------|------------------------------|-------------|
| FT-CAL-001   | Calendar displays current week by default | Current week shown           | 200         |
| FT-CAL-002   | Calendar starts week on Monday            | Monday is first column       | 200         |
| FT-CAL-003   | Calendar shows 7 days (Mon-Sun)           | 7 day columns displayed      | 200         |
| FT-CAL-004   | Calendar displays date range header       | "October 7-13, 2025" shown   | 200         |
| FT-CAL-005   | Dates with entries show visual indicators | Indicator/badge visible      | 200         |
| FT-CAL-006   | Dates without entries have no indicators  | No indicator shown           | 200         |
| FT-CAL-007   | Entry titles display on calendar dates    | Title text visible           | 200         |
| FT-CAL-008   | Calendar is full-width layout             | Full width class applied     | 200         |
| FT-CAL-009   | Multiple entries on same date are shown   | All entry indicators visible | 200         |

5.3.2 Navigation Test (tests/Feature/Calendar/NavigationTest.php)

User Story: US-008 - Navigate Calendar WeeksPriority: High

Test Cases:

| Test Case ID | Test Description                         | Expected Outcome                | HTTP Status |
  |--------------|------------------------------------------|---------------------------------|-------------|
| FT-NAV-001   | Previous week button loads previous week | Previous week displayed         | 200         |
| FT-NAV-002   | Next week button loads next week         | Next week displayed             | 200         |
| FT-NAV-003   | Navigation updates date range header     | Header shows new week range     | 200         |
| FT-NAV-004   | Navigation loads correct entries         | Entries for selected week shown | 200         |
| FT-NAV-005   | Navigation maintains session state       | User remains authenticated      | 200         |
| FT-NAV-006   | Navigation works without page reload     | Livewire updates component      | 200         |

5.4 AI Search Tests

5.4.1 AI Search Test (tests/Feature/Search/AISearchTest.php)

User Stories: US-013, US-014, US-015 - AI SearchPriority: Critical

Test Cases:

| Test Case ID  | Test Description                         | Test Query                   | Expected Outcome              | HTTP Status |
  |---------------|------------------------------------------|------------------------------|-------------------------------|-------------|
| FT-SEARCH-001 | User can perform natural language search | "when did I plant tomatoes?" | Results returned              | 200         |
| FT-SEARCH-002 | Search returns relevant entries          | "tomato watering"            | Tomato-related entries        | 200         |
| FT-SEARCH-003 | Search displays AI-generated summary     | Any valid query              | Summary text shown            | 200         |
| FT-SEARCH-004 | Search displays list of relevant entries | Any valid query              | Entry list shown              | 200         |
| FT-SEARCH-005 | Search with no results shows message     | Irrelevant query             | "No results" message          | 200         |
| FT-SEARCH-006 | Search creates analytics record          | Any valid query              | Analytics saved               | 200         |
| FT-SEARCH-007 | Search validates query length            | Empty string                 | Validation error              | 422         |
| FT-SEARCH-008 | Search is user-isolated                  | User A's query               | Only user A's entries         | 200         |
| FT-SEARCH-009 | Search displays loading indicator        | Any valid query              | Spinner shows during API call | 200         |
| FT-SEARCH-010 | Unauthenticated user cannot search       | N/A                          | Redirect to login             | 401         |
| FT-SEARCH-011 | Search excludes data from AI training    | Any valid query              | Training flag is false        | 200         |

5.4.2 Search Error Handling Test (tests/Feature/Search/SearchErrorHandlingTest.php)

User Story: US-016 - Handle AI Search ErrorsPriority: High

Test Cases:

| Test Case ID      | Test Description                  | Simulated Error      | Expected Outcome       | HTTP Status |
  |-------------------|-----------------------------------|----------------------|------------------------|-------------|
| FT-SEARCH-ERR-001 | Handle API timeout                | Timeout exception    | "Try again" message    | 408         |
| FT-SEARCH-ERR-002 | Handle API 500 error              | 500 response         | "Try again" message    | 500         |
| FT-SEARCH-ERR-003 | Handle API 429 rate limit         | 429 response         | Rate limit message     | 429         |
| FT-SEARCH-ERR-004 | Handle network connectivity error | Connection exception | "Try again" message    | 503         |
| FT-SEARCH-ERR-005 | Handle invalid API response       | Malformed JSON       | "Try again" message    | 500         |
| FT-SEARCH-ERR-006 | User can retry after error        | Error then success   | Results shown on retry | 200         |
| FT-SEARCH-ERR-007 | Modal remains open after error    | Any error            | Modal still visible    | 408/500     |

5.5 Livewire Component Tests

5.5.1 Calendar Component Test (tests/Feature/Livewire/CalendarComponentTest.php)

Purpose: Test calendar Livewire component functionality

Test Cases:

| Test Case ID  | Test Description                         | Expected Outcome             |
  |---------------|------------------------------------------|------------------------------|
| FT-LW-CAL-001 | Component renders with current week      | Component loads successfully |
| FT-LW-CAL-002 | Component loads entries for current week | Entries passed to view       |
| FT-LW-CAL-003 | previousWeek() method updates week       | Week property decremented    |
| FT-LW-CAL-004 | nextWeek() method updates week           | Week property incremented    |
| FT-LW-CAL-005 | Component emits entry-created event      | Event listener triggered     |
| FT-LW-CAL-006 | Component refreshes on entry update      | Entries re-loaded            |
| FT-LW-CAL-007 | Component displays date range correctly  | Computed property accurate   |

5.5.2 Entry Modal Component Test (tests/Feature/Livewire/EntryModalComponentTest.php)

Purpose: Test entry creation/editing modal component

Test Cases:

| Test Case ID    | Test Description                       | Expected Outcome              |
  |-----------------|----------------------------------------|-------------------------------|
| FT-LW-MODAL-001 | Modal opens for new entry              | Modal state is open           |
| FT-LW-MODAL-002 | Modal pre-populates for edit           | Fields filled with entry data |
| FT-LW-MODAL-003 | Save button disabled when invalid      | Button has disabled attribute |
| FT-LW-MODAL-004 | Validation errors display in real-time | Error messages shown          |
| FT-LW-MODAL-005 | Cancel button closes modal             | Modal state is closed         |
| FT-LW-MODAL-006 | Save creates entry and closes modal    | Entry saved, modal closed     |
| FT-LW-MODAL-007 | Date picker restricts future dates     | Future dates disabled         |

5.5.3 Search Modal Component Test (tests/Feature/Livewire/SearchModalComponentTest.php)

Purpose: Test AI search modal component

Test Cases:

| Test Case ID     | Test Description                    | Expected Outcome          |
  |------------------|-------------------------------------|---------------------------|
| FT-LW-SEARCH-001 | Modal opens from navigation         | Modal state is open       |
| FT-LW-SEARCH-002 | Placeholder text displays           | "Ask AI to find..." shown |
| FT-LW-SEARCH-003 | Submit triggers search              | Loading state activated   |
| FT-LW-SEARCH-004 | Results display in modal            | Results rendered          |
| FT-LW-SEARCH-005 | Loading spinner shows during search | Spinner visible           |
| FT-LW-SEARCH-006 | Error message displays on failure   | Error text shown          |
| FT-LW-SEARCH-007 | User can perform multiple searches  | Multiple queries work     |

  ---
6. Integration Tests

6.1 OpenRouter Integration Test (tests/Integration/OpenRouterIntegrationTest.php)

Purpose: Test actual API integration with OpenRouter (requires valid API key)Environment: Staging/QA environment only

Test Cases:

| Test Case ID | Test Description                     | Expected Outcome           | Timeout |
  |--------------|--------------------------------------|----------------------------|---------|
| IT-API-001   | Real API call returns valid response | Response structure correct | 10s     |
| IT-API-002   | API respects training opt-out flag   | Request includes flag      | 10s     |
| IT-API-003   | API handles user context correctly   | Relevant results returned  | 10s     |
| IT-API-004   | API response time is acceptable      | Response < 5 seconds       | 10s     |
| IT-API-005   | API handles large entry sets         | 50 entries processed       | 15s     |

Note: These tests should be tagged @integration and run separately from unit/feature tests.

6.2 Email Delivery Test (tests/Integration/EmailDeliveryTest.php)

Purpose: Test actual email delivery (using Mailtrap or similar in staging)

Test Cases:

| Test Case ID | Test Description                         | Expected Outcome             |
  |--------------|------------------------------------------|------------------------------|
| IT-EMAIL-001 | Verification email delivers successfully | Email received in inbox      |
| IT-EMAIL-002 | Password reset email delivers            | Email received in inbox      |
| IT-EMAIL-003 | Email contains correct verification link | Link is valid and functional |
| IT-EMAIL-004 | Email formatting is correct              | HTML renders properly        |
| IT-EMAIL-005 | Email sender is correct                  | From address is correct      |

6.3 End-to-End User Journey Test (tests/Integration/EndToEndUserJourneyTest.php)

Purpose: Test complete user workflows from registration to AI search

Test Scenarios:

| Test Case ID | User Journey               | Steps                                               | Success Criteria       |
  |--------------|----------------------------|-----------------------------------------------------|------------------------|
| IT-E2E-001   | New user onboarding        | Register → Verify → Login → Create entry → Search   | All steps complete     |
| IT-E2E-002   | Daily journal maintenance  | Login → View calendar → Create 3 entries → Logout   | Entries saved          |
| IT-E2E-003   | Historical search workflow | Login → Navigate past weeks → Search → View results | Results accurate       |
| IT-E2E-004   | Entry lifecycle            | Create → View → Edit → Search → Delete              | All operations succeed |

  ---
7. Browser Tests (Laravel Dusk)

7.1 Registration Flow Test (tests/Browser/RegistrationFlowTest.php)

Purpose: Test registration user interface and interactions

Test Cases:

| Test Case ID | Browser Actions                    | Assertions                  |
  |--------------|------------------------------------|-----------------------------|
| BT-REG-001   | Fill registration form, submit     | User redirected, email sent |
| BT-REG-002   | Submit with missing fields         | Validation messages appear  |
| BT-REG-003   | Click verification link from email | Account activated           |
| BT-REG-004   | Attempt duplicate registration     | Error message shown         |

7.2 Journal Entry Flow Test (tests/Browser/JournalEntryFlowTest.php)

Test Cases:

| Test Case ID | Browser Actions                       | Assertions                     |
  |--------------|---------------------------------------|--------------------------------|
| BT-ENTRY-001 | Click calendar date, fill modal, save | Entry appears on calendar      |
| BT-ENTRY-002 | Click entry, edit, save               | Entry updated on calendar      |
| BT-ENTRY-003 | Click delete, confirm                 | Entry removed from calendar    |
| BT-ENTRY-004 | Select future date                    | Date picker prevents selection |
| BT-ENTRY-005 | Submit empty form                     | Save button remains disabled   |

7.3 AI Search Flow Test (tests/Browser/AISearchFlowTest.php)

Test Cases:

| Test Case ID  | Browser Actions                   | Assertions                      |
  |---------------|-----------------------------------|---------------------------------|
| BT-SEARCH-001 | Click search, enter query, submit | Results display in modal        |
| BT-SEARCH-002 | Observe loading state             | Spinner appears during API call |
| BT-SEARCH-003 | Click result entry                | Entry details shown             |
| BT-SEARCH-004 | Trigger API error (mock)          | "Try again" message appears     |
| BT-SEARCH-005 | Perform multiple searches         | Modal remains functional        |

7.4 Mobile Responsiveness Test (tests/Browser/MobileResponsivenessTest.php)

User Story: US-022 - Mobile Web AccessPurpose: Test mobile viewport behavior

Test Cases:

| Test Case ID  | Viewport Size       | Actions            | Assertions              |
  |---------------|---------------------|--------------------|-------------------------|
| BT-MOBILE-001 | 375x667 (iPhone SE) | View calendar      | Calendar is responsive  |
| BT-MOBILE-002 | 375x667             | Open entry modal   | Modal fits screen       |
| BT-MOBILE-003 | 375x667             | Navigate weeks     | Buttons accessible      |
| BT-MOBILE-004 | 375x667             | Perform search     | Search modal usable     |
| BT-MOBILE-005 | 390x844 (iPhone 13) | Full user journey  | All features work       |
| BT-MOBILE-006 | 360x640 (Android)   | Touch interactions | Taps register correctly |

  ---
8. Security Tests

8.1 Authentication Security Test (tests/Feature/Security/AuthenticationSecurityTest.php)

User Story: US-021 - Data Privacy Compliance

Test Cases:

| Test Case ID | Security Test                           | Expected Outcome              |
  |--------------|-----------------------------------------|-------------------------------|
| ST-AUTH-001  | Password is hashed in database          | bcrypt hash stored            |
| ST-AUTH-002  | Session tokens are regenerated on login | CSRF token changes            |
| ST-AUTH-003  | Failed login rate limiting works        | Lockout after 5 attempts      |
| ST-AUTH-004  | Session expires after inactivity        | User logged out after timeout |
| ST-AUTH-005  | Password reset tokens expire            | Token invalid after 60 min    |
| ST-AUTH-006  | XSS prevention in input fields          | Scripts are escaped           |
| ST-AUTH-007  | CSRF protection on forms                | CSRF token required           |

8.2 Data Isolation Test (tests/Feature/Security/DataIsolationTest.php)

Test Cases:

| Test Case ID | Security Test                                   | Expected Outcome            |
  |--------------|-------------------------------------------------|-----------------------------|
| ST-DATA-001  | User A cannot view User B's entries             | 403 Forbidden               |
| ST-DATA-002  | User A cannot edit User B's entries             | 403 Forbidden               |
| ST-DATA-003  | User A cannot delete User B's entries           | 403 Forbidden               |
| ST-DATA-004  | Search only returns user's own entries          | Results filtered by user_id |
| ST-DATA-005  | Direct API calls to other user's resources fail | Authorization check fails   |
| ST-DATA-006  | SQL injection attempts are prevented            | Query sanitized             |

8.3 AI Privacy Test (tests/Feature/Security/AIPrivacyTest.php)

Test Cases:

| Test Case ID   | Privacy Test                         | Expected Outcome           |
  |----------------|--------------------------------------|----------------------------|
| ST-PRIVACY-001 | AI requests include training opt-out | Flag present in API call   |
| ST-PRIVACY-002 | User data sent server-side only      | No client-side API calls   |
| ST-PRIVACY-003 | Analytics data is anonymized         | PII removed from logs      |
| ST-PRIVACY-004 | Search queries are user-isolated     | No cross-user data leakage |

  ---
9. Performance Tests

9.1 Load Performance Test (tests/Performance/LoadPerformanceTest.php)

Success Metric: AI search response time <3 seconds (95th percentile)

Test Cases:

| Test Case ID | Performance Test            | Target      | Measurement          |
  |--------------|-----------------------------|-------------|----------------------|
| PT-LOAD-001  | Calendar page load time     | < 500ms     | Response time        |
| PT-LOAD-002  | Entry creation response     | < 300ms     | Response time        |
| PT-LOAD-003  | AI search response time     | < 3s (95th) | API latency          |
| PT-LOAD-004  | Week navigation response    | < 200ms     | Livewire update time |
| PT-LOAD-005  | Database query optimization | < 100ms     | Query execution time |

Tools: Laravel Telescope for monitoring, custom performance logging

9.2 Scalability Test

Test Cases:

| Test Case ID | Scenario                  | Load                   | Expected Outcome      |
  |--------------|---------------------------|------------------------|-----------------------|
| PT-SCALE-001 | 50 entries per user       | 1000 users             | All queries < targets |
| PT-SCALE-002 | Concurrent AI searches    | 10 simultaneous        | Queue handles load    |
| PT-SCALE-003 | Calendar with max entries | 50 entries in one week | Renders in < 500ms    |

  ---
10. Acceptance Testing

10.1 User Story Validation

For each user story (US-001 through US-023), create a comprehensive acceptance test that validates ALL acceptance criteria.

10.2 UAT Checklist

Prior to release, manually verify:

- All 23 user stories pass acceptance criteria
- Primary success metric tracking works (3+ searches per user)
- Email delivery works in production environment
- OpenRouter API integration works with production key
- Mobile responsiveness on real devices (iOS Safari, Chrome Android)
- Cross-browser compatibility (Chrome, Firefox, Safari, Edge)
- SSL/HTTPS works correctly
- Privacy policy page displays correctly
- Error pages (404, 500) display correctly
- Performance meets targets in production environment

  ---
11. Test Execution Strategy

11.1 Test Execution Order

1. Phase 1: Unit Tests (All tests must pass before proceeding)
   - Model tests
   - Service tests
   - Helper tests
2. Phase 2: Feature Tests (Run in parallel where possible)
   - Authentication tests
   - Journal entry tests
   - Calendar tests
   - Search tests
   - Livewire component tests
3. Phase 3: Integration Tests (Run in staging environment)
   - API integration tests
   - Email delivery tests
   - End-to-end journey tests
4. Phase 4: Browser Tests (Run in multiple browsers)
   - Registration flow
   - Journal entry flow
   - AI search flow
   - Mobile responsiveness
5. Phase 5: Security & Performance Tests
   - Security tests
   - Performance tests
6. Phase 6: Acceptance Testing
   - User story validation
   - UAT checklist

11.2 Continuous Integration

A GitHub Actions workflow should be configured to run tests automatically on push and pull requests. The workflow should:
- Set up PHP 8.3 environment
- Install dependencies with Composer
- Run unit tests
- Run feature tests
- Run browser tests with Dusk
- Upload coverage reports to Codecov

11.3 Test Data Management

Database Seeders for Testing:

**TestUserSeeder** should create:
- Verified users
- Unverified users
- Users with 50 entries (at limit)
- Users with various entry dates

**JournalEntrySeeder** should create:
- Entries across multiple weeks
- Entries with searchable content
- Entries on same dates
- Entries with edge case dates

11.4 Test Reporting

Coverage Targets:
- Overall code coverage: 80%
- Critical business logic: 95%
- Controllers: 90%
- Models: 95%
- Services: 95%

Reporting Tools:
- PHPUnit code coverage reports (HTML format)
- Laravel Telescope for production monitoring
- Custom analytics dashboard for success metrics

  ---
12. Test Maintenance

12.1 Test Review Schedule

- Daily: Review failed tests in CI pipeline
- Weekly: Review test coverage reports
- Sprint End: Update tests for new features
- Monthly: Review and refactor slow tests

12.2 Test Documentation

Each test class should include:
- Purpose statement
- Related user story reference
- Setup/teardown requirements
- Test data dependencies
- Known limitations
- Appropriate @group tags for test organization

  ---
13. Risk-Based Testing Priority

13.1 Critical (P0) - Must Pass Before Release

- User registration and authentication (US-001, US-002, US-003)
- Email verification (US-002)
- Journal entry CRUD operations (US-009, US-010, US-011, US-012)
- AI search functionality (US-014, US-015)
- Data isolation/security (US-021)
- 50 entry limit enforcement (US-009)

13.2 High (P1) - Should Pass Before Release

- Password reset flow (US-004, US-005)
- Calendar navigation (US-007, US-008)
- Search error handling (US-016)
- Mobile responsiveness (US-022)
- Session management (US-019)

13.3 Medium (P2) - Nice to Have

- Calendar empty states (US-020)
- Entry validation feedback (US-023)
- Performance optimization tests
- Cross-browser compatibility

  ---
14. Defect Management

14.1 Bug Severity Levels

Critical (Sev 1):
- Authentication failures
- Data loss
- Security vulnerabilities
- Application crashes

High (Sev 2):
- Feature not working as specified
- AI search failures
- Email delivery failures

Medium (Sev 3):
- UI/UX issues
- Performance degradation
- Minor validation errors

Low (Sev 4):
- Cosmetic issues
- Documentation errors

14.2 Bug Workflow

1. Discovery: Log bug with reproduction steps
2. Triage: Assign severity and priority
3. Assignment: Assign to developer
4. Fix: Developer fixes and writes regression test
5. Verification: QA verifies fix and regression test
6. Closure: Bug marked as resolved

  ---
15. Success Criteria

The test plan is considered successful when:

✅ Coverage Targets Met:
- 80%+ overall code coverage
- 95%+ coverage for critical paths

✅ All Critical Tests Pass:
- 100% of P0 tests passing
- 95%+ of P1 tests passing

✅ Performance Targets Met:
- AI search < 3s (95th percentile)
- Calendar load < 500ms
- Entry operations < 300ms

✅ User Stories Validated:
- All 23 user stories pass acceptance criteria
- Primary success metric tracking implemented

✅ Security Validated:
- No critical/high security vulnerabilities
- Data isolation confirmed
- Privacy compliance verified

✅ UAT Completed:
- Manual UAT checklist 100% complete
- Stakeholder sign-off received

  ---
16. Appendix

16.1 Test Commands Reference

Standard Laravel testing commands include:
- Run all tests
- Run specific test suites (Unit, Feature)
- Run specific test files or methods
- Run tests with coverage reports
- Run browser tests with Dusk
- Run tests by groups or exclude specific groups

16.2 Mock Data Examples

Use Laravel factories to create test data:
- UserFactory for creating verified/unverified users
- JournalEntryFactory for creating entries with various dates and content
- Support for creating multiple entries at once

16.3 HTTP Mock Examples

Use Laravel's HTTP facade for mocking API responses:
- Mock successful OpenRouter responses
- Mock API timeouts and errors
- Mock rate limiting responses
- Configure different response scenarios for testing

  ---
Document Control

Version: 1.0Created: October 14, 2025Last Updated: October 14, 2025Author: Senior QA EngineerStatus: Draft - Pending Review

Approval:
- QA Lead
- Development Lead
- Product Owner

Change Log:
- v1.0 (2025-10-14): Initial test plan created based on PRD v1.0

  ---
Total Test Cases: 200+Estimated Test Execution Time:
- Automated: ~15 minutes (Unit + Feature)
- Integration: ~30 minutes
- Browser: ~45 minutes
- Manual UAT: ~4 hours

Next Steps:
1. Review and approve test plan
2. Implement factories and seeders
3. Begin test implementation (unit tests first)
4. Set up CI/CD pipeline
5. Configure browser testing environment
