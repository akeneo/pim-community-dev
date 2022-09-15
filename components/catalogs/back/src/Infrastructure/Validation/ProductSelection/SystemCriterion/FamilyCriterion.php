<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\SystemCriterion;

use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\CriterionOperatorsRequireEmptyValue;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\FamilyCriterionContainsValidFamilies;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class FamilyCriterion extends Compound
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
                            new Assert\IdenticalTo('family'),
                        ],
                        'operator' => [
                            new Assert\Choice([
                                Operator::IS_EMPTY,
                                Operator::IS_NOT_EMPTY,
                                Operator::IN_LIST,
                                Operator::NOT_IN_LIST,
                            ]),
                        ],
                        'value' => [
                            new Assert\Type('array'),
                            new Assert\All(new Assert\Type('string')),
                        ],
                    ],
                    'allowMissingFields' => false,
                    'allowExtraFields' => false,
                ]),
                new CriterionOperatorsRequireEmptyValue([
                    Operator::IS_EMPTY,
                    Operator::IS_NOT_EMPTY,
                ]),
                new FamilyCriterionContainsValidFamilies(),
            ]),
        ];
    }
}
