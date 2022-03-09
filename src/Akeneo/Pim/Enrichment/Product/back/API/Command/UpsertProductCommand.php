<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\CategoryUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\FamilyUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
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
     * @param ValueUserIntent[] $valueUserIntents
     */
    public function __construct(
        private int $userId,
        private string $productIdentifier,
        private mixed $identifierUserIntent = null,
        private ?FamilyUserIntent $familyUserIntent = null,
        private ?CategoryUserIntent $categoryUserIntent = null,
        private mixed $parentUserIntent = null,
        private mixed $groupsUserIntent = null,
        private ?SetEnabled $enabledUserIntent = null,
        private mixed $associationsUserIntent = null,
        private array $valueUserIntents = []
    ) {
        Assert::allImplementsInterface($this->valueUserIntents, ValueUserIntent::class);
    }

    /**
     * @param UserIntent[] $userIntents
     */
    public static function createFromCollection(int $userId, string $productIdentifier, array $userIntents): self
    {
        $valueUserIntents = [];
        $categoryUserIntent = null;
        $enabledUserIntent = null;
        $familyUserIntent = null;
        foreach ($userIntents as $userIntent) {
            if ($userIntent instanceof ValueUserIntent) {
                $valueUserIntents[] = $userIntent;
            } elseif ($userIntent instanceof SetEnabled) {
                Assert::null($enabledUserIntent, "Only one SetEnabled intent can be sent to the command.");
                $enabledUserIntent = $userIntent;
            } elseif ($userIntent instanceof FamilyUserIntent) {
                Assert::null($familyUserIntent, 'A family user intent cannot be defined twice');
                $familyUserIntent = $userIntent;
            } elseif ($userIntent instanceof CategoryUserIntent) {
                Assert::null($enabledUserIntent, "Only one Category intent can be sent to the command.");
                $categoryUserIntent = $userIntent;
            }
        }

        return new self(
            userId: $userId,
            productIdentifier: $productIdentifier,
            familyUserIntent: $familyUserIntent,
            categoryUserIntent: $categoryUserIntent,
            enabledUserIntent: $enabledUserIntent,
            valueUserIntents: $valueUserIntents
        );
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function productIdentifier(): string
    {
        return $this->productIdentifier;
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

    public function enabledUserIntent(): ?SetEnabled
    {
        return $this->enabledUserIntent;
    }
}
