<?php

namespace LaravelGrampsXml;

use DOMDocument;
use Exception;

class XmlWriter
{
    /**
     * Write XML content to a file
     *
     * @param string $filePath
     * @param string $xmlContent
     * @throws Exception
     */
    public function write(string $filePath, string $xmlContent): void
    {
        $result = file_put_contents($filePath, $xmlContent);
        if ($result === false) {
            throw new Exception("Failed to write XML content to file: {$filePath}");
        }
    }

    /**
     * Validate XML content against grampsxml.dtd format
     *
     * @param string $xmlContent
     * @return bool
     */
    public function validateXmlContent(string $xmlContent): bool
    {
        $dtdPath = __DIR__ . '/../dtd/grampsxml.dtd';
        
        if (!file_exists($dtdPath)) {
            throw new Exception("DTD file not found at: {$dtdPath}");
        }

        libxml_use_internal_errors(true);
        
        $dom = new DOMDocument();
        $dom->loadXML($xmlContent);
        
        $valid = $dom->validate();
        
        if (!$valid) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            
            $errorMessages = array_map(function($error) {
                return sprintf(
                    "[Line %d] %s",
                    $error->line,
                    trim($error->message)
                );
            }, $errors);
            
            throw new Exception("XML validation failed:\n" . implode("\n", $errorMessages));
        }
        
        libxml_use_internal_errors(false);
        
        return true;
    }

    /**
     * Create a basic Gramps XML structure
     *
     * @param array $data
     * @return string
     */
    public function createGrampsXml(array $data = []): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        
        // Create DOCTYPE
        $implementation = new \DOMImplementation();
        $dtd = $implementation->createDocumentType(
            'database',
            '-//GRAMPS//DTD GRAMPS XML V1.7.2//EN',
            'http://gramps-project.org/xml/1.7.2/grampsxml.dtd'
        );
        $dom->appendChild($dtd);
        
        // Create root database element
        $database = $dom->createElement('database');
        $database->setAttribute('xmlns', 'http://gramps-project.org/xml/1.7.2/');
        $dom->appendChild($database);
        
        // Create header
        $header = $dom->createElement('header');
        $database->appendChild($header);
        
        $created = $dom->createElement('created');
        $created->setAttribute('date', date('Y-m-d'));
        $created->setAttribute('version', '1.7.2');
        $header->appendChild($created);
        
        // Add optional sections if data is provided
        if (isset($data['people']) && !empty($data['people'])) {
            $people = $dom->createElement('people');
            $database->appendChild($people);
            
            foreach ($data['people'] as $personData) {
                $person = $this->createPersonElement($dom, $personData);
                $people->appendChild($person);
            }
        }
        
        if (isset($data['families']) && !empty($data['families'])) {
            $families = $dom->createElement('families');
            $database->appendChild($families);
            
            foreach ($data['families'] as $familyData) {
                $family = $this->createFamilyElement($dom, $familyData);
                $families->appendChild($family);
            }
        }
        
        if (isset($data['events']) && !empty($data['events'])) {
            $events = $dom->createElement('events');
            $database->appendChild($events);
            
            foreach ($data['events'] as $eventData) {
                $event = $this->createEventElement($dom, $eventData);
                $events->appendChild($event);
            }
        }
        
        return $dom->saveXML();
    }

    /**
     * Create a person element
     *
     * @param DOMDocument $dom
     * @param array $data
     * @return \DOMElement
     */
    private function createPersonElement(DOMDocument $dom, array $data): \DOMElement
    {
        $person = $dom->createElement('person');
        $person->setAttribute('handle', $data['handle'] ?? uniqid('person_'));
        $person->setAttribute('change', $data['change'] ?? (string)time());
        $person->setAttribute('id', $data['id'] ?? '');
        
        if (isset($data['priv'])) {
            $person->setAttribute('priv', $data['priv']);
        }
        
        // Gender (required)
        $gender = $dom->createElement('gender', $data['gender'] ?? 'U');
        $person->appendChild($gender);
        
        // Name
        if (isset($data['name'])) {
            $name = $this->createNameElement($dom, $data['name']);
            $person->appendChild($name);
        }
        
        return $person;
    }

    /**
     * Create a name element
     *
     * @param DOMDocument $dom
     * @param array $data
     * @return \DOMElement
     */
    private function createNameElement(DOMDocument $dom, array $data): \DOMElement
    {
        $name = $dom->createElement('name');
        
        if (isset($data['type'])) {
            $name->setAttribute('type', $data['type']);
        }
        
        if (isset($data['first'])) {
            $first = $dom->createElement('first', htmlspecialchars($data['first']));
            $name->appendChild($first);
        }
        
        if (isset($data['surname'])) {
            $surname = $dom->createElement('surname', htmlspecialchars($data['surname']));
            $name->appendChild($surname);
        }
        
        if (isset($data['suffix'])) {
            $suffix = $dom->createElement('suffix', htmlspecialchars($data['suffix']));
            $name->appendChild($suffix);
        }
        
        return $name;
    }

    /**
     * Create a family element
     *
     * @param DOMDocument $dom
     * @param array $data
     * @return \DOMElement
     */
    private function createFamilyElement(DOMDocument $dom, array $data): \DOMElement
    {
        $family = $dom->createElement('family');
        $family->setAttribute('handle', $data['handle'] ?? uniqid('family_'));
        $family->setAttribute('change', $data['change'] ?? (string)time());
        $family->setAttribute('id', $data['id'] ?? '');
        
        if (isset($data['father'])) {
            $father = $dom->createElement('father');
            $father->setAttribute('hlink', $data['father']);
            $family->appendChild($father);
        }
        
        if (isset($data['mother'])) {
            $mother = $dom->createElement('mother');
            $mother->setAttribute('hlink', $data['mother']);
            $family->appendChild($mother);
        }
        
        return $family;
    }

    /**
     * Create an event element
     *
     * @param DOMDocument $dom
     * @param array $data
     * @return \DOMElement
     */
    private function createEventElement(DOMDocument $dom, array $data): \DOMElement
    {
        $event = $dom->createElement('event');
        $event->setAttribute('handle', $data['handle'] ?? uniqid('event_'));
        $event->setAttribute('change', $data['change'] ?? (string)time());
        $event->setAttribute('id', $data['id'] ?? '');
        
        if (isset($data['type'])) {
            $type = $dom->createElement('type', htmlspecialchars($data['type']));
            $event->appendChild($type);
        }
        
        if (isset($data['description'])) {
            $description = $dom->createElement('description', htmlspecialchars($data['description']));
            $event->appendChild($description);
        }
        
        return $event;
    }
}
