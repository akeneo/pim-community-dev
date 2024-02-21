<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Storage\Save;

use Akeneo\Category\Application\Storage\Save\Saver\CategorySaver;
use Webmozart\Assert\Assert;

/**
 * This class returns a category data saver based on a user intent.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategorySaverRegistry
{
    /** @var array<string, CategorySaver> */
    private array $categorySaverByUserIntent;

    /**
     * @param iterable<string, CategorySaver> $categorySavers
     */
    public function __construct(
        private readonly iterable $categorySavers,
    ) {
        $this->sortCategorySaversByUserIntent();
    }

    public function fromUserIntent(string $userIntentClassName): CategorySaver
    {
        $saver = $this->categorySaverByUserIntent[$userIntentClassName] ?? null;
        if (null === $saver) {
            throw new \InvalidArgumentException(\sprintf('No category saver linked to %s userIntent', $userIntentClassName));
        }

        return $saver;
    }

    private function sortCategorySaversByUserIntent(): void
    {
        foreach ($this->categorySavers as $categorySaver) {
            Assert::isInstanceOf($categorySaver, CategorySaver::class);
            $supportedUserIntents = $categorySaver->getSupportedUserIntents();
            foreach ($supportedUserIntents as $userIntentName) {
                if (\array_key_exists($userIntentName, $this->categorySaverByUserIntent ?? [])) {
                    throw new \LogicException(\sprintf('There cannot be more than one category saver supporting user intent: %s', $userIntentName));
                }
                $this->categorySaverByUserIntent[$userIntentName] = $categorySaver;
            }
        }
    }
}
