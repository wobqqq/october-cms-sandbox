<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorLaravel\Rector\ClassMethod\AddGenericBuilderToScopesRector;
use RectorLaravel\Rector\ClassMethod\MakeModelAttributesAndScopesProtectedRector;
use RectorLaravel\Rector\StaticCall\CarbonToDateFacadeRector;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/config',
        __DIR__ . '/app',
        __DIR__ . '/bootstrap',
        __DIR__ . '/tests',
    ])
    ->withRootFiles()
    ->withSkip([
        // The Laravel Date facade returns Carbon and breaks October's Date casts.
        CarbonToDateFacadeRector::class,
        // October's query builder calls a scope as [$model, 'scopeX'] from
        // outside the model, so a protected scope is undefined there.
        MakeModelAttributesAndScopesProtectedRector::class,
        // October's query builder is not generic.
        AddGenericBuilderToScopesRector::class,
    ])
    ->withPhpSets()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        earlyReturn: true,
    )
    ->withRules([
        Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitSelfCallRector::class,
    ])
    ->withSets([
        LaravelSetList::LARAVEL_CODE_QUALITY,
        LaravelSetList::LARAVEL_IF_HELPERS,
        LaravelSetList::LARAVEL_TYPE_DECLARATIONS,
    ]);
