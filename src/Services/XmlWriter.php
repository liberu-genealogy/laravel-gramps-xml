<?php

namespace LaravelGrampsXml\Services;

use Exception;

class XmlWriter {
  public function write($filePath, $content) {
    if (file_put_contents($filePath, $content) === false) {
      throw new Exception("Failed to write to file {$filePath}");
    }
    
    // Add necessary XML tags and attributes
    $xmlContent = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
    $xmlContent .= '<!DOCTYPE grampsxml SYSTEM "https://github.com/gramps-project/gramps/blob/master/data/grampsxml.dtd">' . PHP_EOL;
    $xmlContent .= '<grampsxml>' . PHP_EOL;
    $xmlContent .= $content;
    $xmlContent .= '</grampsxml>' . PHP_EOL;
    
    // Write the XML content to the file
    if (file_put_contents($filePath, $xmlContent) === false) {
      throw new Exception("Failed to write to file {$filePath}");
    }
  }
}
    $xmlContent = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
    $xmlContent .= '<!DOCTYPE grampsxml SYSTEM "https://github.com/gramps-project/gramps/blob/master/data/grampsxml.dtd">' . PHP_EOL;
    $xmlContent .= '<grampsxml>' . PHP_EOL;
    $xmlContent .= $content;
    $xmlContent .= '</grampsxml>' . PHP_EOL;
    
    // Write the XML content to the file
    if (file_put_contents($filePath, $xmlContent) === false) {
      throw new Exception("Failed to write to file {$filePath}");
    }
  }
}
