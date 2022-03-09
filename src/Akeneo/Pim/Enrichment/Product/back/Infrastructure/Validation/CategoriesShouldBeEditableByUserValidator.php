<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Category\API\Query\GetOwnedCategories;
use Akeneo\Pim\Enrichment\Category\API\Query\GetReadCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\CategoryUserIntent;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CategoriesShouldBeEditableByUserValidator extends ConstraintValidator
{
    public function __construct(
        private GetOwnedCategories $getOwnedCategories,
        private GetReadCategories $getReadCategories
    ) {
    }

    public function validate($categoryUserIntent, Constraint $constraint): void
    {
        Assert::isInstanceOf($categoryUserIntent, CategoryUserIntent::class);
        Assert::isInstanceOf($constraint, CategoriesShouldBeEditableByUser::class);

        $command = $this->context->getRoot();
        Assert::isInstanceOf($command, UpsertProductCommand::class);

        $categoryCodes = $categoryUserIntent->categoriesCodes();
        if ([] === $categoryCodes) {
            return;
        }

        $ownedCategories = $this->getOwnedCategories->forUserId($categoryCodes, $command->userId());
        $nonOwnedCategories = \array_values(\array_diff($categoryCodes, $ownedCategories));

        if (\count($nonOwnedCategories) === 0) {
            return;
        }

        $readCategories = $this->getReadCategories->forUserId($nonOwnedCategories, $command->userId());
        $nonReadCategories = \array_values(\array_diff($nonOwnedCategories, $readCategories));

        if (\count($nonReadCategories) > 0) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
