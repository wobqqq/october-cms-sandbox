<?php

declare(strict_types=1);

use Backend\Classes\AuthManager;
use Backend\Facades\BackendAuth;
use Backend\Models\User;
use Backend\Models\UserPreference;
use Cms\Classes\Controller;
use Illuminate\Testing\PendingCommand;
use System\Models\SettingModel;

use function Pest\Laravel\artisan;

/**
 * Forget the singletons that would carry one test's state into the next.
 */
function resetRequestSingletons(): void
{
    AuthManager::forgetInstance();

    (new ReflectionProperty(UserPreference::class, 'cache'))->setValue(null, []);
    (new ReflectionProperty(SettingModel::class, 'instances'))->setValue(null, []);
    (new ReflectionProperty(Controller::class, 'instance'))->setValue(null, null);
}

function superuser(): User
{
    $user = new User();
    $user->forceFill([
        'login' => 'admin',
        'email' => 'admin@sandbox.test',
        'first_name' => 'Sandbox',
        'last_name' => 'Admin',
        'is_superuser' => true,
        'is_activated' => true,
    ]);
    $user->setAttribute('password', 'password');
    $user->setAttribute('password_confirmation', 'password');
    $user->save();

    return $user;
}

function signInAsSuperuser(): User
{
    $user = superuser();

    BackendAuth::login($user);

    return $user;
}

/**
 * @param array<string, mixed> $parameters
 */
function command(string $name, array $parameters = []): PendingCommand
{
    $command = artisan($name, $parameters);

    assert($command instanceof PendingCommand);

    return $command;
}

function adminPasswordIs(string $password): bool
{
    $hash = User::where('login', 'admin')->value('password');

    return is_string($hash) && Illuminate\Support\Facades\Hash::check($password, $hash);
}
