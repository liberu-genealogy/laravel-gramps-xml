<?php

namespace LaravelGrampsXml\Services;

use Exception;

class XmlWriter {
  public function write($filePath, $content) {
    if (file_put_contents($filePath, $content) === false) {
      throw new Exception("Failed to write to file {$filePath}");
    }
  }
}
