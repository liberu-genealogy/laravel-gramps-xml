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
use LaravelGrampsXml\XmlReader;

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

To write to an XML file, use the `XmlWriter` service.

#### Exporting to GRAMPS XML format

To export data to the GRAMPS XML format using `grampsxml.dtd`, you can utilize the `XmlWriter`'s functionality directly. Here's how you can do it:

```php
use LaravelGrampsXml\XmlWriter;

$xmlWriter = new XmlWriter();
$content = 'Your data structured according to GRAMPS XML format';
// Make sure to structure your content according to the GRAMPS XML format
$xmlWriter->write('path/to/your/grampsxml_file.xml', $content);
```

This will export the data in the GRAMPS XML format, ready to be used with GRAMPS software. Here's a basic example:

```php
use LaravelGrampsXml\XmlWriter;

$xmlWriter = new XmlWriter();
$xmlWriter->write('path/to/your/file.xml', $xmlContent);

// This will write $xmlContent to the specified XML file
```