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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SourcesValidator extends ConstraintValidator
{
    private const MAX_SOURCE_COUNT = 4;

    public function validate($sources, Constraint $constraint)
    {
        if (empty($sources)) {
            return;
        }

        $validator = Validation::createValidator();

        $violations = $validator->validate($sources, [
            new Type(['type' => 'array']),
            new Count([
                'max' => self::MAX_SOURCE_COUNT,
                'maxMessage' => 'akeneo.tailored_export.validation.sources.max_source_count_reached'
            ])
        ]);

        if (0 < $violations->count()) {
            foreach ($violations as $violation) {
                $this->context->buildViolation(
                    $violation->getMessage(),
                    $violation->getParameters()
                )
                    ->addViolation();
            }

            return;
        }

        foreach ($sources as $source) {
            $this->validateColumn($validator, $source);
        }
    }

    private function validateColumn(ValidatorInterface $validator, $source): void
    {
        $violations = $validator->validate(
            $source,
            new Collection([
                'fields' => [
                    'uuid' => [
                        new NotBlank(),
                        new Uuid()
                    ],
                    'code' => [
                        new Type([
                            'type' => 'string',
                        ]),
                        new NotBlank(),
                    ],
                    'channel' => [
                        new Type([
                            'type' => 'string',
                        ]),
                    ],
                    'locale' => [
                        new Type([
                            'type' => 'string',
                        ]),
                    ],
                    'operations' => [
                        new Type([
                            'type' => 'array',
                        ]),
                    ],
                    'type' => [
                        new Choice(
                            [
                                'strict' => true,
                                'choices' => ['property', 'attribute'],
                            ]
                        )
                    ],
                    'selection' => new Selection(),
                ],
            ]),
        );

        if (0 < $violations->count()) {
            foreach ($violations as $violation) {
                $this->context->buildViolation(
                    $violation->getMessage(),
                    $violation->getParameters()
                )
                    ->atPath(sprintf('[%s]%s', $source['uuid'], $violation->getPropertyPath()))
                    ->addViolation();
            }
        }
    }
}
