# laravel-gramps-xml

This package provides easy-to-use services for reading and writing XML files in Laravel applications.

## Installation

To install the package, run the following command in your Laravel project:

```bash
composer require laravel-gramps-xml
```

## Usage

### XmlReader

To read an XML file, use the `XmlReader` service. Here's a basic example:

```php
use App\Services\XmlReader;

$xmlReader = new XmlReader();
$xmlContent = $xmlReader->read('path/to/your/file.xml');

// $xmlContent will contain the contents of the XML file
```

### XmlWriter

To write to an XML file, use the `XmlWriter` service. Here's a basic example:

```php
use App\Services\XmlWriter;

$xmlWriter = new XmlWriter();
$xmlWriter->write('path/to/your/file.xml', $xmlContent);

// This will write $xmlContent to the specified XML file
```