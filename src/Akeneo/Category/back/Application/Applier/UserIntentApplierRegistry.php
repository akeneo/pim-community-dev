<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Applier;

use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserIntentApplierRegistry
{
    /**
     * @var array<string, UserIntentApplier>
     */
    private array $userIntentAppliers;

    /**
     * @param iterable<UserIntentApplier> $userIntentAppliers
     */
    public function __construct(iterable $userIntentAppliers)
    {
        Assert::allIsInstanceOf($userIntentAppliers, UserIntentApplier::class);
        foreach ($userIntentAppliers as $userIntentApplier) {
            foreach ($userIntentApplier->getSupportedUserIntents() as $userIntentClass) {
                $this->userIntentAppliers[$userIntentClass] = $userIntentApplier;
            }
        }
    }

    public function getApplier(UserIntent $userIntent): ?UserIntentApplier
    {
        return $this->userIntentAppliers[$userIntent::class] ?? null;
    }
}
