<?php
namespace Strixos\DataFlowBundle\Model\Extract;

use Strixos\DataFlowBundle\Model\Extract\FileReader;

/**
 *
 * @author    Nicolas Dupont @ Strixos
 * @copyright Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CsvFileReader extends FileReader
{
    protected $_headers;
    protected $_rows;

    /**
     * @param string $filepath
     * @param boolean $hasHeaders
     */
    public function loadContent($filepath, $hasHeaders = true)
    {
        // get CSV file content
        $this->_filepath = $filepath;
        $content = array();
        $handle = fopen($filename, 'r');
        while (!feof($handle) && $line = fgetcsv($handle, 0, ',', '"')) {
            $content[] = $line;
        }
        fclose($handle);
        // retrieve first line which contains headers
        if ($hasHeaders) {
            $headers = $content[0];
            unset($content[0]);
            $this->_headers = $headers;
        }
        $this->_rows = $content;
    }

    /**
    * Get file headers
    * @return array
    */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * Get file rows
     * @return array
     */
    public function getRows()
    {
        return $this->_rows;
    }

}