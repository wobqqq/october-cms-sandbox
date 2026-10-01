<?php

declare(strict_types=1);

use Backend\Models\User;

it('creates the local superuser', function (): void {
    command('sandbox:admin')->assertSuccessful();

    expect(User::where('login', 'admin')->value('is_superuser'))->toBeTruthy()
        ->and(adminPasswordIs('password'))->toBeTrue();
});

it('resets the password of an existing administrator', function (): void {
    superuser();

    command('sandbox:admin', ['--password' => 'another-secret'])->assertSuccessful();

    expect(User::where('login', 'admin')->count())->toBe(1)
        ->and(adminPasswordIs('another-secret'))->toBeTrue();
});

it('refuses to run in production', function (): void {
    app()->detectEnvironment(fn (): string => 'production');

    command('sandbox:admin')->assertFailed();

    expect(User::where('login', 'admin')->exists())->toBeFalse();
});
