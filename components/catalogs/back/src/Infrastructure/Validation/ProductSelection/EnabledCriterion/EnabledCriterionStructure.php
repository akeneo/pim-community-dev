<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\EnabledCriterion;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class EnabledCriterionStructure extends Compound
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
