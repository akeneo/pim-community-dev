<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductSelection;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class EnabledCriterion extends Compound
{
    /**
     * @param array<array-key, mixed> $options
     *
     * @return array<array-key, Constraint>
     */
    protected function getConstraints(array $options = []): array
    {
        return [
            new Assert\Collection([
                'fields' => [
                    'field' => [
                        new Assert\IdenticalTo('enabled'),
                    ],
                    'operator' => [
                        new Assert\Type('string'),
                        new Assert\Choice(['=', '!=']),
                    ],
                    'value' => [
                        new Assert\Type('boolean'),
                    ],
                ],
                'allowMissingFields' => false,
                'allowExtraFields' => false,
            ]),
        ];
    }
}
