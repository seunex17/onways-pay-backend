<?php

use App\Mail\PasswordResetMail;
use App\Models\User;

test('password reset email renders the temporary password', function () {
    $user = User::factory()->make([
        'first_name' => 'Ada',
    ]);

    $email = new PasswordResetMail($user, 'TempPass123!');

    $email->assertSeeInHtml('Hello Ada');
    $email->assertSeeInHtml('TempPass123!');
    $email->assertSeeInHtml('Temporary password');
    $email->assertSeeInHtml('please change this password after you sign in');
});
