<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Category\ServiceApi\Category;
use Akeneo\Category\ServiceApi\CategoryQueryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CategoryCodesShouldExistValidator extends ConstraintValidator
{
    public function __construct(
        private readonly CategoryQueryInterface $categoryQuery
    ) {
    }

    public function validate($categoryCodes, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, CategoryCodesShouldExist::class);

        if (!\is_array($categoryCodes)) {
            return;
        }

        if (\count($categoryCodes) === 0) {
            return;
        }

        foreach ($categoryCodes as $categoryCode) {
            if (!\is_string($categoryCode)) {
                return;
            }
        }

        $existingCodes = \array_map(
            fn (Category $category): string => $category->getCode(),
            \iterator_to_array($this->categoryQuery->byCodes($categoryCodes)),
        );
        $nonExistingCodes = \array_diff($categoryCodes, $existingCodes);
        if (\count($nonExistingCodes) > 0) {
            $this->context
                ->buildViolation($constraint->categoriesDoNotExist, [
                    '{{ categoryCodes }}' =>  \implode(', ', \array_map(fn (string $value): string => (string) \json_encode($value), $nonExistingCodes)),
                ])
                ->addViolation();
        }
    }
}
