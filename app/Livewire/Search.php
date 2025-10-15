<?php

namespace App\Livewire;

use App\Models\JournalEntry;
use App\Services\SearchService;
use Livewire\Attributes\Computed;
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
    public bool $hasSearched = false;
    public bool $isSearching = false;
    public array $searchResults = [];
    public string $resultsSummary = '';
    public int $resultsCount = 0;
    public ?string $errorMessage = null;
    public bool $hasError = false;

    public function submitSearch(SearchService $searchService): void
    {
        $this->query = trim($this->query);
        $this->validate();

        $this->isSearching = true;
        $this->hasError = false;
        $this->errorMessage = null;

        try {
            $entries = JournalEntry::query()
                ->where('user_id', auth()->id())
                ->sortByDate('desc')
                ->get();

            $result = $searchService->analyzeAndSearch($this->query, $entries);

            if (!$result['success']) {
                $this->handleSearchError($result['error']);
                return;
            }

            $this->handleSearchResponse($result);
        } catch (\Exception $e) {
            $this->handleSearchError('Unable to complete search. Please try again.');
        } finally {
            $this->isSearching = false;
        }
    }

    public function clearSearch(): void
    {
        $this->reset([
            'query',
            'hasSearched',
            'isSearching',
            'searchResults',
            'resultsSummary',
            'resultsCount',
            'hasError',
            'errorMessage',
        ]);
    }

    public function retrySearch(SearchService $searchService): void
    {
        $this->submitSearch($searchService);
    }

    #[Computed]
    public function currentState(): string
    {
        if ($this->isSearching) {
            return 'loading';
        }

        if ($this->hasError) {
            return 'error';
        }

        if (!$this->hasSearched) {
            return 'empty';
        }

        return $this->resultsCount > 0 ? 'results' : 'no-results';
    }

    public function render()
    {
        return view('livewire.search');
    }

    private function handleSearchResponse(array $result): void
    {
        $this->hasSearched = true;
        $this->resultsCount = $result['count'];
        $this->searchResults = $result['entries'];
        $this->resultsSummary = $result['summary'];
        $this->hasError = false;
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
