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

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\AllIfIterable;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class AllIfIterableValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        Assert::isInstanceOf($constraint, AllIfIterable::class);

        if (!\is_array($value) && !$value instanceof \Traversable) {
            return;
        }

        $context = $this->context;
        $context
            ->getValidator()
            ->inContext($context)
            ->validate(
                $value,
                new All($constraint->constraints),
                $context->getGroup()
            );
    }
}
