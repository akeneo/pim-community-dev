<?php

namespace Pim\Bundle\ImportExportBundle\Processor\CsvSerializer;

/**
 * Serialize heterogeneous data into csv
 *
 * It allows to serialize a collection of array of different length into csv.
 * It will put empty values for value that are not defined.
 * It only works for collection of HASHES (otherwise it can't compute the columns).
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HeterogeneousProcessor extends Processor
{
    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $nbItems = count($item) - ($this->isWithHeader() ? 1 : 0);
        $this->stepExecution->addSummaryInfo('write', $nbItems);

        return $this->serializer->serialize(
            $item,
            'csv',
            array(
                'delimiter'     => $this->delimiter,
                'enclosure'     => $this->enclosure,
                'withHeader'    => $this->withHeader,
                'heterogeneous' => true,
            )
        );
    }
}
