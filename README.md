# PDF Printer
This package is a wrapper for the private print-api package from [AMBERSIVE KG](https://ambersive.com). This package requires this endpoint otherwise you will not be able to create any pdf document.

## Installation

```bash
composer require ambersive/pdfprinter
```

## Usage

Please be aware that you will need a running instance of the [PRINT-API](https://github.com/AMBERSIVE/print-api). Otherwise the methods won't work as expected.

```php
// Setup the pdf printer
$settings = new PdfPrinterSetting("http://localhost", 3000);
$printer  = new PdfPrinter($settings);

// Start the print session and save the result
$printer->create("https://orf.at")->save("folder");
```

## Security Vulnerabilities

If you discover a security vulnerability within this package, please send an e-mail to Manuel Pirker-Ihl via [manuel.pirker-ihl@ambersive.com](mailto:manuel.pirker-ihl@ambersive.com). All security vulnerabilities will be promptly addressed.

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

