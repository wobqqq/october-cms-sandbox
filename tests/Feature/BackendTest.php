<?php

declare(strict_types=1);

use Backend\Facades\Backend;

use function Pest\Laravel\get;

it('sends a guest to the sign-in page', function (): void {
    get(Backend::url('dashboard'))->assertRedirectContains('backend/auth');

    get(Backend::url('backend/auth/signin'))
        ->assertOk()
        ->assertSee('name="login"', false);
});

it('opens the backend pages for a superuser', function (string $page): void {
    signInAsSuperuser();

    get(Backend::url($page))->assertOk();
})->with([
    'dashboard',
    'system/updates',
    'backend/users',
    'cms/themes',
    'system/settings/update/october/backend/editor',
]);
