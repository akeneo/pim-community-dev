<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductSelection;

use Akeneo\Catalogs\Application\Persistence\Category\GetCategoriesByCodeQueryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @psalm-suppress PropertyNotSetInConstructor
 */
class CategoriesCriterionContainsValidCategoriesValidator extends ConstraintValidator
{
    public function __construct(private GetCategoriesByCodeQueryInterface $getCategoriesByCodeQuery)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof CategoriesCriterionContainsValidCategories) {
            throw new UnexpectedTypeException($constraint, CategoriesCriterionContainsValidCategories::class);
        }

        if (!\is_array($value)) {
            throw new UnexpectedValueException($value, 'array');
        }

        /** @var array<array-key, string> $categoriesCodes */
        $categoriesCodes = $value['value'];

        $existingCategories = $this->getCategoriesByCodeQuery->execute($categoriesCodes);
        $existingCategoryCodes = \array_column($existingCategories, 'code');

        $nonExistingCategoryCodes = \array_diff($categoriesCodes, $existingCategoryCodes);

        if ($nonExistingCategoryCodes !== []) {
            $this->context
                ->buildViolation(
                    'akeneo_catalogs.validation.product_selection.criteria.category.value',
                    ['{{ codes }}' => \implode(', ', $nonExistingCategoryCodes)],
                )
                ->atPath('[value]')
                ->addViolation();
        }
    }
}
