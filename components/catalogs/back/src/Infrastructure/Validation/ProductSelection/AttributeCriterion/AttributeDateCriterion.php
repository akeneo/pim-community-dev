<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\AttributeCriterion;

use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\AttributeCriterionContainsValidLocale;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\AttributeCriterionContainsValidScope;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\CriterionOperatorsRequireEmptyValue;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\CriterionOperatorsRequireValueConstraints;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class AttributeDateCriterion extends Compound
{
    /**
     * @param array<array-key, mixed> $options
     *
     * @return array<array-key, Constraint>
     */
    protected function getConstraints(array $options = []): array
    {
        return [
            new Assert\Sequentially([
                new Assert\Collection([
                    'fields' => [
                        'field' => [
                            new Assert\Type('string'),
                            new Assert\NotBlank(),
                        ],
                        'operator' => [
                            new Assert\Choice([
                                Operator::EQUALS,
                                Operator::NOT_EQUAL,
                                Operator::LOWER_THAN,
                                Operator::GREATER_THAN,
                                Operator::BETWEEN,
                                Operator::NOT_BETWEEN,
                                Operator::IS_EMPTY,
                                Operator::IS_NOT_EMPTY,
                            ]),
                        ],
                        'value' => [],
                        'scope' => [
                            new Assert\Type('string'),
                        ],
                        'locale' => [
                            new Assert\Type('string'),
                        ],
                    ],
                    'allowMissingFields' => false,
                    'allowExtraFields' => false,
                ]),
                new CriterionOperatorsRequireValueConstraints([
                    'operators' => [
                        Operator::EQUALS,
                        Operator::NOT_EQUAL,
                        Operator::LOWER_THAN,
                        Operator::GREATER_THAN,
                    ],
                    'constraints' => [
                        new Assert\Type('string'),
                        new Assert\Date(),
                    ],
                ]),
                new CriterionOperatorsRequireValueConstraints([
                    'operators' => [
                        Operator::BETWEEN,
                        Operator::NOT_BETWEEN,
                    ],
                    'constraints' => [
                        new Assert\Type('array'),
                        new Assert\Count(
                            exactly: 2,
                            exactMessage: 'akeneo_catalogs.validation.product_selection.criteria.date.between_is_empty',
                        ),
                        new Assert\All([
                            new Assert\Type('string'),
                            new Assert\Date(),
                        ]),
                    ],
                ]),
                new CriterionOperatorsRequireEmptyValue([
                    Operator::IS_EMPTY,
                    Operator::IS_NOT_EMPTY,
                ]),
                new AttributeCriterionContainsValidScope(),
                new AttributeCriterionContainsValidLocale(),
            ]),
        ];
    }
}
