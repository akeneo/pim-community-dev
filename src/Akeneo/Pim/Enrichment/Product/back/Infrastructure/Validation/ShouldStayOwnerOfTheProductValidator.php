<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Category\API\Query\GetOwnedCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\CategoryUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuids;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ViolationCode;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetNonViewableCategoryCodes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * A user, updating a product, cannot remove the own permission. This validator ensures that at least one
 * owned category is left (or that the product becomes uncategorized).
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ShouldStayOwnerOfTheProductValidator extends ConstraintValidator
{
    public function __construct(
        private GetOwnedCategories $getOwnedCategories,
        private GetNonViewableCategoryCodes $getNonViewableCategoryCodes,
        private GetCategoryCodes $getCategoryCodes,
        private GetProductUuids $getProductUuids,
    ) {
    }

    public function validate($categoryUserIntent, Constraint $constraint): void
    {
        if (null === $categoryUserIntent || $categoryUserIntent instanceof AddCategories) {
            return;
        }

        Assert::implementsInterface($categoryUserIntent, CategoryUserIntent::class);
        Assert::isInstanceOf($constraint, ShouldStayOwnerOfTheProduct::class);
        /** @var UpsertProductCommand $command */
        $command = $this->context->getRoot();
        Assert::isInstanceOf($command, UpsertProductCommand::class);

        $uuid = null;
        if ($command->productIdentifierOrUuid() instanceof ProductIdentifier) {
            $uuid = $this->getProductUuids->fromIdentifier($command->productIdentifierOrUuid()->identifier());
        } elseif ($command->productIdentifierOrUuid() instanceof ProductUuid) {
            $uuid = $this->getProductUuids->fromUuid($command->productIdentifierOrUuid()->uuid());
        }

        if (null === $uuid) {
            // product creation mode
            return;
        }

        $formerProductCategoryCodes = ($this->getCategoryCodes->fromProductUuids([$uuid])[$uuid->toString()] ?? []);
        $formerOwnedCategoryCodes = $formerProductCategoryCodes
            ? ($this->getOwnedCategories->forUserId($formerProductCategoryCodes, $command->userId()))
            : [];
        if ([] !== $formerProductCategoryCodes && [] === $formerOwnedCategoryCodes) {
            // the user does not own the product, another validator should raise a violation
            return;
        }

        if ($categoryUserIntent instanceof SetCategories) {
            $newCategoryCodes = $categoryUserIntent->categoryCodes();
        } elseif ($categoryUserIntent instanceof RemoveCategories) {
            $newCategoryCodes = \array_values(\array_diff($formerProductCategoryCodes, $categoryUserIntent->categoryCodes()));
        } else {
            throw new \LogicException('Not implemented');
        }

        if (\count(\array_intersect($formerOwnedCategoryCodes, $newCategoryCodes)) > 0) {
            // there's still an owned category
            return;
        }

        if ([] === $newCategoryCodes &&
            [] === ($this->getNonViewableCategoryCodes->fromProductUuids([$uuid], $command->userId())[$uuid->toString()] ?? [])
        ) {
            // the product is unclassified
            return;
        }

        if ([] !== $newCategoryCodes && \count($this->getOwnedCategories->forUserId($newCategoryCodes, $command->userId())) > 0) {
            // the user removed all previously owned categories but added a new one they also own
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setCode((string) ViolationCode::PERMISSION)
            ->addViolation();
    }
}
