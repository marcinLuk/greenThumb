<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetJournalEntriesRequest;
use App\Http\Resources\JournalEntryResource;
use App\Models\JournalEntry;
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
}
