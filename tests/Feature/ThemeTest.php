<?php

declare(strict_types=1);

use Cms\Classes\Theme;
use System\Classes\ErrorHandler;

use function Pest\Laravel\get;

it('runs on the sandbox theme', function (): void {
    expect(Theme::getActiveThemeCode())->toBe('sandbox');
});

it('shows the home page with a link to the backend', function (): void {
    get('/')
        ->assertOk()
        ->assertSee('<h1>Plugin sandbox</h1>', false)
        ->assertSee('href="http://localhost/admin"', false)
        ->assertSee('themes/sandbox/assets/css/theme.css', false);
});

it('answers an unknown URL with the theme 404 page', function (): void {
    get('/a/page/that/does/not/exist')
        ->assertNotFound()
        ->assertSee('<h1>Page not found</h1>', false);
});

it('ships the error and maintenance pages October looks for', function (string $page): void {
    expect(Theme::getActiveTheme()?->getPath() . '/pages/' . $page . '.htm')->toBeFile();
})->with(['404', 'error', 'maintenance']);

it('serves the theme stylesheet', function (): void {
    expect(base_path('themes/sandbox/assets/css/theme.css'))->toBeFile();
});

it('renders the theme error page for an error when debug is off', function (): void {
    config(['app.debug' => false]);

    $content = (new ErrorHandler())->handleCustomError(new RuntimeException('Broken page'));

    assert(is_string($content));

    expect($content)->toContain('<h1>Something went wrong</h1>')
        ->and(str_contains($content, 'Broken page'))->toBeFalse();
});

it('leaves the error to the debug screen when debug is on', function (): void {
    config(['app.debug' => true]);

    expect((new ErrorHandler())->handleCustomError(new RuntimeException('Broken page')))->toBeNull();
});
