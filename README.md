# laravel-gramps-xml

This package provides easy-to-use services for reading and writing XML files in Laravel applications.

## Installation

To install the package, run the following command in your Laravel project:

```bash
composer require liberu/laravel-gramps-xml
```

## Usage

### XmlReader

To read an XML file, use the `XmlReader` service. Here's a basic example:

```php
// Import the XmlReader class
use App\Services\XmlReader;

// Create an instance of the XmlReader
$xmlReader = new XmlReader();

try {
    // Attempt to read the XML file
    $xmlContent = $xmlReader->read('path/to/your/file.xml');
    // If successful, $xmlContent will contain the contents of the XML file
} catch (Exception $e) {
    // Handle any errors that occur during the read operation
    echo "Error reading XML file: " . $e->getMessage();
}
```

### XmlWriter

To write to an XML file, use the `XmlWriter` service. Here's a basic example:

```php
use App\Services\XmlWriter;

$xmlWriter = new XmlWriter();
$xmlWriter->write('path/to/your/file.xml', $xmlContent);

// This will write $xmlContent to the specified XML file
```