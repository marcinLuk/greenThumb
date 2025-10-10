<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetEntriesByDateRangeRequest;
use App\Http\Requests\GetJournalEntriesRequest;
use App\Http\Requests\StoreJournalEntryRequest;
use App\Http\Requests\UpdateJournalEntryRequest;
use App\Http\Resources\JournalEntryResource;
use App\Models\JournalEntry;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of the authenticated user's journal entries.
     *
     * @param GetJournalEntriesRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(GetJournalEntriesRequest $request): AnonymousResourceCollection
    {
        // Get validated data
        $validated = $request->validated();

        // Build query starting with JournalEntry (global scope auto-applies user filter)
        $query = JournalEntry::query();

        // Apply date range filter if provided
        if (isset($validated['start_date']) || isset($validated['end_date'])) {
            $query->withinDateRange(
                $validated['start_date'] ?? null,
                $validated['end_date'] ?? null
            );
        }

        // Apply sorting
        $sortDirection = $request->getSortDirection();
        $query->sortByDate($sortDirection);

        // Paginate results
        $perPage = $request->getPerPage();
        $entries = $query->paginate($perPage);

        // Return formatted response with pagination metadata
        return JournalEntryResource::collection($entries);
    }

    /**
     * Display the specified journal entry.
     *
     * @param string $id
     * @return JournalEntryResource|\Illuminate\Http\JsonResponse
     */
    public function show(string $id): JournalEntryResource|JsonResponse
    {
        // Validate that ID is numeric
        if (!is_numeric($id)) {
            return response()->json([
                'message' => 'Invalid entry ID format',
            ], 400);
        }

        try {
            $entry = JournalEntry::findOrFail($id);

            // Return formatted entry resource
            return new JournalEntryResource($entry);
        } catch (ModelNotFoundException $e) {
            // Return 404 instead of 403 to prevent entry ID enumeration
            return response()->json([
                'message' => 'Journal entry not found',
            ], 404);
        }
    }

    /**
     * Retrieve journal entries within a specific date range for weekly calendar view.
     *
     * @param GetEntriesByDateRangeRequest $request
     * @return JsonResponse
     */
    public function dateRange(GetEntriesByDateRangeRequest $request): JsonResponse
    {
        // Get validated data
        $validated = $request->validated();

        // Query entries within date range (UserOwnedScope auto-applies user filter)
        $entries = JournalEntry::query()
            ->withinDateRange($validated['start_date'], $validated['end_date'])
            ->orderBy('entry_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        // Format response with data and metadata
        return response()->json([
            'data' => JournalEntryResource::collection($entries),
            'meta' => [
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'total_entries' => $entries->count(),
            ],
        ], 200);
    }

    /**
     * Store a newly created journal entry.
     *
     * Creates a new journal entry for the authenticated user. Enforces a maximum
     * limit of 50 entries per user. The entry must have a date that is today or
     * in the past.
     *
     * @param StoreJournalEntryRequest $request
     * @return JournalEntryResource|JsonResponse
     */
    public function store(StoreJournalEntryRequest $request): JournalEntryResource|JsonResponse
    {
        try {
            $validated = $request->validated();

            $entriesCount = auth()->user()->entriesCount()->first();
            if ($entriesCount && $entriesCount->count >= 50) {
                return response()->json([
                    'message' => 'You have reached the maximum limit of 50 journal entries.',
                ], 403);
            }


            $journalEntry = DB::transaction(function () use ($validated) {
                $data = array_merge($validated, [
                    'user_id' => auth()->id(),
                ]);

                return JournalEntry::create($data);
            });

            return (new JournalEntryResource($journalEntry))
                ->response()
                ->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create journal entry. Please try again.',
            ], 500);
        }
    }

    /**
     * Update the specified journal entry.
     *
     * Updates an existing journal entry for the authenticated user. Users can only
     * update their own entries. The entry date must be today or in the past.
     *
     * @param UpdateJournalEntryRequest $request
     * @param string $id
     * @return JournalEntryResource|JsonResponse
     */
    public function update(UpdateJournalEntryRequest $request, string $id): JournalEntryResource|JsonResponse
    {
        // Validate that ID is numeric
        if (!is_numeric($id)) {
            return response()->json([
                'message' => 'Invalid entry ID format',
            ], 400);
        }

        try {
            // Find the journal entry - UserOwnedScope ensures data isolation
            $entry = JournalEntry::findOrFail($id);

            // Authorize the update action using policy
            $this->authorize('update', $entry);

            // Get validated data
            $validated = $request->validated();

            // Update the entry
            $entry->update($validated);

            // Return the updated entry resource
            return new JournalEntryResource($entry);
        } catch (ModelNotFoundException $e) {
            // Return 404 instead of 403 to prevent entry ID enumeration
            return response()->json([
                'message' => 'Journal entry not found',
            ], 404);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            // This should rarely happen due to UserOwnedScope, but handle it anyway
            return response()->json([
                'message' => 'Journal entry not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update journal entry. Please try again.',
            ], 500);
        }
    }
}
