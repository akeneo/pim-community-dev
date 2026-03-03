<?php

declare(strict_types=1);

namespace Akeneo\Category\Api\Command;

use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpsertCategoryCommand
{
    /**
     * @param UserIntent[] $userIntents
     */
    public function __construct(
        private string $categoryCode,
        private array $userIntents = [],
    ) {
        Assert::allImplementsInterface($this->userIntents, UserIntent::class);
    }

    /**
     * @param userIntent[] $userIntents
     */
    public static function create(string $categoryCode, array $userIntents): self
    {
        $valueUserIntents = [];

        foreach ($userIntents as $userIntent) {
            if ($userIntent instanceof UserIntent) {
                $valueUserIntents[] = $userIntent;
            }
        }

        return new self($categoryCode, $valueUserIntents);
    }

    public function categoryCode(): string
    {
        return $this->categoryCode;
    }

    /**
     * @return UserIntent[]
     */
    public function userIntents(): array
    {
        return $this->userIntents;
    }
}
