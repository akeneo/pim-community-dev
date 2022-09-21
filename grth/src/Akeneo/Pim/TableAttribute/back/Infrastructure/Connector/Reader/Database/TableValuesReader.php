<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Connector\Reader\Database;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\DTO\TableRow;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Webmozart\Assert\Assert;

final class TableValuesReader implements ItemReaderInterface, TrackableItemReaderInterface, InitializableInterface, StepExecutionAwareInterface
{
    private ?StepExecution $stepExecution = null;
    protected ProductQueryBuilderFactoryInterface $pqbFactory;
    private ?CursorInterface $results;
    private ?EntityWithValuesInterface $currentEntity = null;
    private ?\Generator $tableRowGenerator = null;
    private bool $firstRead = true;

    public function __construct(ProductQueryBuilderFactoryInterface $pqbFactory)
    {
        $this->pqbFactory = $pqbFactory;
    }

    public function initialize(): void
    {
        $filters = $this->getConfiguredFilters();
        $tableAttributeCode = $filters['table_attribute_code'];

        $this->results = $this->pqbFactory->create([])->addFilter($tableAttributeCode, Operators::IS_NOT_EMPTY, [])
            ->execute();
        $this->results->rewind();
    }

    public function read(): ?TableRow
    {
        $filters = $this->getConfiguredFilters();
        Assert::keyExists($filters, 'table_attribute_code');
        $tableAttributeCode = $filters['table_attribute_code'];

        if ($this->firstRead) {
            $this->currentEntity = $this->getNextResult();
            if (null !== $this->currentEntity) {
                $this->tableRowGenerator = $this->getOneTableValue($this->currentEntity, $tableAttributeCode);
            }
            $this->firstRead = false;
        }

        while (null !== $this->currentEntity) {
            $tableRow = $this->getNextTableRow();
            if (null !== $tableRow) {
                return $tableRow;
            }

            $this->currentEntity = $this->getNextResult();
            if (null !== $this->currentEntity) {
                $this->tableRowGenerator = $this->getOneTableValue($this->currentEntity, $tableAttributeCode);
            }
        }

        return null;
    }

    public function totalItems(): int
    {
        if (null === $this->results) {
            throw new \RuntimeException('Class is not initialized.');
        }

        return $this->results->count();
    }

    private function getNextTableRow(): ?TableRow
    {
        if (null === $this->tableRowGenerator) {
            return null;
        }

        $tableRow = $this->tableRowGenerator->current();

        if (null !== $tableRow) {
            $this->tableRowGenerator->next();
            $this->stepExecution->incrementSummaryInfo('read');
        }

        return $tableRow;
    }

    private function getNextResult(): ?EntityWithValuesInterface
    {
        if ($this->results->valid()) {
            $result = $this->results->current();
            $this->results->next();

            return $result;
        }

        return null;
    }

    private function getOneTableValue(EntityWithValuesInterface $entityWithValues, string $attributeCode): \Generator
    {
        foreach ($entityWithValues->getValues() as $value) {
            if ($value->getAttributeCode() !== $attributeCode) {
                continue;
            }

            $table = $value->getData();
            foreach ($table as $row) {
                yield new TableRow(
                    $entityWithValues->getIdentifier(),
                    $attributeCode,
                    $value->getLocaleCode(),
                    $value->getScopeCode(),
                    $row
                );
            }
        }
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    private function getConfiguredFilters(): array
    {
        $filters = $this->stepExecution->getJobParameters()->get('filters');

        if (array_key_exists('data', $filters)) {
            $filters = $filters['data'];
        }

        return $filters;
    }
}
