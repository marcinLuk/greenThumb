<div>
    {{-- Entry Modal --}}
    <flux:modal name="entry-modal" :variant="$modalMode === 'view' ? 'bare' : 'default'" class="w-full max-w-2xl"
                :open="$isOpen">
        <form wire:submit="save">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between border-b px-6 py-4 dark:border-zinc-700">
                <flux:heading size="lg">{{ $this->modalTitle }}</flux:heading>
            </div>

            {{-- Modal Body --}}
            <div class="space-y-6 px-6 py-6">
                {{-- Loading Spinner --}}
                @if($isLoading)
                    <div class="flex items-center justify-center py-12">
                        <flux:icon.loading class="size-8"/>
                    </div>
                @endif

                {{-- Form Section (Create/Edit Modes) --}}
                @if(!$isLoading && ($modalMode === 'create' || $modalMode === 'edit'))
                    <div class="space-y-4">
                        {{-- Title Field --}}
                        <div>
                            <flux:input
                                wire:model.blur="title"
                                label="Title"
                                placeholder="Enter entry title"
                                :required="true"
                                maxlength="255"
                            />
                            @error('title')
                            <flux:error class="mt-2">{{ $message }}</flux:error>
                            @enderror
                        </div>

                        {{-- Hidden Date Field (pre-populated, not editable by user) --}}
                        <input id="entryDate" type="hidden" wire:model="entry_date"/>

                        {{-- Content Field --}}
                        <div>
                            <flux:textarea
                                wire:model.blur="content"
                                label="Content"
                                placeholder="Write your gardening notes here..."
                                :required="true"
                                rows="8"
                            />
                            @error('content')
                            <flux:error class="mt-2">{{ $message }}</flux:error>
                            @enderror
                        </div>
                    </div>
                @endif

                {{-- Display Section (View Mode) --}}
                @if(!$isLoading && $modalMode === 'view')
                    <div class="space-y-4">
                        {{-- Title Display --}}
                        <div>
                            <flux:heading size="lg" class="mb-2">{{ $title }}</flux:heading>
                        </div>

                        {{-- Date Display --}}
                        <div>
                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                {{ \Carbon\Carbon::parse($entry_date)->format('l, F j, Y') }}
                            </flux:text>
                        </div>

                        {{-- Content Display --}}
                        <div class="prose prose-sm max-w-none dark:prose-invert">
                            <flux:text class="whitespace-pre-wrap">{{ $content }}</flux:text>
                        </div>

                        {{-- Timestamps --}}
                        @if($entryId)
                            <div class="border-t pt-4 dark:border-zinc-700">
                                <flux:text class="text-xs text-zinc-500 dark:text-zinc-500">
                                    Created: {{ \Carbon\Carbon::parse($entry_date)->diffForHumans() }}
                                </flux:text>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-between border-t px-6 py-4 dark:border-zinc-700">
                <div class="flex gap-2">
                    {{-- Delete Button (View/Edit Modes Only) --}}
                    @if($modalMode !== 'create' && $entryId)
                        <flux:button
                            type="button"
                            variant="danger"
                            wire:click="confirmDelete"
                            wire:confirm="Are you sure?"
                            :disabled="$isLoading"
                        >
                            Delete
                        </flux:button>
                    @endif
                </div>

                <div class="flex gap-2">

                    {{-- Save Button (Create/Edit Modes) --}}
                    @if($modalMode === 'create' || $modalMode === 'edit')
                        <flux:button
                            type="submit"
                            variant="primary"
                        >
                            <span wire:loading.remove wire:target="save">Save</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </flux:button>
                    @endif
                </div>
            </div>
        </form>
    </flux:modal>

    <flux:error model:errorMsg class="mt-2">{{ $errorMSG }}</flux:error>
</div>
