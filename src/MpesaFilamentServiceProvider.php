<?php

namespace FelixMuhoro\MpesaFilament;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MpesaFilamentServiceProvider extends PackageServiceProvider
{
    public static string $name = 'mpesa-filament';

    public static string $viewNamespace = 'mpesa-filament';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile('mpesa-filament')
            ->hasViews(static::$viewNamespace);
    }

    public function packageBooted(): void
    {
        // Assets, views, etc. registered via Filament plugin boot()
    }
}
