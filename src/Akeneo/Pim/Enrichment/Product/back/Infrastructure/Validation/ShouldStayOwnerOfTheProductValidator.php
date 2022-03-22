<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Category\API\Query\GetOwnedCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\CategoryUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetNonViewableCategoryCodes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * A user, updating a product, cannot remove the own permission. This validator ensures that at least one
 * category is owner on the product (or the product becomes uncategorized).
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ShouldStayOwnerOfTheProductValidator extends ConstraintValidator
{
    public function __construct(
        private GetOwnedCategories $getOwnedCategories,
        private GetNonViewableCategoryCodes $getNonViewableCategoryCodes
    ) {
    }

    public function validate($categoryUserIntent, Constraint $constraint): void
    {
        if (null === $categoryUserIntent || $categoryUserIntent instanceof AddCategories) {
            return;
        }

        Assert::implementsInterface($categoryUserIntent, CategoryUserIntent::class);
        Assert::isInstanceOf($constraint, ShouldStayOwnerOfTheProduct::class);
        $command = $this->context->getRoot();
        Assert::isInstanceOf($command, UpsertProductCommand::class);

        if ($categoryUserIntent instanceof SetCategories) {
            $nonViewableCategoryCodes = $this->getNonViewableCategoryCodes->fromProductIdentifiers([
                ProductIdentifier::fromString($command->productIdentifier()),
            ], $command->userId())[$command->productIdentifier()] ?? [];
            $newCategoryCodes = \array_merge($categoryUserIntent->categoryCodes(), $nonViewableCategoryCodes);
        } else {
            throw new \LogicException('Not implemented');
        }

        if ([] === $newCategoryCodes) {
            // A product without category is always granted (from a category permission point of view).
            return;
        }

        if ([] === $this->getOwnedCategories->forUserId($newCategoryCodes, $command->userId())) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
