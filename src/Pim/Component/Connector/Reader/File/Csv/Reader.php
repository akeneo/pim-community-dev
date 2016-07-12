<?php

namespace Pim\Component\Connector\Reader\File\Csv;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\FileInvalidItem;
use Akeneo\Component\Batch\Item\FlushableInterface;
use Akeneo\Component\Batch\Item\InvalidItemException;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\Exception\DataArrayConversionException;
use Pim\Component\Connector\Exception\InvalidItemFromViolationsException;
use Pim\Component\Connector\Reader\File\FileIteratorFactory;
use Pim\Component\Connector\Reader\File\FileIteratorInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Csv reader
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Reader extends AbstractConfigurableStepElement implements
    ItemReaderInterface,
    StepExecutionAwareInterface,
    FlushableInterface
{
    /** @var FileIteratorFactory */
    protected $fileIteratorFactory;

    /** @var ArrayConverterInterface */
    protected $converter;

    /** @var FileIteratorInterface */
    protected $fileIterator;

    /** @var StepExecution */
    protected $stepExecution;

    /**
     * @param FileIteratorFactory     $fileIteratorFactory
     * @param ArrayConverterInterface $converter
     */
    public function __construct(FileIteratorFactory $fileIteratorFactory, ArrayConverterInterface $converter)
    {
        $this->fileIteratorFactory = $fileIteratorFactory;
        $this->converter           = $converter;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $filePath = null;
        if (null === $this->fileIterator) {
            $jobParameters = $this->stepExecution->getJobParameters();
            $filePath = $jobParameters->get('filePath');
            $delimiter = $jobParameters->get('delimiter');
            $enclosure = $jobParameters->get('enclosure');
            $this->fileIterator = $this->fileIteratorFactory->create($filePath, [
                'fieldDelimiter' => $delimiter,
                'fieldEnclosure' => $enclosure
            ]);
            $this->fileIterator->rewind();
        }

        $this->fileIterator->next();

        if ($this->fileIterator->valid() && null !== $this->stepExecution) {
            $this->stepExecution->incrementSummaryInfo('read_lines');
        }

        $item = $this->fileIterator->current();

        if (null === $item) {
            return null;
        }

        $headers = $this->fileIterator->getHeaders();

        $countHeaders = count($headers);
        $countData    = count($item);

        $this->checkColumnNumber($countHeaders, $countData, $item, $filePath);

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
                new FileInvalidItem($item, ($this->stepExecution->getSummaryInfo('read_lines') + 1)),
                [],
                0,
                $exception
            );
        }

        $invalidItem = new FileInvalidItem(
            $item,
            ($this->stepExecution->getSummaryInfo('read_lines') + 1)
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
                new FileInvalidItem($data, ($this->stepExecution->getSummaryInfo('read_lines') + 1)),
                [
                    '%totalColumnsCount%' => $countHeaders,
                    '%itemColumnsCount%'  => $countData,
                    '%filePath%'          => $filePath,
                    '%lineno%'            => $this->fileIterator->key()
                ]
            );
        }
    }
}
