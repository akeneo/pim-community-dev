<?php

namespace Pim\Bundle\ImportExportBundle\Reader;

use Oro\Bundle\BatchBundle\Entity\StepExecution;

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
     * Since this reader reads the whole file at once, store the executed state
     * and return null when read is called the second time to indicate completion
     *
     * @var boolean
     */
    private $executed = false;

    /**
     * {@inheritdoc}
     */
    public function read(StepExecution $stepExecution)
    {
        if ($this->executed) {
            return null;
        }

        $this->executed = true;

        $data = array();

        while ($row = parent::read($stepExecution)) {
            $data[] = $row;
        }

        return $data;
    }
}
