<?php

namespace AMBERSIVE\PdfPrinter;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;

class PdfPrinterServiceProvider extends ServiceProvider
{

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
       
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Configs
        $this->publishes([
            __DIR__.'/Configs/pdf-printer.php'         => config_path('pdf-printer.php'),
        ],'pdf-printer');

        $this->mergeConfigFrom(
            __DIR__.'/Configs/pdf-printer.php', 'pdf-printer.php'
        );
    }

}
