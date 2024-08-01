<?php

use App\Models\User;

test('that true is true', function () {
    expect(true)->toBeTrue();
});

test('the email ends with @gmail.com', function () {
    $user = User::factory()->create();
    $user->update(['email' => 'johndoe@gmail.com']);
    expect($user->email)->endsWith('@gmail.com');
});
