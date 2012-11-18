<?php
namespace Pim\Bundle\DataFlowBundle\Model\Extract;

use Pim\Bundle\DataFlowBundle\Model\Extract\FileReader;

/**
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CsvFileReader extends FileReader
{
    protected $_headers;
    protected $_rows;
    protected $_rowsWithHeaderAsKeys;
    const OPTIONKEY_HAS_HEADER = 'has_header';
    const OPTIONKEY_FILEPATH   = 'filepath';

    /**
     * (non-PHPdoc)
     */
    public function run($inputData = null)
    {
        $filepath = $this->getOption(self::OPTIONKEY_FILEPATH);
        $hasHeader = $this->getOption(self::OPTIONKEY_HAS_HEADER);
        $output = $this->loadContent($filepath, $hasHeader);

        return $output;
    }

    /**
     * @param string  $filepath
     * @param boolean $hasHeaders
     */
    public function loadContent($filepath, $hasHeaders = true)
    {
        // get CSV file content
        $this->_filepath = $filepath;
        $content = array();
        $handle = fopen($this->_filepath, 'r');
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
        // add notice message
        $msg = __CLASS__.' : read '.count($content).' lines from '.$filepath;
        $this->addMessage($msg);
        // merge to get header as key in each row
        if ($hasHeaders) {
            $this->_rowsWithHeaderAsKeys = array();
            foreach ($this->_rows as $line) {
                $this->_rowsWithHeaderAsKeys[] = array_combine($this->_headers, $line);
            }

            return $this->_rowsWithHeaderAsKeys;
        }

        return $this->_rows;
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
