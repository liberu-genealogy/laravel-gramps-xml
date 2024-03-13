<?php

use LaravelGrampsXml\XmlReader;
use SimpleXMLElement;
use Exception;
use InvalidArgumentException;
use App\Models\Person; // Import the Person model from your Laravel application

class XmlReader
{
    public function read(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new Exception("File not found: {$filePath}");
        }

        set_error_handler(function($severity, $message, $file, $line) {
            throw new InvalidArgumentException($message, $severity);
        });

        try {
            $xml = simplexml_load_file($filePath, 'SimpleXMLElement', LIBXML_NOCDATA);
            if ($xml === false) {
                throw new InvalidArgumentException("Failed to parse XML file: {$filePath}");
            }

            $people = [];
            foreach ($xml->person as $personData) {
                $person = new Person();
                // Map the XML data to the Person model attributes
                $person->name = (string) $personData->name;
                $person->age = (int) $personData->age;
                // Add more attribute mappings as needed
                $people[] = $person;
            }

            return $people;
        } finally {
            restore_error_handler();
        }
    }
}
