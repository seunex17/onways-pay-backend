<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseFuelVoucherRequest extends FormRequest
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
            'litres' => ['required', 'numeric', 'gt:0', 'max:1000'],
            'recipient_type' => ['required', Rule::in(['self', 'other'])],
            'recipient_name' => ['required_if:recipient_type,other', 'nullable', 'string', 'max:120'],
            'recipient_phone' => ['required_if:recipient_type,other', 'nullable', 'regex:/^[0-9]{6,15}$/'],
            'recipient_phone_code' => ['required', 'regex:/^[0-9]{1,4}$/'],
            'payment_method' => ['required', Rule::in(['mobile_money'])],
            'payment_provider' => ['required', Rule::in(['mtn', 'moov', 'orange-money'])],
            'payment_phone' => ['required', 'regex:/^[0-9]{6,15}$/'],
            'payment_phone_code' => ['required', 'regex:/^[0-9]{1,4}$/'],
            'pin' => ['prohibited'],
            'otp' => ['prohibited'],
            'card_secret' => ['prohibited'],
            'partner_secret' => ['prohibited'],
        ];
    }
}
