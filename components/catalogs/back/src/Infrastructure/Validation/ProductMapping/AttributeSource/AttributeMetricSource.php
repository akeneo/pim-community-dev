<?php

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSource;

use Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSourceContainsValidLocale;
use Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSourceContainsValidMetricUnit;
use Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSourceContainsValidScope;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class AttributeMetricSource extends Compound
{
    /**
     * @param array<array-key, mixed> $options
     *
     * @return array<array-key, Constraint>
     */
    protected function getConstraints(array $options): array
    {
        return [
            new Assert\Sequentially([
                new Assert\Collection([
                    'fields' => [
                        'source' => [
                            new Assert\Type('string'),
                            new Assert\NotBlank(),
                        ],
                        'scope' => [
                            new Assert\Type('string'),
                            new Assert\NotBlank(allowNull: true),
                        ],
                        'locale' => [
                            new Assert\Type('string'),
                            new Assert\NotBlank(allowNull: true),
                        ],
                        'default' => [
                            new Assert\Optional([
                                new Assert\Type('numeric'),
                            ]),
                        ],
                        'parameters' => [
                            new Assert\Collection([
                                'fields' => [
                                    'unit' => [
                                        new Assert\Type('string'),
                                        new Assert\NotBlank(message: 'akeneo_catalogs.validation.product_mapping.source.measurement.unit.not_empty'),
                                    ],
                                ],
                                'allowMissingFields' => false,
                                'allowExtraFields' => false,
                            ]),
                        ],
                    ],
                    'allowMissingFields' => false,
                    'allowExtraFields' => false,
                ]),
                new AttributeSourceContainsValidScope(),
                new AttributeSourceContainsValidLocale(),
                new AttributeSourceContainsValidMetricUnit(),
            ]),
        ];
    }
}
