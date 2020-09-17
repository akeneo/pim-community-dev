<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\ProductSource;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\ProductSourceKeys;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class ProductSourceKeysValidator extends ConstraintValidator
{
    public function validate($productSource, Constraint $constraint)
    {
        Assert::isInstanceOf($productSource, ProductSource::class);
        Assert::isInstanceOf($constraint, ProductSourceKeys::class);

        $numberOfRequiredKeys = 0;
        if (null !== $productSource->field && '' !== trim($productSource->field)) {
            $numberOfRequiredKeys++;
        }

        if (null !== $productSource->text) {
            $numberOfRequiredKeys++;
        }

        if (null !== $productSource->newLine) {
            $numberOfRequiredKeys++;
        }

        if (0 === $numberOfRequiredKeys) {
            $this->context->buildViolation($constraint->missingSourceKeyMessage)->addViolation();
        } elseif (1 < $numberOfRequiredKeys) {
            $this->context->buildViolation($constraint->onlyOneSourceKeyExpectedMessage)->addViolation();
        }
    }
}
