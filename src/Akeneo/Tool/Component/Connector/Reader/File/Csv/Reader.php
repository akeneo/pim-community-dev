<?php

namespace Akeneo\Tool\Component\Connector\Reader\File\Csv;

use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\StatefulInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableItemReaderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException;
use Akeneo\Tool\Component\Connector\Exception\InvalidItemFromViolationsException;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorFactory;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorInterface;
use Akeneo\Tool\Component\Connector\Reader\File\FileReaderInterface;

/**
 * Csv reader
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Reader implements FileReaderInterface, TrackableItemReaderInterface, InitializableInterface, StatefulInterface
{
    /** @var FileIteratorFactory */
    protected $fileIteratorFactory;

    /** @var ArrayConverterInterface */
    protected $converter;

    /** @var FileIteratorInterface */
    protected $fileIterator;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var array */
    protected $options;

    protected array $state = [];

    /**
     * @param FileIteratorFactory     $fileIteratorFactory
     * @param ArrayConverterInterface $converter
     * @param array                   $options
     */
    public function __construct(
        FileIteratorFactory $fileIteratorFactory,
        ArrayConverterInterface $converter,
        array $options = []
    ) {
        $this->fileIteratorFactory = $fileIteratorFactory;
        $this->converter = $converter;
        $this->options = $options;
    }

    public function totalItems(): int
    {
        $totalItems = max(iterator_count($this->fileIterator) - 1, 0);
        $this->fileIterator->rewind();
        return $totalItems;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $this->fileIterator->next();

        if ($this->fileIterator->valid() && null !== $this->stepExecution) {
            $this->stepExecution->incrementSummaryInfo('item_position');
        }

        $data = $this->fileIterator->current();

        if (null === $data) {
            return null;
        }

        $headers = $this->fileIterator->getHeaders();

        $countHeaders = count($headers);
        $countData = count($data);

        $this->checkColumnNumber($countHeaders, $countData, $data, $this->stepExecution->getJobParameters()->get('storage')['file_path']);

        if ($countHeaders > $countData) {
            $missingValuesCount = $countHeaders - $countData;
            $missingValues = array_fill(0, $missingValuesCount, '');
            $data = array_merge($data, $missingValues);
        }

        $item = array_combine($this->fileIterator->getHeaders(), $data);

        try {
            $item = $this->converter->convert($item, $this->getArrayConverterOptions());
        } catch (DataArrayConversionException $e) {
            $this->skipItemFromConversionException($item, $e);
        }

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $this->fileIterator = null;
    }

    /**
     * Returns the options for array converter. It can be overridden in the sub classes.
     *
     * @return array
     */
    protected function getArrayConverterOptions()
    {
        return [];
    }

    /**
     * @param array                        $item
     * @param DataArrayConversionException $exception
     *
     * @throws InvalidItemException
     * @throws InvalidItemFromViolationsException
     */
    protected function skipItemFromConversionException(array $item, DataArrayConversionException $exception)
    {
        if (null !== $this->stepExecution) {
            $this->stepExecution->incrementSummaryInfo('skip');
        }

        if (null !== $exception->getViolations()) {
            throw new InvalidItemFromViolationsException(
                $exception->getViolations(),
                new FileInvalidItem($item, ($this->stepExecution->getSummaryInfo('item_position'))),
                [],
                0,
                $exception
            );
        }

        $invalidItem = new FileInvalidItem(
            $item,
            ($this->stepExecution->getSummaryInfo('item_position'))
        );

        throw new InvalidItemException($exception->getMessage(), $invalidItem, [], 0, $exception);
    }

    /**
     * @param int    $countHeaders
     * @param int    $countData
     * @param string $data
     * @param string $filePath
     *
     * @throws InvalidItemException
     */
    protected function checkColumnNumber($countHeaders, $countData, $data, $filePath)
    {
        if ($countHeaders < $countData) {
            throw new InvalidItemException(
                'pim_connector.steps.file_reader.invalid_item_columns_count',
                new FileInvalidItem($data, ($this->stepExecution->getSummaryInfo('item_position'))),
                [
                    '%totalColumnsCount%' => $countHeaders,
                    '%itemColumnsCount%'  => $countData,
                    '%filePath%'          => $filePath,
                    '%lineno%'            => $this->fileIterator->key()
                ]
            );
        }
    }

    private function createFileIterator(
        JobParameters $jobParameters,
        string $filePath
    ): FileIteratorInterface {
        $delimiter = $jobParameters->get('delimiter');
        $enclosure = $jobParameters->get('enclosure');
        $defaultOptions = [
            'reader_options' => [
                'fieldDelimiter' => $delimiter,
                'fieldEnclosure' => $enclosure,
            ],
        ];

        return $this->fileIteratorFactory->create(
            $filePath,
            array_merge($defaultOptions, $this->options)
        );
    }

    public function getState(): array
    {
        return null !== $this->fileIterator ? ['position' => $this->fileIterator->key()] : [];
    }

    public function setState(array $state): void
    {
        $this->state = $state;
    }

    public function initialize(): void
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $filePath = $jobParameters->get('storage')['file_path'];

        $this->fileIterator = $this->createFileIterator($jobParameters, $filePath);
        $this->fileIterator->rewind();

        if (!array_key_exists('position', $this->state)) {
            return;
        }

        while ($this->fileIterator->key() < $this->state['position']) {
            $this->fileIterator->next();
        }
    }
}
