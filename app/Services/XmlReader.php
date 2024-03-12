<?php

namespace App\Services;

use SimpleXMLElement;
use Exception;
use InvalidArgumentException;

class XmlReader
{
    public function read(string $filePath): SimpleXMLElement
    {
        if (!file_exists($filePath)) {
            throw new Exception("File not found: {$filePath}");
        }

        set_error_handler(function($severity, $message, $file, $line) {
            throw new InvalidArgumentException($message, $severity);
        });

        try {
            $xml = simplexml_load_file($filePath);
            if ($xml === false) {
                throw new InvalidArgumentException("Failed to parse XML file: {$filePath}");
            }
            return $xml;
        } finally {
            restore_error_handler();
        }
    }
}
