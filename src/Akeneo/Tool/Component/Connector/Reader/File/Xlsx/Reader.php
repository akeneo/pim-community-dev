<?php

namespace Akeneo\Tool\Component\Connector\Reader\File\Xlsx;

use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\PausableReaderInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Exception\BusinessArrayConversionException;
use Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException;
use Akeneo\Tool\Component\Connector\Exception\InvalidItemFromViolationsException;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorFactory;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorInterface;
use Akeneo\Tool\Component\Connector\Reader\File\FileReaderInterface;

/**
 * Xlsx Reader
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Reader implements FileReaderInterface, TrackableItemReaderInterface, PausableReaderInterface
{
    /** @var FileIteratorFactory */
    protected $fileIteratorFactory;

    /** ArrayConverterInterface */
    protected $converter;

    /** @var FileIteratorInterface */
    protected $fileIterator;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var array */
    protected $options;

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
        $jobParameters = $this->stepExecution->getJobParameters();
        $filePath = $jobParameters->get('storage')['file_path'];
        $iterator = $this->fileIteratorFactory->create($filePath, $this->options);

        return max(iterator_count($iterator) - 1, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $this->initFileIterator();

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
            $dataMask = array_fill(0, $countHeaders, '');
            $data = array_replace($dataMask, $data);
        }

        $item = array_combine($headers, $data);

        try {
            $item = $this->converter->convert($item, $this->getArrayConverterOptions());
        } catch (BusinessArrayConversionException $exception) {
            throw new InvalidItemException(
                $exception->getMessageKey(),
                new FileInvalidItem($item, ($this->stepExecution->getSummaryInfo('item_position'))),
                $exception->getMessageParameters(),
                $exception->getCode(),
                $exception
            );
        } catch (DataArrayConversionException $e) {
            $this->skipItemFromConversionException($item, $e);
        }

        return $item;
    }

    private function initFileIterator(): void
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $filePath = $jobParameters->get('storage')['file_path'];

        if (null === $this->fileIterator) {
            $this->fileIterator = $this->fileIteratorFactory->create($filePath, $this->options);
            $this->fileIterator->rewind();
        }
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

        throw new InvalidItemException(
            $exception->getMessage(),
            new FileInvalidItem($item, ($this->stepExecution->getSummaryInfo('item_position'))),
            [],
            0,
            $exception
        );
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

    public function getState(): array
    {
        return null !== $this->fileIterator ? ['position' => $this->fileIterator->key()] : [];
    }

    public function rewindToState(int $key): void
    {
        $this->initFileIterator();

        $this->fileIterator->current();
        while ($this->fileIterator->key() < $key) {
            $this->fileIterator->next();
        }
    }
}
