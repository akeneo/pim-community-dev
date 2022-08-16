<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Storage\Save;

use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryBaseSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategorySaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTranslationsSaver;
use Akeneo\Category\Domain\Model\Category;

/**
 * This is the entry point to save a Category. The categoryModel is used to get the data's values to save.
 * The user intents are used to only call the dedicated savers, based on the data actually changed.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProcessCategorySave
{
    // List of expected savers: it also assures the order in which the savers will be executed
    private array $saversExecutionOrder = [
        'category' => null,
        'category_translation' => null,
    ];

    public function __construct(
        private CategorySaverRegistry $categorySaverRegistry
    )
    {
    }

    /**
     * @param Category $categoryModel
     * @param UserIntent[] $userIntents
     * @return void
     */
    public function save(Category $categoryModel, array $userIntents): void
    {
        foreach ($userIntents as $userIntent) {
            $saver = $this->categorySaverRegistry->fromUserIntent($userIntent::class);

            if ($saver instanceOf CategoryBaseSaver) {
                $this->addSaverInOrderList('category', $saver);
                continue;
            }

            if ($saver instanceOf CategoryTranslationsSaver) {
                $this->addSaverInOrderList('category_translation', $saver);
                continue;
            }

            throw new \LogicException(\sprintf('This saver was not expected: %s', $saver::class));
        }

        foreach ($this->saversExecutionOrder as $saver)
        {
            /** @var CategorySaver $saver */
            $saver->save($categoryModel);
        }

        // TODO: hydrate a Category Object with all the new data and return it ?
    }

    private function addSaverInOrderList(string $key, CategorySaver $saver): void
    {
        if (
            !\array_key_exists($key, $this->saversExecutionOrder)
            || null !== $this->saversExecutionOrder[$key]
        ) {
            throw new \LogicException(\sprintf("The saver has already been set for execution: %s", $saver::class));
        }

        $this->saversExecutionOrder[$key] = $saver;
    }
}
