<?php

namespace App\Livewire;

use App\Models\JournalEntry;
use Flux\Flux;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class EntryModal extends Component
{
    public string $modalMode = 'create';

    public bool $isOpen = false;

    public ?int $entryId = null;

    public string $title = '';

    public string $content = '';

    public string $entry_date = '';

    public string $errorMSG = '';

    public bool $isLoading = false;

    public bool $showDeleteConfirmation = false;

    /**
     * Validation rules for form fields.
     */
    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'entry_date' => ['required', 'date', 'date_format:Y-m-d', 'before_or_equal:today'],
        ];
    }

    /**
     * Custom validation messages.
     */
    protected function messages(): array
    {
        return [
            'title.required' => 'The title field is required.',
            'title.max' => 'The title cannot exceed 255 characters.',
            'content.required' => 'The content field is required.',
            'entry_date.required' => 'The entry date field is required.',
            'entry_date.date_format' => 'The entry date must be in YYYY-MM-DD format.',
            'entry_date.before_or_equal' => 'Entry date must be today or in the past.',
        ];
    }

    /**
     * Open modal in create mode for a specific date.
     */
    #[On('openCreateModal')]
    public function openCreateModal(string $date): void
    {
        $this->resetState();
        $this->modalMode = 'create';
        $this->entry_date = $date;
        $this->isOpen = true;
    }

    /**
     * Open modal in view mode for an existing entry.
     */
    #[On('openViewModal')]
    public function openViewModal(int $entryId): void
    {
        $this->resetState();
        $this->modalMode = 'view';
        $this->entryId = $entryId;
        $this->loadEntry();
        $this->isOpen = true;
    }

    /**
     * Open modal in edit mode for an existing entry.
     */
    #[On('openEditModal')]
    public function openEditModal(int $entryId): void
    {
        $this->resetState();
        $this->modalMode = 'edit';
        $this->entryId = $entryId;
        $this->loadEntry();
        $this->isOpen = true;
    }

    /**
     * Load entry data from database.
     */
    protected function loadEntry(): void
    {
        $this->isLoading = true;

        try {
            $entry = JournalEntry::findOrFail($this->entryId);

            // Check authorization
            $this->authorize('view', $entry);

            $this->title = $entry->title;
            $this->content = $entry->content;
            $this->entry_date = $entry->entry_date->format('Y-m-d');
        } catch (ModelNotFoundException $e) {
            Flux::toast(
                heading: 'Entry Not Found',
                text: 'Journal entry not found.',
                variant: 'danger'
            );
            $this->closeModal();
            $this->dispatch('entryUpdated');
        } catch (AuthorizationException $e) {
            Flux::toast(
                heading: 'Unauthorized',
                text: 'Journal entry not found.',
                variant: 'danger'
            );
            $this->closeModal();
            $this->dispatch('entryUpdated');
        } catch (\Exception $e) {
            Flux::toast(
                heading: 'Error',
                text: 'Failed to load entry. Please try again.',
                variant: 'danger'
            );
            $this->closeModal();
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Save entry (create or update based on mode).
     */
    public function save(): void
    {
        $this->isLoading = true;

        try {
            if ($this->modalMode === 'create') {
                $this->createEntry();
            } elseif ($this->modalMode === 'edit') {
                $this->updateEntry();
            }
        } catch (\Exception $e) {
            // Error handling is done in createEntry() and updateEntry()
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Create a new journal entry.
     */
    protected function createEntry(): void
    {
        try {
            // Check entry limit
            $entriesCount = auth()->user()->entriesCount;
            if ($entriesCount && $entriesCount->count >= 50) {
                $this->errorMSG = 'You have reached the maximum limit of 50 journal entries.';
                return;
            }

            DB::transaction(function () {
                JournalEntry::create([
                    'user_id' => auth()->id(),
                    'title' => $this->title,
                    'content' => $this->content,
                    'entry_date' => $this->entry_date,
                ]);
            });

        } catch (\Exception $e) {
            $this->errorMSG = 'Failed to create entry. Please try again.';
        } finally {
            $this->dispatch('entryUpdated');
            $this->closeModal();
        }
    }

    /**
     * Update an existing journal entry.
     */
    protected function updateEntry(): void
    {
        try {
            $entry = JournalEntry::findOrFail($this->entryId);

            // Check authorization
            $this->authorize('update', $entry);

            $entry->update([
                'title' => $this->title,
                'content' => $this->content,
                'entry_date' => $this->entry_date,
            ]);

            Flux::toast(
                heading: 'Success',
                text: 'Entry updated successfully.',
                variant: 'success'
            );

            $this->dispatch('entryUpdated');
            $this->closeModal();
        } catch (ModelNotFoundException $e) {
            Flux::toast(
                heading: 'Entry Not Found',
                text: 'Journal entry not found.',
                variant: 'danger'
            );
            $this->closeModal();
            $this->dispatch('entryUpdated');
        } catch (AuthorizationException $e) {
            Flux::toast(
                heading: 'Unauthorized',
                text: 'Journal entry not found.',
                variant: 'danger'
            );
            $this->closeModal();
            $this->dispatch('entryUpdated');
        } catch (\Exception $e) {
            Flux::toast(
                heading: 'Error',
                text: 'Failed to update entry. Please try again.',
                variant: 'danger'
            );
        }
    }

    /**
     * Show delete confirmation modal.
     */
    public function delete(): void
    {
        $this->showDeleteConfirmation = true;
    }

    /**
     * Confirm and execute entry deletion.
     */
    public function confirmDelete(): void
    {
        $this->isLoading = true;

        try {
            $entry = JournalEntry::findOrFail($this->entryId);

            // Check authorization
            $this->authorize('delete', $entry);

            $entry->delete();

            Flux::toast(
                heading: 'Success',
                text: 'Entry deleted successfully.',
                variant: 'success'
            );

            $this->dispatch('entryUpdated');
            $this->closeModal();
        } catch (ModelNotFoundException $e) {
            Flux::toast(
                heading: 'Entry Not Found',
                text: 'Journal entry not found.',
                variant: 'danger'
            );
            $this->closeModal();
            $this->dispatch('entryUpdated');
        } catch (AuthorizationException $e) {
            Flux::toast(
                heading: 'Unauthorized',
                text: 'Journal entry not found.',
                variant: 'danger'
            );
            $this->closeModal();
            $this->dispatch('entryUpdated');
        } catch (\Exception $e) {
            Flux::toast(
                heading: 'Error',
                text: 'An error occurred while deleting the entry.',
                variant: 'danger'
            );
        } finally {
            $this->isLoading = false;
            $this->showDeleteConfirmation = false;
        }
    }

    /**
     * Switch from view mode to edit mode.
     */
    public function switchToEditMode(): void
    {
        $this->modalMode = 'edit';
    }

    /**
     * Close the modal and reset state.
     */
    public function closeModal(): void
    {
        $this->isOpen = false;
        Flux::modal('entry-modal')->close();
        $this->resetState();
    }

    /**
     * Reset all component state to defaults.
     */
    protected function resetState(): void
    {
        $this->modalMode = 'create';
        $this->entryId = null;
        $this->title = '';
        $this->content = '';
        $this->entry_date = '';
        $this->isLoading = false;
        $this->showDeleteConfirmation = false;
        $this->resetValidation();
    }

    /**
     * Get the modal title based on current mode.
     */
    public function getModalTitleProperty(): string
    {
        return match ($this->modalMode) {
            'create' => 'New Entry',
            'view' => 'View Entry',
            'edit' => 'Edit Entry',
            default => 'Entry',
        };
    }

    /**
     * Check if save button should be disabled.
     */
    public function getSaveDisabledProperty(): bool
    {
        return empty($this->title) || empty($this->content) || empty($this->entry_date) || $this->isLoading;
    }

    public function render()
    {
        return view('livewire.entry-modal');
    }
}
