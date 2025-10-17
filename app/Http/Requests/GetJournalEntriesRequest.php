<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetJournalEntriesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by auth middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_date' => [
                'nullable',
                'date',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],
            'end_date' => [
                'nullable',
                'date',
                'date_format:Y-m-d',
                'after_or_equal:start_date',
                'before_or_equal:today',
            ],
            'sort' => [
                'nullable',
                'string',
                'in:asc,desc',
            ],
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:50',
            ],
            'page' => [
                'nullable',
                'integer',
                'min:1',
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
            'start_date.date' => 'The start date must be a valid date.',
            'start_date.date_format' => 'The start date must be in YYYY-MM-DD format.',
            'start_date.before_or_equal' => 'The start date cannot be in the future.',
            'end_date.date' => 'The end date must be a valid date.',
            'end_date.date_format' => 'The end date must be in YYYY-MM-DD format.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'end_date.before_or_equal' => 'The end date cannot be in the future.',
            'sort.in' => 'The sort parameter must be either "asc" or "desc".',
            'per_page.integer' => 'The per_page parameter must be an integer.',
            'per_page.min' => 'The per_page parameter must be at least 1.',
            'per_page.max' => 'The per_page parameter cannot exceed 50.',
            'page.integer' => 'The page parameter must be an integer.',
            'page.min' => 'The page parameter must be at least 1.',
        ];
    }

    /**
     * Get validated sort direction with default value.
     */
    public function getSortDirection(): string
    {
        return $this->validated()['sort'] ?? 'desc';
    }

    /**
     * Get validated per page value with default.
     */
    public function getPerPage(): int
    {
        return $this->validated()['per_page'] ?? 50;
    }
}
