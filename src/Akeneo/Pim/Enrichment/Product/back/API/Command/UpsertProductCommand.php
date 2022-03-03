<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command;

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
        private mixed $familyUserIntent = null,
        private mixed $categoryUserIntent = null,
        private mixed $parentUserIntent = null,
        private mixed $groupsUserIntent = null,
        private mixed $enabledUserIntent = null,
        private mixed $associationsUserIntent = null,
        private array $valueUserIntents = []
    ) {
        Assert::allImplementsInterface($this->valueUserIntents, ValueUserIntent::class);
    }

    /**
     * @param ValueUserIntent[] $userIntents
     */
    public static function createFromCollection(int $userId, string $productIdentifier, array $userIntents): self
    {
        $valueUserIntents = [];
        foreach ($userIntents as $userIntent) {
            if ($userIntent instanceof ValueUserIntent) {
                $valueUserIntents[] = $userIntent;
            }
        }

        return new self(
            userId: $userId,
            productIdentifier: $productIdentifier,
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

    /**
     * @return ValueUserIntent[]
     */
    public function valueUserIntents(): array
    {
        return $this->valueUserIntents;
    }
}
