<?php

namespace LaravelGrampsXml;

use SimpleXMLElement;
use DOMDocument;
use Exception;
use InvalidArgumentException;

class XmlReader
{
    /**
     * Read and parse a Gramps XML file
     *
     * @param string $filePath
     * @return SimpleXMLElement
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function read(string $filePath): SimpleXMLElement
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
            return $xml;
        } finally {
            restore_error_handler();
        }
    }

    /**
     * Read and validate a Gramps XML file against local DTD
     *
     * @param string $filePath
     * @return SimpleXMLElement
     * @throws Exception
     */
    public function readAndValidate(string $filePath): SimpleXMLElement
    {
        if (!file_exists($filePath)) {
            throw new Exception("File not found: {$filePath}");
        }

        $dtdPath = __DIR__ . '/../dtd/grampsxml.dtd';
        
        if (!file_exists($dtdPath)) {
            throw new Exception("DTD file not found at: {$dtdPath}");
        }

        libxml_use_internal_errors(true);
        libxml_disable_entity_loader(false);
        
        $dom = new DOMDocument();
        $dom->load($filePath);
        
        // Read the DTD content and create a temporary file with modified DOCTYPE
        $xmlContent = file_get_contents($filePath);
        
        // Replace the remote DTD reference with a local file path
        $dtdSystemId = 'file://' . realpath($dtdPath);
        $xmlContent = preg_replace(
            '/<!DOCTYPE\s+database\s+PUBLIC[^>]+>/',
            '<!DOCTYPE database SYSTEM "' . $dtdSystemId . '">',
            $xmlContent
        );
        
        // Create a new DOM from the modified content
        $dom = new DOMDocument();
        $dom->loadXML($xmlContent);
        
        // Now validate against the local DTD
        $valid = @$dom->validate();
        
        if (!$valid) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            
            if (!empty($errors)) {
                $errorMessages = array_map(function($error) {
                    return sprintf(
                        "[Line %d] %s",
                        $error->line,
                        trim($error->message)
                    );
                }, $errors);
                
                throw new Exception("XML validation failed:\n" . implode("\n", $errorMessages));
            }
        }
        
        libxml_use_internal_errors(false);
        
