<?php

namespace LaravelGrampsXml\Services;

use Exception;

class XmlReader {
  public function read($filePath) {
    if (!is_readable($filePath)) {
      throw new Exception("File {$filePath} cannot be read");
    }

    $xmlContent = file_get_contents($filePath);
    if ($xmlContent === false) {
      throw new Exception("Failed to read file {$filePath}");
    }

    return $xmlContent;
  }
}
