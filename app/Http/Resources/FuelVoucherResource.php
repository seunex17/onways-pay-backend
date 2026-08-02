<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FuelVoucherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'code' => $this->code,
            'qr_data' => $this->qr_data,
            'status' => $this->status,
            'litres' => $this->litres,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'recipient_name' => $this->recipient_name,
            'recipient_phone' => $this->recipient_phone,
            'expires_at' => $this->expires_at?->toISOString(),
            'download_url' => route('fuel-vouchers.download', $this->resource),
        ];
    }
}
