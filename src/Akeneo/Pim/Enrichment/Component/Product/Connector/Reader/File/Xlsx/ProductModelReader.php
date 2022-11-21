<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\File\Xlsx;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorFactory;
use Akeneo\Tool\Component\Connector\Reader\File\FileReaderInterface;
use Akeneo\Tool\Component\Connector\Reader\File\MediaPathTransformer;
use Akeneo\Tool\Component\Connector\Reader\File\Xlsx\Reader;

/**
 * Product model Xlsx reader
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelReader extends Reader implements FileReaderInterface
{
    /** @var MediaPathTransformer */
    private $mediaPathTransformer;

    /**
     * @param FileIteratorFactory     $fileIteratorFactory
     * @param ArrayConverterInterface $converter
     * @param array                   $options
     * @param MediaPathTransformer    $mediaPathTransformer
     */
    public function __construct(
        FileIteratorFactory $fileIteratorFactory,
        ArrayConverterInterface $converter,
        MediaPathTransformer $mediaPathTransformer,
        array $options = []
    ) {
        parent::__construct($fileIteratorFactory, $converter, $options);

        $this->mediaPathTransformer = $mediaPathTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $data = parent::read();

        if (!is_array($data) || !isset($data['values'])) {
            return $data;
        }

        $data['values'] = $this->mediaPathTransformer
            ->transform($data['values'], $this->fileIterator->getDirectoryPath());

        return $data;
    }

    /**
     * @return array
     */
    protected function getArrayConverterOptions()
    {
        $jobParameters = $this->stepExecution->getJobParameters();

        return [
            // for the array converters
            'mapping' => [
                $jobParameters->get('familyVariantColumn') => 'family_variant',
                $jobParameters->get('categoriesColumn') => 'categories',
            ],
            'with_associations' => false,

            // for the delocalization
            'decimal_separator' => $jobParameters->get('decimalSeparator'),
            'date_format' => $jobParameters->get('dateFormat'),
        ];
    }
}
