<?php

namespace AMBERSIVE\Tests;

use Illuminate\Contracts\Console\Kernel;

use Orchestra\Testbench\TestCase as Orchestra;

use AMBERSIVE\PdfPrinter\PdfPrinterServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            PdfPrinterServiceProvider::class,
        ];
    }
}
