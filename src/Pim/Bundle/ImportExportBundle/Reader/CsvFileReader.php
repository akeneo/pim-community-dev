<?php

namespace Pim\Bundle\ImportExportBundle\Reader;

/**
 * Csv file reader
 * Reads the whole csv file
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvFileReader extends CsvReader
{
    /**
     * {@inheritDoc}
     */
    public function read()
    {
        $data = array();

        while ($row = parent::read()) {
            $data[] = $row;
        }

        return $data;
    }
}
