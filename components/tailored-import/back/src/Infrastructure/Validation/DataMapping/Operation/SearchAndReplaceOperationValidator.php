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

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\SearchAndReplaceOperation;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Operation\SearchAndReplaceOperation as SearchAndReplaceOperationConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class SearchAndReplaceOperationValidator extends ConstraintValidator
{
    private const MAX_REPLACEMENTS = 10;

    public function validate($operation, Constraint $constraint): void
    {
        if (!$constraint instanceof SearchAndReplaceOperationConstraint) {
            throw new UnexpectedTypeException($constraint, SearchAndReplaceOperationConstraint::class);
        }

        $this->context->getValidator()->inContext($this->context)->validate($operation, new Collection([
            'fields' => [
                'uuid' => [new Uuid(), new NotBlank()],
                'type' => new EqualTo(SearchAndReplaceOperation::TYPE),
                'replacements' => [
                    new Type('array'),
                    new Count([
                        'max' => self::MAX_REPLACEMENTS,
                        'maxMessage' => SearchAndReplaceOperationConstraint::MAX_REPLACEMENTS_REACHED,
                    ]),
                ],
            ],
        ]));

        if (0 < $this->context->getViolations()->count()) {
            return;
        }

        foreach ($operation['replacements'] as $replacement) {
            $this->context->getValidator()->inContext($this->context)
                ->atPath(sprintf('[replacements][%s]', $replacement['uuid']))
                ->validate(
                    $replacement,
                    new Collection([
                        'fields' => [
                            'uuid' => [new Uuid(), new NotBlank()],
                            'what' => [
                                new Type('string'),
                                new NotBlank(),
                                new Length([
                                    'max' => 255,
                                    'maxMessage' => OperationConstraint::MAX_LENGTH_REACHED,
                                ]),
                            ],
                            'with' => [
                                new Type('string'),
                                new Length([
                                    'max' => 255,
                                    'maxMessage' => OperationConstraint::MAX_LENGTH_REACHED,
                                ]),
                            ],
                            'case_sensitive' => new Type('bool'),
                        ],
                    ]),
                );
        }
    }
}
