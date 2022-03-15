<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Category\API\Query\GetOwnedCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\CategoryUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * Check the user has own permission on the product
 *  - if the product exists, we should check the user owns it
 *  - if new categories are provided for the product, we should check the user still owns it after update.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class IsUserOwnerOfTheProductValidator extends ConstraintValidator
{
    public function __construct(
        private GetCategoryCodes $getCategoryCodes,
        private GetOwnedCategories $getOwnedCategories
    ) {
    }

    public function validate($command, Constraint $constraint): void
    {
        Assert::isInstanceOf($command, UpsertProductCommand::class);
        Assert::isInstanceOf($constraint, IsUserOwnerOfTheProduct::class);

        try {
            $productIdentifier = ProductIdentifier::fromString($command->productIdentifier());
        } catch (\InvalidArgumentException) {
            return;
        }

        if (!$this->checkAtLeastOneCurrentProductCategoryIsGranted($productIdentifier, $command->userId())) {
            $this->context->buildViolation($constraint->noAccessMessage)->addViolation();

            return;
        }

        $categoryUserIntent = $command->categoryUserIntent();
        if (null === $categoryUserIntent) {
            return;
        }

        if (!$this->checkAtLeastOneNewCategoryIsGranted($categoryUserIntent, $command->userId())) {
            $this->context->buildViolation($constraint->shouldKeepOneOwnedCategoryMessage)->addViolation();
        }
    }

    private function checkAtLeastOneCurrentProductCategoryIsGranted(ProductIdentifier $productIdentifier, int $userId): bool
    {
        $productCategoryCodes = $this->getCategoryCodes->fromProductIdentifiers([$productIdentifier])[$productIdentifier->asString()] ?? [];
        if (0 < \count($productCategoryCodes)) {
            if ([] === $this->getOwnedCategories->forUserId($productCategoryCodes, $userId)) {
                return false;
            }
        }

        return true;
    }

    private function checkAtLeastOneNewCategoryIsGranted(CategoryUserIntent $categoryUserIntent, int $userId): bool
    {
        if ($categoryUserIntent instanceof SetCategories) {
            $newCategoryCodes = $categoryUserIntent->categoryCodes();
        } else {
            throw new \LogicException('Not implemented');
        }

        if ([] === $newCategoryCodes) {
            // A product without category is always granted (from a category permission point of view).
            return true;
        }

        return [] !== $this->getOwnedCategories->forUserId($newCategoryCodes, $userId);
    }
}
