<?php

declare(strict_types=1);

namespace Pim\Component\Connector\Reader\File\Csv;

use Akeneo\Component\Batch\Item\FlushableInterface;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\Reader\File\FileIteratorFactory;
use Pim\Component\Connector\Reader\File\MediaPathTransformer;

/**
 * Product model Csv reader
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelReader extends Reader implements
    ItemReaderInterface,
    StepExecutionAwareInterface,
    FlushableInterface
{
    /** @var MediaPathTransformer */
    private $mediaPathTransformer;

    /**
     * @param FileIteratorFactory     $fileIteratorFactory
     * @param ArrayConverterInterface $converter
     * @param MediaPathTransformer    $mediaPathTransformer
     * @param array                   $options
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
    protected function getArrayConverterOptions(): array
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
