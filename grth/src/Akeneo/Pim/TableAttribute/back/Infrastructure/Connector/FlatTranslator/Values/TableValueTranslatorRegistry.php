<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\Values;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Webmozart\Assert\Assert;

class TableValueTranslatorRegistry
{
    private TableConfigurationRepository $tableConfigurationRepository;
    /** @var iterable<TableValueTranslator> */
    private iterable $tableValueTranslators;

    /**
     * @param iterable<TableValueTranslator> $tableValueTranslators
     */
    public function __construct(
        TableConfigurationRepository $tableConfigurationRepository,
        iterable $tableValueTranslators
    ) {
        $this->tableConfigurationRepository = $tableConfigurationRepository;
        foreach ($tableValueTranslators as $tableValueTranslator) {
            Assert::isInstanceOf($tableValueTranslator, TableValueTranslator::class);
            $this->tableValueTranslators[$tableValueTranslator->getSupportedColumnDataType()] = $tableValueTranslator;
        }
    }

    public function translate(
        string $attributeCode,
        string $columnCode,
        string $localeCode,
        mixed $value
    ): mixed {
        $tableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($attributeCode);
        $column = $tableConfiguration->getColumnByCode(ColumnCode::fromString($columnCode));
        if (null === $column) {
            return $value;
        }

        $valueTranslator = $this->tableValueTranslators[$column->dataType()->asString()] ?? null;

        return null !== $valueTranslator
            ? $valueTranslator->translate($attributeCode, $column, $localeCode, $value) ?? $value
            : $value
            ;
    }
}
