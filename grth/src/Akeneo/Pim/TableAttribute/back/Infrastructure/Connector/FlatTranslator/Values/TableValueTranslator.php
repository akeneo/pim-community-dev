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

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ColumnDefinition;

interface TableValueTranslator
{
    public function getSupportedColumnDataType(): string;

    public function translate(
        string $attributeCode,
        ColumnDefinition $column,
        string $localeCode,
        string $value
    ): string;
}
