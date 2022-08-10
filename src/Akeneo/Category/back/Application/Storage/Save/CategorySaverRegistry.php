<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Storage\Save;

use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Webmozart\Assert\Assert;

/**
 * This class returns a category data saver based on a user intent.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategorySaverRegistry
{
    private array $categorySaverByUserIntent;
    /**
     * @param CategorySaver[] $categorySavers
     */
    public function __construct(
        private array $categorySavers
    )
    {
        foreach ($this->categorySavers as $categorySaver) {
            Assert::isInstanceOf($categorySaver, CategorySaver::class);
            $supportedUserIntents = $categorySaver->getSupportedUserIntents();
            foreach($supportedUserIntents as $userIntent) {
                if (\array_key_exists($userIntent::class, $this->categorySaverByUserIntent ?? [])) {
                    //TODO: this is to discuss with the team
                    throw new \LogicException(\sprintf('There cannot be more than one category saver supporting user intent: %s', $userIntent::class));
                }
                $this->categorySaverByUserIntent[$userIntent::class] = $categorySaver;
            }
        }
    }

    public function fromUserIntent(UserIntent $userIntent): CategorySaver
    {
        $saver = $this->categorySaverByUserIntent[$userIntent::class] ?? null;
        if (null === $saver) {
            throw new \InvalidArgumentException(\sprintf('No category saver linked to %s userIntent', $userIntent::class));
        }

        return $saver;
    }
}
