<?php

use App\Models\Transaction;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('transaction.{id}', function ($user, $id) {
    $transaction = Transaction::find($id);

    return $transaction && (int) $user->id === (int) $transaction->user_id;
});
