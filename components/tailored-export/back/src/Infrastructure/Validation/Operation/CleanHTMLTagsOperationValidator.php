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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation\Operation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;

class CleanHTMLTagsOperationValidator extends ConstraintValidator
{
    public function validate($operation, Constraint $constraint): void
    {
        $validator = $this->context->getValidator();

        $violations = $validator->validate($operation, new Collection([
            'fields' => [
                'type' => new EqualTo(['value' => 'clean_html_tags']),
                'value' => new Type('bool'),
            ],
        ]));

        foreach ($violations as $violation) {
            $this->context->buildViolation(
                $violation->getMessage(),
                $violation->getParameters(),
            )
                ->atPath($violation->getPropertyPath())
                ->addViolation();
        }
    }
}
