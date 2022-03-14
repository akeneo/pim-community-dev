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

namespace Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Saver;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ColumnDefinition;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Event\TableConfigurationHasBeenUpdated;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Factory\TableConfigurationFactory;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationNotFoundException;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfigurationUpdater;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\WriteSelectOptionCollection;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Webmozart\Assert\Assert;

class TableConfigurationSaver implements SaverInterface
{
    private TableConfigurationRepository $tableConfigurationRepository;
    private SelectOptionCollectionRepository $optionCollectionRepository;
    private TableConfigurationFactory $tableConfigurationFactory;
    private TableConfigurationUpdater $tableConfigurationUpdater;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        TableConfigurationRepository $tableConfigurationRepository,
        SelectOptionCollectionRepository $optionCollectionRepository,
        TableConfigurationFactory $tableConfigurationFactory,
        TableConfigurationUpdater $tableConfigurationUpdater,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->tableConfigurationRepository = $tableConfigurationRepository;
        $this->optionCollectionRepository = $optionCollectionRepository;
        $this->tableConfigurationFactory = $tableConfigurationFactory;
        $this->tableConfigurationUpdater = $tableConfigurationUpdater;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function save($attribute, array $options = []): void
    {
        /** @var AttributeInterface $attribute */
        Assert::isInstanceOf($attribute, AttributeInterface::class);
        if (AttributeTypes::TABLE !== $attribute->getType()) {
            return;
        }
        Assert::isArray($attribute->getRawTableConfiguration());
        Assert::allIsArray($attribute->getRawTableConfiguration());

        $tableConfiguration = $this->createOrUpdateTableConfiguration($attribute);
        $this->tableConfigurationRepository->save($attribute->getCode(), $tableConfiguration);

        foreach ($attribute->getRawTableConfiguration() as $rawColumnDefinition) {
            if ($rawColumnDefinition['data_type'] === SelectColumn::DATATYPE) {
                if (
                    !array_key_exists('options', $rawColumnDefinition) ||
                    null === $rawColumnDefinition['options']
                ) {
                    continue;
                }

                Assert::isArray($rawColumnDefinition['options'] ?? []);
                $selectOptionCollection = $this->optionCollectionRepository->getByColumn(
                    $attribute->getCode(),
                    ColumnCode::fromString($rawColumnDefinition['code'])
                );
                $writeSelectOptionCollection = WriteSelectOptionCollection::fromReadSelectOptionCollection(
                    $selectOptionCollection
                );
                $columnCode = ColumnCode::fromString($rawColumnDefinition['code']);
                $writeSelectOptionCollection->update($attribute->getCode(), $columnCode, $rawColumnDefinition['options'] ?? []);
                $this->optionCollectionRepository->save(
                    $attribute->getCode(),
                    $columnCode,
                    $writeSelectOptionCollection
                );
                foreach ($writeSelectOptionCollection->releaseEvents() as $event) {
                    $this->eventDispatcher->dispatch($event);
                }
            }
        }
    }

    /**
     * @param array<string, mixed> $normalized
     */
    private function createColumnDefinitionFromNormalized(array $normalized): ColumnDefinition
    {
        Assert::string($normalized['data_type']);

        $class = $this->columnDefinitionMapping[$normalized['data_type']] ?? null;
        if (null === $class) {
            throw new \InvalidArgumentException(sprintf('The "%s" type is unknown', $normalized['data_type']));
        }

        return $class::fromNormalized($normalized);
    }

    private function hasAttributeColumnsCompletenessBeenUpdated(AttributeInterface $newAttribute, TableConfiguration $formerTableConfiguration): bool
    {
        $newTableConfiguration = $newAttribute->getRawTableConfiguration();
        $newlyRequired = [];
        // ['code-text', 'code2-number']
        foreach ($newTableConfiguration as $rawColumnDefinition) {
            if (
                isset($rawColumnDefinition['is_required_for_completeness'])
                && isset($rawColumnDefinition['code'])
                && isset($rawColumnDefinition['data_type'])
                && $rawColumnDefinition['is_required_for_completeness']
            ) {
                $newlyRequired[] = \implode('-', [$rawColumnDefinition['code'], $rawColumnDefinition['data_type']]);
            }
        }
        \sort($newlyRequired);

        $formerRequiredColumns = $formerTableConfiguration->requiredColumns();
        $formerlyRequired = \array_map(
            fn(ColumnDefinition $column): string =>\implode('-', [$column->code()->asString(), $column->dataType()->asString()]),
            $formerRequiredColumns
        );
        \sort($formerlyRequired);

        return \json_encode($newlyRequired) !== \json_encode($formerlyRequired);
    }

    private function createOrUpdateTableConfiguration(AttributeInterface $attribute): TableConfiguration
    {
        try {
            $tableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($attribute->getCode());

            $hasBeenUpdated = $this->hasAttributeColumnsCompletenessBeenUpdated($attribute, $tableConfiguration);
            if ($hasBeenUpdated) {
                $this->eventDispatcher->dispatch(new TableConfigurationHasBeenUpdated($attribute->getCode()));
            }

            return $this->tableConfigurationUpdater->update(
                $tableConfiguration,
                $attribute->getRawTableConfiguration()
            );
        } catch (TableConfigurationNotFoundException $e) {
            return $this->tableConfigurationFactory->createFromNormalized(
                array_map(
                    fn (array $rawColumnDefinition): array => array_merge(
                        $rawColumnDefinition,
                        [
                            'id' => $this->tableConfigurationRepository->getNextIdentifier(
                                ColumnCode::fromString($rawColumnDefinition['code'])
                            )->asString(),
                        ]
                    ),
                    $attribute->getRawTableConfiguration()
                )
            );
        }
    }
}
