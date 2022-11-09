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

namespace Akeneo\Platform\Syndication\Infrastructure\Validation\Source\SimpleSelect;

use Akeneo\Platform\Syndication\Infrastructure\Validation\Operation\DefaultValueOperationConstraint;
use Akeneo\Platform\Syndication\Infrastructure\Validation\Operation\ReplacementOperationConstraint;
use Akeneo\Platform\Syndication\Infrastructure\Validation\Operation\String\ExtractOperationConstraint;
use Akeneo\Platform\Syndication\Infrastructure\Validation\Selection\CodeLabelSelectionConstraint;
use Akeneo\Platform\Syndication\Infrastructure\Validation\Source\SourceConstraintProvider;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\ConstraintValidator;

class SimpleSelectSourceValidator extends ConstraintValidator
{
    public function validate($source, Constraint $constraint): void
    {
        $validator = $this->context->getValidator();
        $sourceConstraintFields = SourceConstraintProvider::getConstraintCollection()->fields;
        $sourceConstraintFields['selection'] = new CodeLabelSelectionConstraint();
        $sourceConstraintFields['operations'] = new Collection(['fields' => [
            'replacement' => new Optional(new ReplacementOperationConstraint()),
            'extract' => new Optional(new ExtractOperationConstraint()),
            'default_value' => new Optional(new DefaultValueOperationConstraint()),
        ]]);

        $violations = $validator->validate($source, new Collection(['fields' => $sourceConstraintFields]));

        foreach ($violations as $violation) {
            $this->context->buildViolation(
                $violation->getMessage(),
                $violation->getParameters()
            )
                ->atPath($violation->getPropertyPath())
                ->addViolation();
        }
    }
}
