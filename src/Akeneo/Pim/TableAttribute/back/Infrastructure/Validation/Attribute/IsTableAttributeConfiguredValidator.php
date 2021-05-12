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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @author Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 */
final class IsTableAttributeConfiguredValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        Assert::implementsInterface($value, AttributeInterface::class);
        Assert::isInstanceOf($constraint, IsTableAttributeConfigured::class);
        if (AttributeTypes::TABLE !== $value->getType()) {
            return;
        }

        if (null === $value->getRawTableConfiguration()) {
            $this->context->buildViolation('TODO error message', [])->atPath('table_configuration')->addViolation();
        }
    }
}
