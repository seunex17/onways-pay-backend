<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: TransactionStatusEvent.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 4/12/26
 * Time: 8:27 AM
 */

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionStatusEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Transaction $transaction
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('transaction.'.$this->transaction->id);
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->transaction->id,
            'user_id' => (string) $this->transaction->user_id,
            'type' => $this->transaction->type,
            'purpose' => $this->transaction->purpose,
            'amount' => number_format((float) $this->transaction->amount, 2, '.', ''),
            'description' => $this->transaction->description,
            'status' => $this->transaction->status,
            'reference' => $this->transaction->reference,
            'created_at' => $this->transaction->created_at?->toISOString(),
        ];
    }
}
