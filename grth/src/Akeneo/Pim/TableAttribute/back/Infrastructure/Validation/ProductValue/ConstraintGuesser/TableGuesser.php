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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue\CellDataTypesShouldMatch;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue\FirstColumnShouldBeFilled;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue\MeasurementUnitsShouldExist;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue\RecordsShouldExist;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue\SelectOptionsShouldExist;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue\TableColumnsShouldExist;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue\TableShouldNotHaveTooManyRows;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue\TableValidationsShouldMatch;

final class TableGuesser implements ConstraintGuesserInterface
{
    public function supportAttribute(AttributeInterface $attribute): bool
    {
        return AttributeTypes::TABLE === $attribute->getType();
    }

    public function guessConstraints(AttributeInterface $attribute): array
    {
        return [
            new CellDataTypesShouldMatch(),
            new FirstColumnShouldBeFilled(),
            new TableColumnsShouldExist(),
            new SelectOptionsShouldExist(),
            new TableValidationsShouldMatch(),
            new TableShouldNotHaveTooManyRows(),
            new RecordsShouldExist(),
            new MeasurementUnitsShouldExist(),
        ];
    }
}
