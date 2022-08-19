<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\CatalogPayload;

use Akeneo\Catalogs\Application\Persistence\GetCategoriesByCodeQueryInterface;
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
class CategoriesFieldIsValid extends Compound
{
    public function __construct(private GetCategoriesByCodeQueryInterface $getCategoriesByCodeQuery)
    {
        parent::__construct();
    }

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
                            new Assert\Type('string'),
                            new Assert\Choice(['IN', 'NOT IN', 'IN CHILDREN', 'NOT IN CHILDREN', 'UNCLASSIFIED', 'IN OR UNCLASSIFIED']),
                        ],
                        'value' => [
                            new Assert\Type('array'),
                            new Assert\All(new Assert\Type('string')),
                        ],
                    ],
                    'allowMissingFields' => false,
                    'allowExtraFields' => false,
                ]),
                new Assert\Callback(function (array $criterion, ExecutionContextInterface $context): void {
                    /** @var array<array-key, string> $categoryCodes */
                    $categoryCodes = $criterion['value'];

                    $existingCategories = $this->getCategoriesByCodeQuery->execute($categoryCodes, 'en_US');
                    $existingCategoryCodes = \array_column($existingCategories, 'code');

                    $nonExistingCategoryCodes = \array_diff($categoryCodes, $existingCategoryCodes);

                    if ($nonExistingCategoryCodes !== []) {
                        $context->buildViolation('akeneo_catalogs.validation.product_selection.criteria.category.value')
                            ->atPath('[value]')
                            ->addViolation();
                    }
                }),
            ]),
        ];
    }
}
