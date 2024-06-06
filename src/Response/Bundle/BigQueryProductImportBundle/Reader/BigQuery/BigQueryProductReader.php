<?php

namespace Response\Bundle\BigQueryProductImportBundle\Reader\BigQuery;

use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException;
use Akeneo\Tool\Component\Connector\Exception\InvalidItemFromViolationsException;
use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\Core\Iterator\ItemIterator;

class BigQueryProductReader implements
    ItemReaderInterface,
    StepExecutionAwareInterface,
    FlushableInterface,
    TrackableItemReaderInterface
{

    /** @var ItemIterator */
    protected $bigQueryIterator;

    /** @var int */
    protected $totalRows;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var ArrayConverterInterface */
    protected $converter;

    /**
     * @param ArrayConverterInterface $converter
     */
    public function __construct(ArrayConverterInterface $converter)
    {
        $this->converter = $converter;
    }

    public function getBigQueryIterator()
    {
        if (null === $this->bigQueryIterator) {
            $jobParameters = $this->stepExecution->getJobParameters();
            $bigQueryDataset = $jobParameters->get('bigquery_dataset');

            // Construct and execute a query to retrieve the products to
            // import from BigQuery
            $bigQuery = new BigQueryClient();
            $queryJobConfig = $bigQuery->query(
                "SELECT * FROM `{$bigQueryDataset}`"
            );
            $queryResults = $bigQuery->runQuery($queryJobConfig);
            $this->totalRows = $queryResults->info()['totalRows'];
            $this->bigQueryIterator = $queryResults->getIterator();
        }
        return $this->bigQueryIterator;
    }

    public function read()
    {
        if ($data = $this->getBigQueryIterator()->current()) {
            $item = $data;
            $this->bigQueryIterator->next();

            if (null !== $this->stepExecution) {
                $this->stepExecution->incrementSummaryInfo('item_position');
            }

            try {
                $item = $this->converter->convert($item);
            } catch (DataArrayConversionException $e) {
                $this->skipItemFromConversionException($this->bigQueryIterator->current(), $e);
            }

            return $item;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function totalItems(): int
    {
        $this->getBigQueryIterator();
        return $this->totalRows;
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $this->bigQueryIterator = null;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
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
                new FileInvalidItem($item, $this->stepExecution->getSummaryInfo('item_position')),
                [],
                0,
                $exception
            );
        }

        $invalidItem = new FileInvalidItem(
            $item,
            $this->stepExecution->getSummaryInfo('item_position')
        );

        throw new InvalidItemException($exception->getMessage(), $invalidItem, [], 0, $exception);
    }
}
