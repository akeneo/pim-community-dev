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
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\BooleanColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ColumnDefinition;
use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;

final class TableBooleanTranslator implements TableValueTranslator
{
    private LabelTranslatorInterface $labelTranslator;
    private array $trueLocalized = [];
    private array $falseLocalized = [];

    public function __construct(LabelTranslatorInterface $labelTranslator)
    {
        $this->labelTranslator = $labelTranslator;
    }

    public function getSupportedColumnDataType(): string
    {
        return BooleanColumn::DATATYPE;
    }

    public function translate(string $attributeCode, ColumnDefinition $column, string $localeCode, mixed $value): string
    {
        if (!\array_key_exists($localeCode, $this->trueLocalized)) {
            $this->trueLocalized[$localeCode] = $this->labelTranslator->translate(
                'pim_common.yes',
                $localeCode,
                \sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, 'yes')
            );
        }

        if (!\array_key_exists($localeCode, $this->falseLocalized)) {
            $this->falseLocalized[$localeCode] = $this->labelTranslator->translate(
                'pim_common.no',
                $localeCode,
                \sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, 'no')
            );
        }

        return match ($value) {
            true => $this->trueLocalized[$localeCode],
            false => $this->falseLocalized[$localeCode],
            default => \sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, (string) $value),
        };
    }
}