        return simplexml_import_dom($dom);
    }

    /**
     * Parse Gramps XML and extract structured data
     *
     * @param string $filePath
     * @return array
     * @throws Exception
     */
    public function parseGrampsXml(string $filePath): array
    {
        $xml = $this->read($filePath);
        
        $data = [
            'header' => $this->parseHeader($xml),
            'people' => $this->parsePeople($xml),
            'families' => $this->parseFamilies($xml),
            'events' => $this->parseEvents($xml),
            'places' => $this->parsePlaces($xml),
            'sources' => $this->parseSources($xml),
            'citations' => $this->parseCitations($xml),
            'repositories' => $this->parseRepositories($xml),
            'notes' => $this->parseNotes($xml),
            'tags' => $this->parseTags($xml),
        ];
        
        return $data;
    }

    /**
     * Parse header information
     *
     * @param SimpleXMLElement $xml
     * @return array
     */
    private function parseHeader(SimpleXMLElement $xml): array
    {
        $header = [];
        
        if (isset($xml->header)) {
            if (isset($xml->header->created)) {
                $header['created'] = [
                    'date' => (string) $xml->header->created['date'],
                    'version' => (string) $xml->header->created['version'],
                ];
            }
            
            if (isset($xml->header->researcher)) {
                $researcher = $xml->header->researcher;
                $header['researcher'] = [
                    'resname' => (string) $researcher->resname,
                    'resaddr' => (string) $researcher->resaddr,
                    'rescity' => (string) $researcher->rescity,
                    'resstate' => (string) $researcher->resstate,
                    'rescountry' => (string) $researcher->rescountry,
                    'respostal' => (string) $researcher->respostal,
                    'resphone' => (string) $researcher->resphone,
                    'resemail' => (string) $researcher->resemail,
                ];
            }
            
            if (isset($xml->header->mediapath)) {
                $header['mediapath'] = (string) $xml->header->mediapath;
            }
        }
        
        return $header;
    }

    /**
     * Parse people
     *
     * @param SimpleXMLElement $xml
     * @return array
     */
    private function parsePeople(SimpleXMLElement $xml): array
    {
        $people = [];
        
        if (isset($xml->people->person)) {
            foreach ($xml->people->person as $person) {
                $personData = [
                    'id' => (string) $person['id'],
                    'handle' => (string) $person['handle'],
                    'change' => (string) $person['change'],
                    'priv' => isset($person['priv']) ? (string) $person['priv'] : null,
                    'gender' => (string) $person->gender,
                    'names' => [],
                ];
                
                if (isset($person->name)) {
                    foreach ($person->name as $name) {
                        $personData['names'][] = [
                            'type' => isset($name['type']) ? (string) $name['type'] : 'Unknown',
                            'first' => (string) $name->first,
                            'surname' => (string) $name->surname,
                            'suffix' => (string) $name->suffix,
                            'title' => (string) $name->title,
                        ];
                    }
                }
                
                $people[] = $personData;
            }
        }
        
        return $people;
    }

    /**
     * Parse families
     *
     * @param SimpleXMLElement $xml
     * @return array
     */
    private function parseFamilies(SimpleXMLElement $xml): array
    {
        $families = [];
        
        if (isset($xml->families->family)) {
            foreach ($xml->families->family as $family) {
                $familyData = [
                    'id' => (string) $family['id'],
                    'handle' => (string) $family['handle'],
                    'change' => (string) $family['change'],
                    'father' => isset($family->father) ? (string) $family->father['hlink'] : null,
                    'mother' => isset($family->mother) ? (string) $family->mother['hlink'] : null,
                    'children' => [],
                ];
                
                if (isset($family->childref)) {
                    foreach ($family->childref as $child) {
                        $familyData['children'][] = [
                            'hlink' => (string) $child['hlink'],
                            'mrel' => isset($child['mrel']) ? (string) $child['mrel'] : null,
                            'frel' => isset($child['frel']) ? (string) $child['frel'] : null,
                        ];
                    }
                }
                
                $families[] = $familyData;
            }
        }
        
        return $families;
    }

    /**
     * Parse events
     *
     * @param SimpleXMLElement $xml
     * @return array
     */
    private function parseEvents(SimpleXMLElement $xml): array
    {
        $events = [];
        
        if (isset($xml->events->event)) {
            foreach ($xml->events->event as $event) {
                $events[] = [
                    'id' => (string) $event['id'],
                    'handle' => (string) $event['handle'],
                    'change' => (string) $event['change'],
                    'type' => (string) $event->type,
                    'description' => (string) $event->description,
                ];
            }
        }
        
        return $events;
    }

    /**
     * Parse places
     *
     * @param SimpleXMLElement $xml
     * @return array
     */
    private function parsePlaces(SimpleXMLElement $xml): array
    {
        $places = [];
        
        if (isset($xml->places->placeobj)) {
            foreach ($xml->places->placeobj as $place) {
                $places[] = [
                    'id' => (string) $place['id'],
                    'handle' => (string) $place['handle'],
                    'change' => (string) $place['change'],
                    'type' => (string) $place['type'],
                    'ptitle' => (string) $place->ptitle,
                ];
            }
        }
        
        return $places;
    }

    /**
     * Parse sources
     *
     * @param SimpleXMLElement $xml
     * @return array
     */
    private function parseSources(SimpleXMLElement $xml): array
    {
        $sources = [];
        
        if (isset($xml->sources->source)) {
            foreach ($xml->sources->source as $source) {
                $sources[] = [
                    'id' => (string) $source['id'],
                    'handle' => (string) $source['handle'],
                    'change' => (string) $source['change'],
                    'stitle' => (string) $source->stitle,
                    'sauthor' => (string) $source->sauthor,
                ];
            }
        }
        
        return $sources;
    }

    /**
     * Parse citations
     *
     * @param SimpleXMLElement $xml
     * @return array
     */
    private function parseCitations(SimpleXMLElement $xml): array
    {
        $citations = [];
        
        if (isset($xml->citations->citation)) {
            foreach ($xml->citations->citation as $citation) {
                $citations[] = [
                    'id' => (string) $citation['id'],
                    'handle' => (string) $citation['handle'],
                    'change' => (string) $citation['change'],
                    'page' => (string) $citation->page,
                    'confidence' => (string) $citation->confidence,
                ];
            }
        }
        
        return $citations;
    }

    /**
     * Parse repositories
     *
     * @param SimpleXMLElement $xml
     * @return array
     */
    private function parseRepositories(SimpleXMLElement $xml): array
    {
        $repositories = [];
        
        if (isset($xml->repositories->repository)) {
            foreach ($xml->repositories->repository as $repo) {
                $repositories[] = [
                    'id' => (string) $repo['id'],
                    'handle' => (string) $repo['handle'],
                    'change' => (string) $repo['change'],
                    'rname' => (string) $repo->rname,
                ];
            }
        }
        
        return $repositories;
    }

    /**
     * Parse notes
     *
     * @param SimpleXMLElement $xml
     * @return array
     */
    private function parseNotes(SimpleXMLElement $xml): array
    {
        $notes = [];
        
        if (isset($xml->notes->note)) {
            foreach ($xml->notes->note as $note) {
                $notes[] = [
                    'id' => (string) $note['id'],
                    'handle' => (string) $note['handle'],
                    'change' => (string) $note['change'],
                    'type' => (string) $note['type'],
                    'text' => (string) $note->text,
                ];
            }
        }
        
        return $notes;
    }

    /**
     * Parse tags
     *
     * @param SimpleXMLElement $xml
     * @return array
     */
    private function parseTags(SimpleXMLElement $xml): array
    {
        $tags = [];
        
        if (isset($xml->tags->tag)) {
            foreach ($xml->tags->tag as $tag) {
                $tags[] = [
                    'handle' => (string) $tag['handle'],
                    'name' => (string) $tag['name'],
                    'color' => (string) $tag['color'],
                    'priority' => (string) $tag['priority'],
                    'change' => (string) $tag['change'],
                ];
            }
        }
        
        return $tags;
    }
}
