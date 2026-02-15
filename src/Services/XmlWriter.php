<?php

namespace LaravelGrampsXml\Services;

use LaravelGrampsXml\XmlWriter as BaseXmlWriter;
use Exception;

/**
 * Service class for writing Gramps XML files
 * Extends the base XmlWriter with additional service-oriented functionality
 */
class XmlWriter extends BaseXmlWriter
{
    /**
     * Write Gramps XML content to a file with proper formatting
     *
     * @param string $filePath
     * @param mixed $content Can be array or string
     * @throws Exception
     */
    public function write($filePath, $content)
    {
        // If content is an array, convert it to Gramps XML format
        if (is_array($content)) {
            $xmlContent = $this->createGrampsXml($content);
        } else {
            // If content is already a string, ensure it has proper Gramps XML structure
            $xmlContent = $this->ensureGrampsXmlStructure($content);
        }
        
        // Write the XML content to the file
        if (file_put_contents($filePath, $xmlContent) === false) {
            throw new Exception("Failed to write to file {$filePath}");
        }
    }

    /**
     * Ensure content has proper Gramps XML structure
     *
     * @param string $content
     * @return string
     */
    private function ensureGrampsXmlStructure(string $content): string
    {
        // If content already starts with XML declaration, return as is
        if (strpos($content, '<?xml') === 0) {
            return $content;
        }

        // Otherwise, wrap the content in proper Gramps XML structure
        $xmlContent = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xmlContent .= '<!DOCTYPE database PUBLIC "-//GRAMPS//DTD GRAMPS XML V1.7.2//EN"' . PHP_EOL;
        $xmlContent .= '    "http://gramps-project.org/xml/1.7.2/grampsxml.dtd">' . PHP_EOL;
        
        // If content doesn't have a database tag, add it
        if (strpos($content, '<database') === false) {
            $xmlContent .= '<database xmlns="http://gramps-project.org/xml/1.7.2/">' . PHP_EOL;
            $xmlContent .= $content;
            $xmlContent .= '</database>' . PHP_EOL;
        } else {
            $xmlContent .= $content;
        }
        
        return $xmlContent;
    }
}
