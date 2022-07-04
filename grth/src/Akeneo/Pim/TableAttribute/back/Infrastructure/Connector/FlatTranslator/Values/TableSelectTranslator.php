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

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ColumnDefinition;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;

final class TableSelectTranslator implements TableValueTranslator
{
    public function __construct(private SelectOptionCollectionRepository $selectOptionCollectionRepository)
    {
    }

    public function getSupportedColumnDataType(): string
    {
        return SelectColumn::DATATYPE;
    }

    public function translate(string $attributeCode, ColumnDefinition $column, string $localeCode, string $value): string
    {
        $selectOptionCollection = $this->selectOptionCollectionRepository->getByColumn($attributeCode, $column->code());

        $selectOption = $selectOptionCollection->getByCode($value);
        if (null === $selectOption) {
            return \sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $value);
        }

        return $selectOption->labels()->getLabel($localeCode)
            ?? \sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $value);
    }
}
