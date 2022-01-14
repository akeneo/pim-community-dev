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

namespace Akeneo\Platform\Syndication\Infrastructure\Validation\Format;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\ConstraintValidator;

class FormatValidator extends ConstraintValidator
{
    private const MAX_TEXT_COUNT = 10;
    private const TEXT_MAX_LENGTH = 255;

    public function validate($format, Constraint $constraint): void
    {
        $this->context->getValidator()
            ->inContext($this->context)
            ->validate($format, new Collection([
                'type' => new Choice([
                    'choices' => ['concat', 'none'],
                ]),
                'space_between' => new Optional(new Type('bool')),
                'elements' => new Optional(new Type('array')),
            ]));

        if (0 < $this->context->getViolations()->count() || 'none' === $format['type']) {
            return;
        }

        foreach ($format['elements'] as $element) {
            $this->context->getValidator()
                ->inContext($this->context)
                ->atPath(sprintf('[elements][%s]', $element['uuid']))
                ->validate($element, new Collection([
                    'uuid' => new Uuid(),
                    'type' => new Choice([
                        'choices' => ['text', 'source'],
                    ]),
                    'value' => [
                        new Type('string'),
                        new NotBlank(['message' => 'akeneo.syndication.validation.required']),
                        new Length([
                            'max' => self::TEXT_MAX_LENGTH,
                        ])
                    ],
                ]));
        }

        $textElements = array_filter($format['elements'], static fn (array $element) => 'text' === $element['type']);

        if (self::MAX_TEXT_COUNT < count($textElements)) {
            $this->context->buildViolation(Format::MAX_TEXT_COUNT_REACHED)
                ->atPath('[elements]')
                ->setParameter('{{ limit }}', (string) self::MAX_TEXT_COUNT)
                ->addViolation();
        }
    }
}
