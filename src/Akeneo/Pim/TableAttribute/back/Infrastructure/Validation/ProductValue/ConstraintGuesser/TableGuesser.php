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
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue\TableColumnsShouldExist;

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
            new TableColumnsShouldExist(),
        ];
    }
}
