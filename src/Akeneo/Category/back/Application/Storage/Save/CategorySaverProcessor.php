<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Storage\Save;

use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryBaseSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategorySaver as CategorySaverInterface;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTranslationsSaver;
use Akeneo\Category\Domain\Model\Enrichment\Category;

/**
 * This is the entry point to save a Category. The category is used to get the data's values to save.
 * The user intents are used to only call the dedicated savers, based on the data actually changed.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategorySaverProcessor implements SaveCategory
{
    /**
     * List of expected savers: it also ensures the order in which the savers will be executed.
     *
     * @var array<string, ?CategorySaverInterface>
     */
    private array $saversExecutionOrder = [
        'category' => null,
        'category_translation' => null,
    ];

    public function __construct(
        private readonly CategorySaverRegistry $categorySaverRegistry,
    ) {
    }

    /**
     * @param UserIntent[] $userIntents
     */
    public function save(Category $category, array $userIntents): void
    {
        foreach ($userIntents as $userIntent) {
            $saver = $this->categorySaverRegistry->fromUserIntent($userIntent::class);

            match (true) {
                $saver instanceof CategoryBaseSaver => $this->saversExecutionOrder['category'] = $saver,
                $saver instanceof CategoryTranslationsSaver => $this->saversExecutionOrder['category_translation'] = $saver,
                default => throw new \LogicException(\sprintf('This saver was not expected: %s', $saver::class))
            };
        }

        foreach ($this->saversExecutionOrder as $saver) {
            $saver?->save($category);
        }
    }
}
