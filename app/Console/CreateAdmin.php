<?php

declare(strict_types=1);

namespace App\Console;

use Backend\Models\User;
use Illuminate\Console\Command;

final class CreateAdmin extends Command
{
    /** @var string */
    protected $signature = 'sandbox:admin
        {--login=admin : The login of the administrator}
        {--email=admin@localhost : The e-mail address of the administrator}
        {--password=password : The password of the administrator}';

    /** @var string */
    protected $description = 'Create the local superuser, or reset its password';

    public function handle(): int
    {
        if (app()->isProduction()) {
            $this->error('Refusing to create a default administrator in production.');

            return self::FAILURE;
        }

        $login = (string) $this->option('login');
        $password = (string) $this->option('password');

        $user = User::where('login', $login)->first() ?? new User();

        $user->forceFill([
            'login' => $login,
            'email' => (string) $this->option('email'),
            'first_name' => 'Sandbox',
            'last_name' => 'Admin',
            'is_superuser' => true,
            'is_activated' => true,
        ]);
        $user->setAttribute('password', $password);
        $user->setAttribute('password_confirmation', $password);
        $user->save();

        $this->info(sprintf('Sign in at %s as "%s".', \Backend\Facades\Backend::url(''), $login));

        return self::SUCCESS;
    }
}
