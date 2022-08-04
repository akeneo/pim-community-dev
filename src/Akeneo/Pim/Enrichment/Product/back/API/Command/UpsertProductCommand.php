<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociationUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociationUserIntentCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\CategoryUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\FamilyUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\GroupUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ParentUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedAssociationUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedAssociationUserIntentCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Webmozart\Assert\Assert;

/**
 * @experimental
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpsertProductCommand
{
    /**
     * @param ProductIdentifier|ProductUuid|string|null $identifierOrUuid
     * The product can now be created or updated by :
     * - a string: will be deprecated, related to the only attribute identifier
     * - a ProductIdentifier: a pair attribute of the identifier code + the attribute code
     * - a ProductUuid: the uuid of the existing product or the one you want to assign to the future product
     * - null: the product will have a random uuid and no identifier
     * @param ValueUserIntent[] $valueUserIntents
     */
    public function __construct(
        private int $userId,
        private ProductIdentifier | ProductUuid | string | null $identifierOrUuid = null,
        private ?FamilyUserIntent $familyUserIntent = null,
        private ?CategoryUserIntent $categoryUserIntent = null,
        private ?ParentUserIntent $parentUserIntent = null,
        private ?GroupUserIntent $groupUserIntent = null,
        private ?SetEnabled $enabledUserIntent = null,
        private ?AssociationUserIntentCollection $associationUserIntents = null,
        private ?QuantifiedAssociationUserIntentCollection $quantifiedAssociationUserIntents = null,
        private array $valueUserIntents = []
    ) {
        Assert::allImplementsInterface($this->valueUserIntents, ValueUserIntent::class);
    }

    /**
     * @param UserIntent[] $userIntents
     */
    public static function createFromCollection(int $userId, ProductIdentifier | ProductUuid | string | null $identifierOrUuid = null, array $userIntents = []): self
    {
        $valueUserIntents = [];
        $categoryUserIntent = null;
        $groupUserIntent = null;
        $enabledUserIntent = null;
        $familyUserIntent = null;
        $parentUserIntent = null;
        $associationUserIntents = \array_values(
            \array_filter($userIntents, fn ($userIntent): bool => $userIntent instanceof AssociationUserIntent)
        );
        $quantifiedAssociationUserIntents = \array_values(
            \array_filter($userIntents, fn ($userIntent): bool => $userIntent instanceof QuantifiedAssociationUserIntent)
        );
        foreach ($userIntents as $userIntent) {
            if ($userIntent instanceof ValueUserIntent) {
                $valueUserIntents[] = $userIntent;
            } elseif ($userIntent instanceof GroupUserIntent) {
                Assert::null($groupUserIntent, "Only one groups intent can be passed to the command.");
                $groupUserIntent = $userIntent;
            } elseif ($userIntent instanceof SetEnabled) {
                Assert::null($enabledUserIntent, "Only one enabled intent can be passed to the command.");
                $enabledUserIntent = $userIntent;
            } elseif ($userIntent instanceof FamilyUserIntent) {
                Assert::null($familyUserIntent, 'Only one family intent can be passed to the command.');
                $familyUserIntent = $userIntent;
            } elseif ($userIntent instanceof CategoryUserIntent) {
                Assert::null($categoryUserIntent, 'Only one category intent can be passed to the command.');
                $categoryUserIntent = $userIntent;
            } elseif ($userIntent instanceof ParentUserIntent) {
                Assert::null($parentUserIntent, 'Only one parent intent can be passed to the command.');
                $parentUserIntent = $userIntent;
            }
        }

        return new self(
            userId: $userId,
            identifierOrUuid: $identifierOrUuid,
            familyUserIntent: $familyUserIntent,
            categoryUserIntent: $categoryUserIntent,
            parentUserIntent: $parentUserIntent,
            groupUserIntent: $groupUserIntent,
            enabledUserIntent: $enabledUserIntent,
            associationUserIntents: [] ===  $associationUserIntents
                ? null
                : new AssociationUserIntentCollection($associationUserIntents),
            quantifiedAssociationUserIntents: [] === $quantifiedAssociationUserIntents
                ? null
                : new QuantifiedAssociationUserIntentCollection($quantifiedAssociationUserIntents),
            valueUserIntents: $valueUserIntents
        );
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function identifierOrUuid(): ProductIdentifier | ProductUuid | string | null
    {
        return $this->identifierOrUuid;
    }

    public function familyUserIntent(): ?FamilyUserIntent
    {
        return $this->familyUserIntent;
    }

    public function categoryUserIntent(): ?CategoryUserIntent
    {
        return $this->categoryUserIntent;
    }

    /**
     * @return ValueUserIntent[]
     */
    public function valueUserIntents(): array
    {
        return $this->valueUserIntents;
    }

    public function parentUserIntent(): ?ParentUserIntent
    {
        return $this->parentUserIntent;
    }

    public function groupUserIntent(): ?GroupUserIntent
    {
        return $this->groupUserIntent;
    }

    public function enabledUserIntent(): ?SetEnabled
    {
        return $this->enabledUserIntent;
    }

    public function associationUserIntents(): ?AssociationUserIntentCollection
    {
        return $this->associationUserIntents;
    }

    public function quantifiedAssociationUserIntents(): ?QuantifiedAssociationUserIntentCollection
    {
        return $this->quantifiedAssociationUserIntents;
    }
}
