<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
#[\Attribute]
class Criteria extends Compound
{
    /**
     * @param array<array-key, mixed> $options
     *
     * @return array<Constraint>
     */
    protected function getConstraints(array $options): array
    {
        return [
            new Assert\Type('array'),
            new Assert\All(
                new Assert\Collection([
                    'fields' => [
                        'field' => new Assert\Required([
                            new Assert\Type('string'),
                            new Assert\NotBlank(),
                        ]),
                        'operator' => new Assert\Required([
                            new Assert\Type('string'),
                            new Assert\NotBlank(),
                        ]),
                        'value' => new Assert\Optional(),
                    ],
                    'allowMissingFields' => false,
                    'allowExtraFields' => false,
                ])
            ),
        ];
    }
}
