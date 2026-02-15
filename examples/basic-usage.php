<?php
/**
 * Basic Usage Examples for Laravel Gramps XML Package
 * 
 * This file demonstrates how to use the XmlReader and XmlWriter classes
 * to import and export genealogical data in Gramps XML format.
 */

require_once __DIR__ . '/../src/XmlReader.php';
require_once __DIR__ . '/../src/XmlWriter.php';

use LaravelGrampsXml\XmlReader;
use LaravelGrampsXml\XmlWriter;

// ============================================================================
// Example 1: Creating a Gramps XML file with sample data
// ============================================================================

echo "Example 1: Creating a Gramps XML file\n";
echo "======================================\n\n";

$writer = new XmlWriter();

// Define sample genealogical data
$data = [
    'people' => [
        [
            'handle' => 'person_001',
            'id' => 'I0001',
            'change' => '1609459200',  // Unix timestamp: 2021-01-01 00:00:00 UTC
            'gender' => 'M',
            'name' => [
                'type' => 'Birth Name',
                'first' => 'John',
                'surname' => 'Smith',
            ],
        ],
        [
            'handle' => 'person_002',
            'id' => 'I0002',
            'change' => '1609459200',
            'gender' => 'F',
            'name' => [
                'type' => 'Birth Name',
                'first' => 'Mary',
                'surname' => 'Johnson',
            ],
        ],
        [
            'handle' => 'person_003',
            'id' => 'I0003',
            'change' => '1640995200',  // 2022-01-01 00:00:00 UTC
            'gender' => 'M',
            'name' => [
                'type' => 'Birth Name',
                'first' => 'James',
                'surname' => 'Smith',
            ],
        ],
    ],
    'families' => [
        [
            'handle' => 'family_001',
            'id' => 'F0001',
            'change' => '1609459200',
            'father' => 'person_001',
            'mother' => 'person_002',
        ],
    ],
    'events' => [
        [
            'handle' => 'event_001',
            'id' => 'E0001',
            'change' => '1609459200',
            'type' => 'Birth',
            'description' => 'Birth of John Smith',
        ],
        [
            'handle' => 'event_002',
            'id' => 'E0002',
            'change' => '1609459200',
            'type' => 'Birth',
            'description' => 'Birth of Mary Johnson',
        ],
        [
            'handle' => 'event_003',
            'id' => 'E0003',
            'change' => '1640995200',
            'type' => 'Birth',
            'description' => 'Birth of James Smith',
        ],
        [
            'handle' => 'event_004',
            'id' => 'E0004',
            'change' => '1609459200',
            'type' => 'Marriage',
            'description' => 'Marriage of John Smith and Mary Johnson',
        ],
    ],
];

try {
    // Create the Gramps XML content
    $xmlContent = $writer->createGrampsXml($data);
    
    // Save to file
    $outputFile = '/tmp/sample-family.gramps';
    $writer->write($outputFile, $xmlContent);
    
    echo "✓ Successfully created Gramps XML file: $outputFile\n";
    echo "  - People: " . count($data['people']) . "\n";
    echo "  - Families: " . count($data['families']) . "\n";
    echo "  - Events: " . count($data['events']) . "\n\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// ============================================================================
// Example 2: Reading and parsing a Gramps XML file
// ============================================================================

echo "Example 2: Reading and parsing the Gramps XML file\n";
echo "===================================================\n\n";

$reader = new XmlReader();

try {
    // Read the file we just created
    $parsedData = $reader->parseGrampsXml($outputFile);
    
    echo "✓ Successfully parsed Gramps XML file\n\n";
    
    // Display header information
    if (isset($parsedData['header']['created'])) {
        echo "Header Information:\n";
        echo "  - Created Date: " . $parsedData['header']['created']['date'] . "\n";
        echo "  - Gramps Version: " . $parsedData['header']['created']['version'] . "\n\n";
    }
    
    // Display people information
    echo "People (" . count($parsedData['people']) . "):\n";
    foreach ($parsedData['people'] as $person) {
        $name = $person['names'][0] ?? [];
        $fullName = ($name['first'] ?? '') . ' ' . ($name['surname'] ?? '');
        echo "  - ID: {$person['id']}, Name: $fullName, Gender: {$person['gender']}\n";
    }
    echo "\n";
    
    // Display family information
    echo "Families (" . count($parsedData['families']) . "):\n";
    foreach ($parsedData['families'] as $family) {
        echo "  - ID: {$family['id']}\n";
        echo "    Father: " . ($family['father'] ?? 'N/A') . "\n";
        echo "    Mother: " . ($family['mother'] ?? 'N/A') . "\n";
    }
    echo "\n";
    
    // Display event information
    echo "Events (" . count($parsedData['events']) . "):\n";
    foreach ($parsedData['events'] as $event) {
        echo "  - ID: {$event['id']}, Type: {$event['type']}, Description: {$event['description']}\n";
    }
    echo "\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// ============================================================================
// Example 3: Validating XML against the Gramps DTD
// ============================================================================

echo "Example 3: Validating XML against Gramps DTD\n";
echo "============================================\n\n";

try {
    // Attempt to validate the file
    $validatedXml = $reader->readAndValidate($outputFile);
    
    echo "✓ XML file is valid according to Gramps DTD v1.7.2\n";
    echo "  The file conforms to the official Gramps XML schema\n\n";
    
} catch (Exception $e) {
    echo "✗ Validation failed: " . $e->getMessage() . "\n\n";
}

// ============================================================================
// Example 4: Working with raw XML data
// ============================================================================

echo "Example 4: Working with raw SimpleXML data\n";
echo "===========================================\n\n";

try {
    // Read as SimpleXMLElement for direct XML access
    $xml = $reader->read($outputFile);
    
    echo "✓ Read XML as SimpleXMLElement\n\n";
    
    // Access XML directly
    echo "Accessing XML elements directly:\n";
    
    if (isset($xml->people->person)) {
        foreach ($xml->people->person as $person) {
            $firstName = (string)$person->name->first;
            $surname = (string)$person->name->surname;
            $gender = (string)$person->gender;
            
            echo "  - $firstName $surname ($gender)\n";
        }
    }
    echo "\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

echo "All examples completed successfully!\n";
