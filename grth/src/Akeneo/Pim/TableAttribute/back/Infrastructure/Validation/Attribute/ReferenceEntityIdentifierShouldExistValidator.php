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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\ReferenceEntityExists;
use Akeneo\Pim\TableAttribute\Infrastructure\AntiCorruptionLayer\Feature;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class ReferenceEntityIdentifierShouldExistValidator extends ConstraintValidator
{
    public function __construct(
        private ReferenceEntityExists $referenceEntityExists,
        private Feature $feature
    ) {
    }

    public function validate($value, Constraint $constraint)
    {
        Assert::isInstanceOf($constraint, ReferenceEntityIdentifierShouldExist::class);
        if (!is_string($value) || !$this->feature->isEnabled(Feature::REFERENCE_ENTITY)) {
            return;
        }

        if (!$this->referenceEntityExists->forIdentifier($value)) {
            $this->context
                ->buildViolation($constraint->message, ['{{ reference_entity_identifier }}' => $value])
                ->addViolation();
        }
    }
}
