<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\SystemCriterion;

use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\CriterionContainsScopeAndLocale;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class CompletenessCriterion extends Compound
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
                            new Assert\IdenticalTo('completeness'),
                        ],
                        'operator' => [
                            new Assert\Choice([
                                Operator::EQUALS,
                                Operator::NOT_EQUAL,
                                Operator::LOWER_THAN,
                                Operator::GREATER_THAN,
                            ]),
                        ],
                        'value' => [
                            new Assert\Type('int'),
                            new Assert\Range([
                                'min' => 0,
                                'max' => 100,
                                'notInRangeMessage' => 'akeneo_catalogs.validation.product_selection.criteria.completeness.value',
                            ]),
                        ],
                        'scope' => [
                            new Assert\Type('string'),
                            new Assert\NotBlank(),
                        ],
                        'locale' => [
                            new Assert\Type('string'),
                            new Assert\NotBlank(),
                        ],
                    ],
                    'allowMissingFields' => false,
                    'allowExtraFields' => false,
                ]),
                new CriterionContainsScopeAndLocale(),
            ]),
        ];
    }
}
