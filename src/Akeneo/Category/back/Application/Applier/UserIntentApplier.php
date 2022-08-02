<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Applier;

use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;

/**
 * Interface meant for applying user intents on products
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface UserIntentApplier
{
    public function apply(UserIntent $userIntent, CategoryInterface $category, int $userId): void;

    /**
     * @return array<class-string>
     */
    public function getSupportedUserIntents(): array;
}
