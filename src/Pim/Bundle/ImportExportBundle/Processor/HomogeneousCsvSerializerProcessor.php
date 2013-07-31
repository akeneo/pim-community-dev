<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

/**
 * Serialize homogeneous data into csv
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HomogeneousCsvSerializerProcessor extends CsvSerializerProcessor
{
    /**
     * {@inheritDoc}
     */
    public function process($item)
    {
        return $this->serializer->serialize(
            $item,
            'csv',
            array(
                'delimiter'     => $this->delimiter,
                'enclosure'     => $this->enclosure,
                'withHeader'    => $this->withHeader,
                'heterogeneous' => false,
            )
        );
    }
}

