<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class GetEntriesByDateRangeRequest extends FormRequest
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
                'required',
                'date',
                'date_format:Y-m-d',
                $this->validateStartDateIsMonday(),
            ],
            'end_date' => [
                'required',
                'date',
                'date_format:Y-m-d',
                'after_or_equal:start_date',
                $this->validateEndDateIsSunday(),
                $this->validateMaxDateRange(),
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
            'start_date.required' => 'The start date field is required.',
            'start_date.date' => 'The start date must be a valid date.',
            'start_date.date_format' => 'The start date must be in YYYY-MM-DD format.',
            'end_date.required' => 'The end date field is required.',
            'end_date.date' => 'The end date must be a valid date.',
            'end_date.date_format' => 'The end date must be in YYYY-MM-DD format.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
        ];
    }

    /**
     * Validate that start_date is a Monday for weekly calendar view.
     */
    protected function validateStartDateIsMonday(): \Closure
    {
        return function ($attribute, $value, $fail) {
            $date = Carbon::parse($value);
            if ($date->dayOfWeek !== Carbon::MONDAY) {
                $fail('The start date must be a Monday for weekly calendar view.');
            }
        };
    }

    /**
     * Validate that end_date is a Sunday for weekly calendar view.
     */
    protected function validateEndDateIsSunday(): \Closure
    {
        return function ($attribute, $value, $fail) {
            $date = Carbon::parse($value);
            if ($date->dayOfWeek !== Carbon::SUNDAY) {
                $fail('The end date must be a Sunday for weekly calendar view.');
            }
        };
    }

    /**
     * Validate that the date range does not exceed maximum allowed days.
     */
    protected function validateMaxDateRange(): \Closure
    {
        return function ($attribute, $value, $fail) {
            $startDate = Carbon::parse($this->input('start_date'));
            $endDate = Carbon::parse($value);

            if ($endDate->diffInDays($startDate) > 31) {
                $fail('The date range cannot exceed 31 days.');
            }
        };
    }
}