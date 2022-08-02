<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Category\API\Query\GetOwnedCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\CategoryUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier as ProductIdentifierValueObject;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ViolationCode;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
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
        private GetNonViewableCategoryCodes $getNonViewableCategoryCodes,
        private GetCategoryCodes $getCategoryCodes
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
            $nonViewableCategoryCodes = [];
            if ($command->identifierOrUuid() instanceof ProductIdentifierValueObject) {
                $nonViewableCategoryCodes = $this->getNonViewableCategoryCodes->fromProductIdentifiers([
                        ProductIdentifier::fromString($command->identifierOrUuid()->identifier()),
                    ], $command->userId())[$command->identifierOrUuid()->identifier()] ?? [];
            } elseif ($command->identifierOrUuid() instanceof ProductUuid) {
                $nonViewableCategoryCodes = $this->getNonViewableCategoryCodes->fromProductUuids([
                        $command->identifierOrUuid()->uuid(),
                    ], $command->userId())[$command->identifierOrUuid()->uuid()->toString()] ?? [];
            } elseif (\is_string($command->identifierOrUuid())) {
                $nonViewableCategoryCodes = $this->getNonViewableCategoryCodes->fromProductIdentifiers([
                        ProductIdentifier::fromString($command->identifierOrUuid()),
                    ], $command->userId())[$command->identifierOrUuid()] ?? [];
            }

            $newCategoryCodes = \array_merge($categoryUserIntent->categoryCodes(), $nonViewableCategoryCodes);
        } elseif ($categoryUserIntent instanceof RemoveCategories) {
            $productCategoryCodes = [];
            if ($command->identifierOrUuid() instanceof ProductIdentifierValueObject) {
                $productCategoryCodes = $this->getCategoryCodes->fromProductIdentifiers([
                        ProductIdentifier::fromString($command->identifierOrUuid()->identifier()),
                    ])[$command->identifierOrUuid()->identifier()] ?? [];
            } elseif ($command->identifierOrUuid() instanceof ProductUuid) {
                $productCategoryCodes = $this->getCategoryCodes->fromProductUuids([
                        $command->identifierOrUuid()->uuid()
                    ])[$command->identifierOrUuid()->uuid()->toString()] ?? [];
            } elseif (\is_string($command->identifierOrUuid())) {
                $productCategoryCodes = $this->getCategoryCodes->fromProductIdentifiers([
                        ProductIdentifier::fromString($command->identifierOrUuid()),
                    ])[$command->identifierOrUuid()] ?? [];
            }

            $newCategoryCodes = \array_values(\array_diff($productCategoryCodes, $categoryUserIntent->categoryCodes()));
        } else {
            throw new \LogicException('Not implemented');
        }

        if ([] === $newCategoryCodes) {
            // A product without category is always granted (from a category permission point of view).
            return;
        }

        if ([] === $this->getOwnedCategories->forUserId($newCategoryCodes, $command->userId())) {
            $this->context->buildViolation($constraint->message)
                ->setCode((string) ViolationCode::PERMISSION)
                ->addViolation();
        }
    }
}
