<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Category\API\Query\GetExistingCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\CategoryUserIntent;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CategoriesShouldExistValidator extends ConstraintValidator
{
    public function __construct(private GetExistingCategories $getExistingCategories)
    {
    }

    public function validate($categoryUserIntent, Constraint $constraint): void
    {
        if (null === $categoryUserIntent) {
            return;
        }
        Assert::isInstanceOf($categoryUserIntent, CategoryUserIntent::class);
        Assert::isInstanceOf($constraint, CategoriesShouldExist::class);

        $command = $this->context->getRoot();
        Assert::isInstanceOf($command, UpsertProductCommand::class);

        $categoryCodes = $categoryUserIntent->categoryCodes();

        $nonExistingCategories = \array_diff($categoryCodes, $this->getExistingCategories->forCodes($categoryCodes));

        if (\count($nonExistingCategories) > 0) {
            $this->context->buildViolation(
                $constraint->message,
                [
                    '{{ categoryCodes }}' => \implode(', ', $nonExistingCategories),
                    '%count%' => \count($nonExistingCategories),
                ]
            )->addViolation();
        }
    }
}
