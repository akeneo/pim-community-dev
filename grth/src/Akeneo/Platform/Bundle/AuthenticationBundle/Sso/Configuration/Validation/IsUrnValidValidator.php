<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\Validation;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\EntityId;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates a URN
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
final class IsUrnValidValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($uri, Constraint $constraint): void
    {
        if (!$constraint instanceof IsUrnValid) {
            throw new \InvalidArgumentException(
                sprintf('This validator expects a "%s" constraint', IsUrnValid::class)
            );
        }

        if (!preg_match(EntityId::URN_PATTERN, $uri)) {
            $this->context
                ->buildViolation($constraint->invalidUrn)
                ->addViolation();

            return;
        }
    }
}
