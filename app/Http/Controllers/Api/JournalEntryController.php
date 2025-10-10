<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetEntriesByDateRangeRequest;
use App\Http\Requests\GetJournalEntriesRequest;
use App\Http\Resources\JournalEntryResource;
use App\Models\JournalEntry;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
}
