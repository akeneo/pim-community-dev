<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Api\Command;

use Akeneo\Pim\Enrichment\Product\Api\Command\UserIntent\ValueUserIntent;
use Webmozart\Assert\Assert;

/**
 * @experimental
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpsertProductCommand
{
    public function __construct(
        private int $userId,
        private string $productIdentifier,
        private $identifierUserIntent = null,
        private $familyUserIntent = null,
        private $categoryUserIntent = null,
        private $parentUserIntent = null,
        private $groupsUserIntent = null,
        private $enabledUserIntent = null,
        private $associationsUserIntent = null,
        private array $valuesUserIntent = []
    ) {
        Assert::allImplementsInterface($this->valuesUserIntent, ValueUserIntent::class);
    }

    public static function createFromCollection(int $userId, string $productIdentifier, array $userIntents): self
    {
        $valuesUserIntents = [];
        foreach ($userIntents as $userIntent) {
            if ($userIntent instanceof ValueUserIntent) {
                $valuesUserIntents[] = $userIntent;
            }
        }

        return new self(
            userId: $userId,
            productIdentifier: $productIdentifier,
            valuesUserIntent: $valuesUserIntents
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
    public function valuesUserIntent(): array
    {
        return $this->valuesUserIntent;
    }
}
