<?php

namespace Pim\Component\Connector\Reader\File\Csv;

/**
 * Product Association CSV reader
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvAssociationReader extends CsvReader
{
    /**
     * {@inheritdoc}
     */
    protected function getArrayConverterOptions()
    {
        return ['with_associations' => true];
    }
}
