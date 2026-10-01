<?php

declare(strict_types=1);

arch('the application code declares strict types', function (): void {
    expect(['App', 'Tests'])->toUseStrictTypes();
});

arch('the environment is read only through config', function (): void {
    expect('env')->not->toBeUsedIn('App');
});

foreach (['dd', 'dump', 'var_dump', 'var_export', 'print_r', 'ray'] as $function) {
    arch('no ' . $function . '() is left behind', function () use ($function): void {
        expect($function)->not->toBeUsedIn(['App', 'Tests']);
    });
}

arch('console commands are final commands', function (): void {
    expect('App\Console')
        ->classes()
        ->toBeFinal()
        ->toExtend(Illuminate\Console\Command::class);
});
