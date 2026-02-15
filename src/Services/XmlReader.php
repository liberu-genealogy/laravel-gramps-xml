<?php

namespace LaravelGrampsXml\Services;

use LaravelGrampsXml\XmlReader as BaseXmlReader;
use SimpleXMLElement;
use Exception;

/**
 * Service class for reading Gramps XML files
 * Extends the base XmlReader with additional service-oriented functionality
 */
class XmlReader extends BaseXmlReader
{
    /**
     * Read Gramps XML file and return parsed data as array
     *
     * @param string $filePath
     * @return array
     * @throws Exception
     */
    public function readAsArray(string $filePath): array
    {
        // Use parent's parseGrampsXml method to get structured data
        return $this->parseGrampsXml($filePath);
    }

    /**
     * Import Gramps XML data
     * This method can be extended to integrate with Laravel models
     *
     * @param string $filePath
     * @return array
     * @throws Exception
     */
    public function import(string $filePath): array
    {
        $data = $this->parseGrampsXml($filePath);
        
        // Process and return import statistics
        $stats = [
            'people_count' => count($data['people']),
            'families_count' => count($data['families']),
            'events_count' => count($data['events']),
            'places_count' => count($data['places']),
            'sources_count' => count($data['sources']),
            'citations_count' => count($data['citations']),
            'repositories_count' => count($data['repositories']),
            'notes_count' => count($data['notes']),
            'tags_count' => count($data['tags']),
        ];
        
        return [
            'data' => $data,
            'stats' => $stats,
        ];
    }
}

