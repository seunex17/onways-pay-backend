<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: PaymentRequestEvent.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 4/16/26
 * Time: 3:05 PM
 */

namespace App\Events;

use App\Models\PaymentRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentRequestEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public PaymentRequest $paymentRequest,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('payment-request.'.$this->paymentRequest->id);
    }

    public function broadcastWith(): array
    {
        return $this->paymentRequest->toArray();
    }
}
