<?php

namespace App\Events;

use App\Http\Resources\FuelVoucherResource;
use App\Models\FuelVoucherPurchase;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FuelVoucherIssued implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public FuelVoucherPurchase $purchase) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.'.$this->purchase->user_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'transaction_id' => $this->purchase->transaction_id,
            'voucher' => (new FuelVoucherResource($this->purchase->voucher))->resolve(),
        ];
    }
}
