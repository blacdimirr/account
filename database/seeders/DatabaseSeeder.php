<?php

namespace Database\Seeders;

use App\Models\Utility;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Nombre de ruta si existe (en web); será null en CLI
        $routeName = \Request::route()?->getName();

        // Si estás en la web y la ruta es la del Updater, corre solo la inicialización de idioma
        if (!app()->runningInConsole() && $routeName === 'LaravelUpdater::database') {
            Utility::languagecreate();
            return;
        }

        // Ejecución estándar: CLI (artisan) o web fuera del Updater
        $this->call(UsersTableSeeder::class);
        $this->call(NotificationSeeder::class);
        $this->call(AiTemplateSeeder::class);

        // Migrar y sembrar el módulo LandingPage
        Artisan::call('module:migrate LandingPage');
        Artisan::call('module:seed LandingPage');
    }
}