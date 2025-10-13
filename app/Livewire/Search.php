<?php

namespace App\Livewire;

use App\Models\JournalEntry;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Search Your Journal')]
class Search extends Component
{
    #[Validate('required|string|min:3|max:500')]
    public string $query = '';

    public bool $isLoading = false;
    public bool $hasSearched = false;
    public array $searchResults = [];
    public string $resultsSummary = '';
    public int $resultsCount = 0;
    public ?string $errorMessage = null;
    public bool $hasError = false;

    public function submitSearch(): void
    {
        $this->query = trim($this->query);

        $this->validate();

        $this->isLoading = true;
        $this->hasError = false;
        $this->errorMessage = null;

        try {
            $entries = JournalEntry::query()
                ->sortByDate('desc')
                ->get();

            $filteredEntries = $this->performBasicSearch($entries, $this->query);

            $this->handleSearchResponse($filteredEntries);
        } catch (\Exception $e) {
            $this->handleSearchError('Unable to complete search. Please try again.');
        } finally {
            $this->isLoading = false;
        }
    }

    public function clearSearch(): void
    {
        $this->reset([
            'query',
            'hasSearched',
            'searchResults',
            'resultsSummary',
            'resultsCount',
            'hasError',
            'errorMessage',
        ]);
    }

    public function retrySearch(): void
    {
        $this->submitSearch();
    }

    public function render()
    {
        return view('livewire.search');
    }

    private function performBasicSearch($entries, string $query)
    {
        $keywords = strtolower($query);

        return $entries->filter(function ($entry) use ($keywords) {
            $title = strtolower($entry->title ?? '');
            $content = strtolower($entry->content ?? '');

            return str_contains($title, $keywords) || str_contains($content, $keywords);
        });
    }

    private function handleSearchResponse($entries): void
    {
        $this->hasSearched = true;
        $this->resultsCount = $entries->count();

        $this->searchResults = $entries->map(function ($entry) {
            return [
                'id' => $entry->id,
                'title' => $entry->title,
                'content' => $entry->content,
                'entry_date' => $entry->entry_date->format('Y-m-d'),
                'formatted_date' => $entry->entry_date->format('F j, Y'),
            ];
        })->toArray();

        $this->resultsSummary = $this->generateSummary($this->query, $this->resultsCount);
        $this->hasError = false;
    }

    private function generateSummary(string $query, int $resultsCount): string
    {
        if ($resultsCount === 0) {
            return "";
        }

        if ($resultsCount === 1) {
            return "Found 1 entry matching your query: \"{$query}\"";
        }

        return "Found {$resultsCount} entries matching your query: \"{$query}\"";
    }

    private function handleSearchError(string $message): void
    {
        $this->hasSearched = true;
        $this->hasError = true;
        $this->errorMessage = $message;
        $this->searchResults = [];
        $this->resultsCount = 0;
    }
}
