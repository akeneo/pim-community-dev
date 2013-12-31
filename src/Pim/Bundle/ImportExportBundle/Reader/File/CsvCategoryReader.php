<?php

namespace Pim\Bundle\ImportExportBundle\Reader\File;

/**
 * Csv file reader, reads the whole csv file to deal with category parents and circular references
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvCategoryReader extends CsvReader
{
    /**
     * Since this reader reads the whole file at once, store the executed state
     * and return null when read is called the second time to indicate completion
     *
     * @var boolean
     */
    private $executed = false;

    /**
     * {@inheritdoc}
     */
    public function setFilePath($filePath)
    {
        parent::setFilePath($filePath);
        $this->executed = false;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if ($this->executed) {
            return null;
        }
        $this->executed = true;

        $data = array();

        while ($row = parent::read()) {
            $data[] = $row;
        }

        return $data;
    }
}
