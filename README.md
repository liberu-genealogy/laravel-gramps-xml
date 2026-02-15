# laravel-gramps-xml

This package provides easy-to-use services for reading and writing GRAMPS XML files in Laravel applications. It supports the Gramps XML DTD format (version 1.7.2) for genealogical data import and export.

## Features

- ✅ Full support for Gramps XML DTD v1.7.2
- ✅ XML validation against official Gramps DTD
- ✅ Comprehensive import/export functionality
- ✅ Parse genealogical data (people, families, events, places, sources, etc.)
- ✅ Create properly formatted Gramps XML files
- ✅ Error handling and validation

## Installation

To install the package, run the following command in your Laravel project:

```bash
composer require liberu/laravel-gramps-xml
```

## Usage

### XmlReader

#### Reading and Parsing Gramps XML Files

```php
use LaravelGrampsXml\XmlReader;

$xmlReader = new XmlReader();

try {
    // Read and parse Gramps XML file
    $data = $xmlReader->parseGrampsXml('path/to/your/file.gramps');
    
    // Access parsed data
    print_r($data['people']);      // Array of people
    print_r($data['families']);    // Array of families
    print_r($data['events']);      // Array of events
    print_r($data['places']);      // Array of places
    print_r($data['sources']);     // Array of sources
    
} catch (Exception $e) {
    echo "Error reading XML file: " . $e->getMessage();
}
```

#### Reading Raw XML

```php
use LaravelGrampsXml\XmlReader;

$xmlReader = new XmlReader();

try {
    // Get raw SimpleXMLElement object
    $xml = $xmlReader->read('path/to/your/file.gramps');
    
    // Access XML elements directly
    foreach ($xml->people->person as $person) {
        echo $person->name->first . " " . $person->name->surname . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

#### Validating XML Against DTD

```php
use LaravelGrampsXml\XmlReader;

$xmlReader = new XmlReader();

try {
    // Read and validate against Gramps DTD
    $xml = $xmlReader->readAndValidate('path/to/your/file.gramps');
    echo "XML is valid!\n";
} catch (Exception $e) {
    echo "Validation error: " . $e->getMessage();
}
```

### XmlWriter

#### Creating and Writing Gramps XML Files

```php
use LaravelGrampsXml\XmlWriter;

$xmlWriter = new XmlWriter();

// Define genealogical data
$data = [
    'people' => [
        [
            'handle' => 'person001',
            'id' => 'I0001',
            'gender' => 'M',
            'name' => [
                'type' => 'Birth Name',
                'first' => 'John',
                'surname' => 'Doe',
            ],
        ],
    ],
    'families' => [
        [
            'handle' => 'family001',
            'id' => 'F0001',
            'father' => 'person001',
            'mother' => 'person002',
        ],
    ],
    'events' => [
        [
            'handle' => 'event001',
            'id' => 'E0001',
            'type' => 'Birth',
            'description' => 'Birth of John Doe',
        ],
    ],
];

try {
    // Create Gramps XML
    $xmlContent = $xmlWriter->createGrampsXml($data);
    
    // Write to file
    $xmlWriter->write('output.gramps', $xmlContent);
    
    echo "Gramps XML file created successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

#### Validating XML Content

```php
use LaravelGrampsXml\XmlWriter;

$xmlWriter = new XmlWriter();

try {
    $xmlContent = $xmlWriter->createGrampsXml($data);
    
    // Validate the XML content against the Gramps DTD
    if ($xmlWriter->validateXmlContent($xmlContent)) {
        echo "XML content is valid according to Gramps DTD!\n";
        $xmlWriter->write('validated-output.gramps', $xmlContent);
    }
} catch (Exception $e) {
    echo "Validation error: " . $e->getMessage();
}
```

### Using Service Classes

The package also provides service classes in the `Services` namespace that extend the base functionality:

```php
use LaravelGrampsXml\Services\XmlReader;
use LaravelGrampsXml\Services\XmlWriter;

// Reader service - returns parsed array data by default
$readerService = new XmlReader();
$importData = $readerService->import('path/to/file.gramps');

echo "Imported {$importData['stats']['people_count']} people\n";
echo "Imported {$importData['stats']['families_count']} families\n";

// Writer service - handles both array and string content
$writerService = new XmlWriter();
$writerService->write('output.gramps', $data);
```

## Gramps XML Format

This package implements the Gramps XML DTD version 1.7.2. The DTD file is included in the package at `dtd/grampsxml.dtd`.

### Supported Elements

- **Header**: Database metadata and researcher information
- **People**: Individual persons with names, gender, events, etc.
- **Families**: Family units with relationships
- **Events**: Life events (birth, death, marriage, etc.)
- **Places**: Geographic locations
- **Sources**: Source documents and references
- **Citations**: Citations to sources
- **Repositories**: Archives and repositories
- **Notes**: Textual notes and annotations
- **Tags**: Categorization tags

## Error Handling

The package throws exceptions for various error conditions:

- `Exception`: File not found, write failures, DTD validation errors
- `InvalidArgumentException`: XML parsing errors

Always wrap your code in try-catch blocks to handle errors gracefully.

## License

This package is open-source software licensed under the MIT license.