<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Category\Application\Storage\Update;

use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Webmozart\Assert\Assert;

class CategoryUpdaterRegistry
{
    private array $categoryUpdaterByUserIntent;
    /**
     * @param CategoryUpdater[] $categoryUpdaters
     */
    public function __construct(
        private array $categoryUpdaters
    )
    {
        foreach ($this->categoryUpdaters as $categoryUpdater) {
            Assert::isInstanceOf($categoryUpdater, CategoryUpdater::class);
            $supportedUserIntents = $categoryUpdater->getSupportedUserIntents();
            foreach($supportedUserIntents as $userIntent) {
                if (\array_key_exists($userIntent::class, $this->categoryUpdaterByUserIntent ?? [])) {
                    //TODO: this is to discuss with the team
                    throw new \LogicException(\sprintf('There cannot be more than one category updater supporting user intent: %s', $userIntent::class));
                }
                $this->categoryUpdaterByUserIntent[$userIntent::class] = $categoryUpdater;
            }
        }
    }

    public function fromUserIntent(UserIntent $userIntent): CategoryUpdater
    {
        $updater = $this->categoryUpdaterByUserIntent[$userIntent::class] ?? null;
        if (null === $updater) {
            throw new \InvalidArgumentException(\sprintf('No category updater linked to %s userIntent', $userIntent::class));
        }

        return $updater;
    }
}
