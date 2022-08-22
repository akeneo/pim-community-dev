<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\CatalogPayload;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class FamilyFieldIsValid extends Compound
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
                        new Assert\IdenticalTo('family'),
                    ],
                    'operator' => [
                        new Assert\Type('string'),
                        new Assert\Choice(['EMPTY', 'NOT EMPTY', 'IN', 'NOT IN']),
                    ],
                    'value' => [
                        new Assert\Type('array'),
                        new Assert\All(new Assert\Type('string')),
                    ],
                ],
                'allowMissingFields' => false,
                'allowExtraFields' => false,
            ]),
        ];
    }
}
