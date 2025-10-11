<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SearchRequest;
use App\Http\Resources\JournalEntryResource;
use App\Models\JournalEntry;
use App\Models\SearchAnalytic;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    /**
     * Search journal entries using natural language query.
     *
     * This endpoint processes search queries and returns relevant journal entries.
     * AI integration point is marked for future enhancement.
     */
    public function search(SearchRequest $request): JsonResponse
    {
        try {
            $query = $request->validated()['query'];
            $user = $request->user();
            
            $entries = JournalEntry::query()
                ->sortByDate('desc')
                ->get();

            // ============================================================
            // AI INTEGRATION POINT
            // ============================================================
            // Future enhancement: Integrate AI service here to process the query
            // and return semantically relevant results.
            //
            // Expected AI service interface:
            // - Input: $query (string), $entries (Collection<JournalEntry>)
            // - Output: Filtered collection of relevant entries with relevance scores
            //
            // For MVP: Implementing basic keyword matching as fallback
            // ============================================================

            $filteredEntries = $this->performBasicSearch($entries, $query);
            
            $formattedEntries = JournalEntryResource::collection($filteredEntries);
            
            $resultsCount = $filteredEntries->count();
            
            $this->logSearchAnalytics($user->id, $query, $resultsCount);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $this->generateSummary($query, $resultsCount),
                    'entries' => $formattedEntries,
                    'results_count' => $resultsCount,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Search processing failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your search. Please try again.',
            ], 500);
        }
    }

    /**
     * Perform basic keyword matching search (MVP fallback).
     * This method will be replaced when AI integration is implemented.
     */
    private function performBasicSearch( $entries, string $query)
    {
        $keywords = strtolower($query);

        return $entries->filter(function ($entry) use ($keywords) {
            $title = strtolower($entry->title ?? '');
            $content = strtolower($entry->content ?? '');

            return str_contains($title, $keywords) || str_contains($content, $keywords);
        });
    }

    /**
     * Generate a placeholder summary for search results.
     * This will be replaced with AI-generated summary in future implementation.
     */
    private function generateSummary(string $query, int $resultsCount): string
    {
        if ($resultsCount === 0) {
            return "No entries found matching your query: \"{$query}\"";
        }

        if ($resultsCount === 1) {
            return "Found 1 entry matching your query: \"{$query}\"";
        }

        return "Found {$resultsCount} entries matching your query: \"{$query}\"";
    }

    /**
     * Log search analytics to database.
     * Failures are caught and logged but don't block the response.
     */
    private function logSearchAnalytics(int $userId, string $query, int $resultsCount): void
    {
        try {
            SearchAnalytic::create([
                'user_id' => $userId,
                'query' => $query,
                'results_count' => $resultsCount,
            ]);
        } catch (\Exception $e) {
            // Log the error but don't block the search response
            Log::warning('Failed to log search analytics', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
            ]);
        }
    }
}
