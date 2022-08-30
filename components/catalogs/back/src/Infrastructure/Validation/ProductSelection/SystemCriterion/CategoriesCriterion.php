<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\SystemCriterion;

use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\CategoriesCriterionContainsValidCategories;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\CriterionOperatorsRequireEmptyValue;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class CategoriesCriterion extends Compound
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
                            new Assert\IdenticalTo('categories'),
                        ],
                        'operator' => [
                            new Assert\Choice([
                                Operator::IN_LIST,
                                Operator::NOT_IN_LIST,
                                Operator::IN_CHILDREN_LIST,
                                Operator::NOT_IN_CHILDREN_LIST,
                                Operator::UNCLASSIFIED,
                                Operator::IN_LIST_OR_UNCLASSIFIED,
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
                new CriterionOperatorsRequireEmptyValue([Operator::UNCLASSIFIED]),
                new CategoriesCriterionContainsValidCategories(),
            ]),
        ];
    }
}
