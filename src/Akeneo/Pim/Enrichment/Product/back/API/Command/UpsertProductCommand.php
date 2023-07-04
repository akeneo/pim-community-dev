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
     * The product can now be created or updated by :
     * - a ProductIdentifier: the value of the identifier attribute
     * - a ProductUuid: the uuid of the existing product or the one you want to assign to the future product
     * - null: the product will have a random uuid and no identifier.
     *
     * @param ValueUserIntent[] $valueUserIntents
     */
    private function __construct(
        private readonly int $userId,
        private readonly ProductUuid|ProductIdentifier|null $productIdentifierOrUuid,
        private readonly ?FamilyUserIntent $familyUserIntent = null,
        private readonly ?CategoryUserIntent $categoryUserIntent = null,
        private readonly ?ParentUserIntent $parentUserIntent = null,
        private readonly ?GroupUserIntent $groupUserIntent = null,
        private readonly ?SetEnabled $enabledUserIntent = null,
        private readonly ?AssociationUserIntentCollection $associationUserIntents = null,
        private readonly ?QuantifiedAssociationUserIntentCollection $quantifiedAssociationUserIntents = null,
        private readonly array $valueUserIntents = [],
        private readonly bool $dryRun = false,
    ) {
        /*
         * TODO to remove when false negative will be fixed
         * Call to static method Webmozart\Assert\Assert::allImplementsInterface() with array<Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent> and
        'Akeneo\\Pim\\Enrichment\\Product\\API\\Command\\UserIntent\\ValueUserIntent' will always evaluate to false.
         * @phpstan-ignore-next-line
         */
        Assert::allImplementsInterface($this->valueUserIntents, ValueUserIntent::class);
    }

    /**
     * @param UserIntent[] $userIntents
     */
    public static function createWithIdentifier(int $userId, ProductIdentifier $productIdentifier, array $userIntents): self
    {
        return self::create($userIntents, $userId, $productIdentifier);
    }

    /**
     * @param UserIntent[] $userIntents
     */
    public static function createWithUuid(int $userId, ProductUuid $productUuid, array $userIntents): self
    {
        return self::create($userIntents, $userId, $productUuid);
    }

    /**
     * @param UserIntent[] $userIntents
     */
    public static function createWithoutUuidNorIdentifier(int $userId, array $userIntents): self
    {
        return self::create($userIntents, $userId);
    }

    /**
     * @param UserIntent[] $userIntents
     */
    public static function createWithIdentifierDryRun(int $userId, ProductIdentifier $productIdentifier, array $userIntents): self
    {
        return self::create($userIntents, $userId, $productIdentifier, true);
    }

    /**
     * @param UserIntent[] $userIntents
     *
     * @deprecated
     */
    public static function createFromCollection(int $userId, string $productIdentifier, array $userIntents): self
    {
        @trigger_error(
            \sprintf(
                '%s is deprecated and will be removed, please use createWithIdentifier() or createWithUuid() instead',
                __METHOD__
            ),
            \E_USER_DEPRECATED
        );

        return self::create($userIntents, $userId, ProductIdentifier::fromIdentifier($productIdentifier));
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function productIdentifierOrUuid(): ProductIdentifier|ProductUuid|null
    {
        return $this->productIdentifierOrUuid;
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

    public function dryRun(): bool
    {
        return $this->dryRun;
    }

    /**
     * @param UserIntent[] $userIntents
     */
    private static function create(
        array $userIntents,
        int $userId,
        ProductIdentifier|ProductUuid|null $productIdentifierOrUuid = null,
        bool $dryRun = false
    ): self {
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
                Assert::null($groupUserIntent, 'Only one group intent can be passed to the command.');
                $groupUserIntent = $userIntent;
            } elseif ($userIntent instanceof SetEnabled) {
                Assert::null($enabledUserIntent, 'Only one enabled intent can be passed to the command.');
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
            productIdentifierOrUuid: $productIdentifierOrUuid,
            familyUserIntent: $familyUserIntent,
            categoryUserIntent: $categoryUserIntent,
            parentUserIntent: $parentUserIntent,
            groupUserIntent: $groupUserIntent,
            enabledUserIntent: $enabledUserIntent,
            associationUserIntents: [] === $associationUserIntents
                ? null
                : new AssociationUserIntentCollection($associationUserIntents),
            quantifiedAssociationUserIntents: [] === $quantifiedAssociationUserIntents
                ? null
                : new QuantifiedAssociationUserIntentCollection($quantifiedAssociationUserIntents),
            valueUserIntents: $valueUserIntents,
            dryRun: $dryRun
        );
    }
}
