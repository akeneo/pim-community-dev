<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\File\Xlsx;

use Akeneo\Tool\Component\Connector\Reader\File\FileReaderInterface;
use Akeneo\Tool\Component\Connector\Reader\File\Xlsx\Reader;

/**
 * Product Association XLSX reader
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAssociationReader extends Reader implements FileReaderInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getArrayConverterOptions()
    {
        $jobParameters = $this->stepExecution->getJobParameters();

        return [
            // for the array converters
            'mapping'           => [
                $jobParameters->get('familyColumn')     => 'family',
                $jobParameters->get('categoriesColumn') => 'categories',
                $jobParameters->get('groupsColumn')     => 'groups'
            ],
            'with_associations' => true,

            // for the delocalization
            'decimal_separator' => $jobParameters->get('decimalSeparator'),
            'date_format'       => $jobParameters->get('dateFormat')
        ];
    }
}
