<?php

namespace LaravelGrampsXml;

class XmlWriter
{
    public function write($filePath, $xmlContent)
    {
        $result = file_put_contents($filePath, $xmlContent);
        if ($result === false) {
            throw new \Exception("Failed to write XML content to file: {$filePath}");
        }
    }
}
