<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Operation;

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CleanHTMLOperation;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Operation\CleanHTMLOperation as CleanHTMLOperationConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class CleanHTMLOperationValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof CleanHTMLOperationConstraint) {
            throw new UnexpectedTypeException($constraint, CleanHTMLOperationConstraint::class);
        }

        $this->context->getValidator()
            ->inContext($this->context)
            ->validate($value, new Collection([
                'fields' => [
                    'uuid' => [new Uuid(), new NotBlank()],
                    'modes' => new Choice(
                        choices: [
                            CleanHTMLOperation::MODE_REMOVE_HTML_TAGS,
                            CleanHTMLOperation::MODE_DECODE_HTML_CHARACTERS,
                        ],
                        multiple: true,
                    ),
                    'type' => new EqualTo(['value' => CleanHTMLOperation::TYPE]),
                ],
            ]));
    }
}
