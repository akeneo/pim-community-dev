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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation\Source\Groups;

use Akeneo\Platform\TailoredExport\Infrastructure\Validation\Selection\CodeLabelCollectionSelectionConstraint;
use Akeneo\Platform\TailoredExport\Infrastructure\Validation\Source\SourceConstraintProvider;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;

class GroupsSourceValidator extends ConstraintValidator
{
    public function validate($source, Constraint $constraint)
    {
        $validator = $this->context->getValidator();
        $sourceConstraintFields = SourceConstraintProvider::getConstraintCollection()->fields;
        $sourceConstraintFields['selection'] = [
            new NotBlank(),
            new CodeLabelCollectionSelectionConstraint()
        ];

        $sourceConstraintFields['operations'] = new Type([
            'type' => 'array',
        ]);

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
