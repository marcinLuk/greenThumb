<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJournalEntryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\JournalEntry::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'content' => [
                'required',
                'string',
            ],
            'entry_date' => [
                'required',
                'date',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The title field is required.',
            'title.string' => 'The title must be a string.',
            'title.max' => 'The title cannot exceed 255 characters.',
            'content.required' => 'The content field is required.',
            'content.string' => 'The content must be a string.',
            'entry_date.required' => 'The entry date field is required.',
            'entry_date.date' => 'The entry date must be a valid date.',
            'entry_date.date_format' => 'The entry date must be in YYYY-MM-DD format.',
            'entry_date.before_or_equal' => 'Entry date must be today or in the past.',
        ];
    }
}