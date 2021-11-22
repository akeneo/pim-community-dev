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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValueCollection;

use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class ProductShouldNotHaveTooManyCellsValidator extends ConstraintValidator
{
    public function validate($writeValueCollection, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, ProductShouldNotHaveTooManyCells::class);
        if (!$writeValueCollection instanceof WriteValueCollection) {
            return;
        }

        $cellsCount = 0;
        foreach ($writeValueCollection as $value) {
            if (!$value instanceof TableValue) {
                continue;
            }

            $cellsCount += $value->getData()->cellsCount();
        }

        if ($cellsCount > ProductShouldNotHaveTooManyCells::LIMIT_CELLS_PER_PRODUCT) {
            $this->context->buildViolation(
                $constraint->message,
                [
                    '{{ count }}' => $cellsCount,
                    '{{ limit }}' => ProductShouldNotHaveTooManyCells::LIMIT_CELLS_PER_PRODUCT,
                ]
            )->addViolation();
        }
    }
}
