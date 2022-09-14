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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Connector\Writer\Database;

use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\DTO\TableRow;
use Akeneo\Pim\TableAttribute\Infrastructure\Normalizer\Standard\TableNormalizer;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

final class ProductTableValues implements ItemWriterInterface, StepExecutionAwareInterface
{
    private IdentifiableObjectRepositoryInterface $entityRepository;
    private AttributeRepositoryInterface $attributeRepository;
    private EntityWithValuesBuilderInterface $entityWithValuesBuilder;
    private TableNormalizer $tableNormalizer;
    private TableConfigurationRepository $tableConfigurationRepository;
    private ValidatorInterface $validator;
    private BulkSaverInterface $bulkSaver;
    private StepExecution $stepExecution;

    public function __construct(
        IdentifiableObjectRepositoryInterface $entityRepository,
        AttributeRepositoryInterface $attributeRepository,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        TableNormalizer $tableNormalizer,
        TableConfigurationRepository $tableConfigurationRepository,
        ValidatorInterface $validator,
        BulkSaverInterface $bulkSaver
    ) {
        $this->entityRepository = $entityRepository;
        $this->entityWithValuesBuilder = $entityWithValuesBuilder;
        $this->attributeRepository = $attributeRepository;
        $this->tableNormalizer = $tableNormalizer;
        $this->tableConfigurationRepository = $tableConfigurationRepository;
        $this->validator = $validator;
        $this->bulkSaver = $bulkSaver;
    }

    /**
     * {@inheritDoc}
     */
    public function write(array $items): void
    {
        Assert::allIsInstanceOf($items, TableRow::class);

        $productSortedTableRows = [];

        /** @var TableRow $tableRow */
        foreach ($items as $tableRow) {
            $productSortedTableRows[$tableRow->entityId][] = $tableRow;
        }

        $productsToSave = [];
        foreach ($productSortedTableRows as $entityId => $tableRows) {
            /** @var EntityWithValuesInterface $entity */
            $entity = $this->entityRepository->findOneByIdentifier($entityId);
            Assert::notNull($entity);

            $updatedRowCount = $this->updateEntityAndReturnUpdatedRowCount($entity, $tableRows);
            if (0 < $updatedRowCount) {
                $productsToSave[] = $entity;
                $this->stepExecution->incrementSummaryInfo('update', $updatedRowCount);
            }
        }

        $this->bulkSaver->saveAll($productsToSave);
    }

    private function updateEntityAndReturnUpdatedRowCount(EntityWithValuesInterface $entity, array $tableRows): int
    {
        $updatedRowCount = 0;
        foreach ($tableRows as $tableRow) {
            $attributeCode = $tableRow->attributeCode;
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
            Assert::notNull($attribute);

            $formerValue = $entity->getValue($attributeCode, $tableRow->localeCode, $tableRow->scopeCode);
            $newTable = $this->computeNewTable($formerValue, $tableRow, $attributeCode);
            $newValue = $this->entityWithValuesBuilder->addOrReplaceValue(
                $entity,
                $attribute,
                $tableRow->localeCode,
                $tableRow->scopeCode,
                $newTable
            );

            $violations = $this->validator->validate($entity);
            if (\count($violations) > 0) {
                if (null !== $newValue) {
                    $entity->removeValue($newValue);
                }
                if (null !== $formerValue) {
                    $entity->addValue($formerValue);
                }
                $this->stepExecution->addWarning(
                    \implode(', ', \array_map(
                        fn (ConstraintViolationInterface $constraintViolation): string => $constraintViolation->getMessage(),
                        \iterator_to_array($violations)
                    )),
                    [],
                    new DataInvalidItem($this->normalizeTableRow($tableRow))
                );
                $this->stepExecution->incrementSummaryInfo('skip');
            } else {
                $updatedRowCount++;
            }
        }

        return $updatedRowCount;
    }

    private function computeNewTable(?ValueInterface $formerValue, TableRow $tableRow, string $attributeCode): array
    {
        if (null === $formerValue) {
            return [$tableRow->row->normalize()];
        }

        $normalizedRow = $tableRow->row->normalize();

        $newTable = $this->tableNormalizer->normalize($formerValue->getData());
        $tableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($attributeCode);
        $firstColumnCode = $tableConfiguration->getFirstColumnCode()->asString();

        $found = false;
        foreach ($newTable as $index => $row) {
            if ($row[$firstColumnCode] === ($normalizedRow[$firstColumnCode] ?? null)) {
                $newTable[$index] = $normalizedRow;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $newTable[] = $normalizedRow;
        }

        return $newTable;
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    private function normalizeTableRow(TableRow $tableRow): array
    {
        $attribute = \implode('-', \array_filter([
            $tableRow->attributeCode,
            $tableRow->localeCode,
            $tableRow->scopeCode,
        ]));

        $data = [
            'entity' => $tableRow->entityId,
            'attribute' => $attribute,
        ];
        foreach ($tableRow->row as $columnCode => $cell) {
            $data[$columnCode] = $cell->normalize();
        }

        return $data;
    }
}
