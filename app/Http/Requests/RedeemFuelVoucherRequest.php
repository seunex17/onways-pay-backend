<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RedeemFuelVoucherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['required_without:qr_data', 'nullable', 'string', 'max:100'],
            'qr_data' => ['required_without:code', 'nullable', 'string', 'max:2000'],
            'station_id' => ['required', 'string', 'max:100'],
            'partner_redemption_id' => ['required', 'string', 'max:120'],
            'terminal_id' => ['nullable', 'string', 'max:120'],
        ];
    }
}
