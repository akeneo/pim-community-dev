<?php

declare(strict_types=1);

namespace Pim\Component\Connector\Reader\File\Csv;

use Akeneo\Component\Batch\Item\FlushableInterface;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\Reader\File\FileIteratorFactory;
use Pim\Component\Connector\Reader\File\FileIteratorInterface;
use Pim\Component\Connector\Reader\File\MediaPathTransformer;

/**
 * Product model Csv reader
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RootProductModelReader extends Reader implements
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

        while (!$this->shouldLaunchComputeDescendantsFrom($data)) {
            $data = parent::read();
        }

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

    private function shouldLaunchComputeDescendantsFrom($data): bool
    {
        return $this->isRootProductModel($data) || !$this->isParentPresentInFile($data);
    }

    /**
     * @param $data
     *
     * @return bool
     *
     */
    protected function isRootProductModel($data): bool
    {
        return !isset($data['parent']) || null === $data['parent'] || '' === $data['parent'];
    }

    private function isParentPresentInFile(array $data): bool
    {
        $fileIterator = $this->createNewFilteIterator();
        $found = false;
        $fileIterator->rewind();

        while(!$found && $fileIterator->valid()) {
            $fileIterator->next();
            $itemRead = $fileIterator->current();

            $item = array_combine($fileIterator->getHeaders(), $itemRead);
            $found = $data['parent'] === $item['code'];
        }

        return $found;
    }

    /**
     * @param $jobParameters
     * @param $filePath
     *
     */
    private function createNewFilteIterator(): FileIteratorInterface
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $filePath = $jobParameters->get('filePath');
        $delimiter = $jobParameters->get('delimiter');
        $enclosure = $jobParameters->get('enclosure');
        $defaultOptions = [
            'reader_options' => [
                'fieldDelimiter' => $delimiter,
                'fieldEnclosure' => $enclosure,
            ],
        ];
        $fileIterator = $this->fileIteratorFactory->create(
            $filePath,
            array_merge($defaultOptions, $this->options)
        );

        return $fileIterator;
    }
}
