<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Storage;

use Akeneo\Category\Application\Storage\Save\CategoryBaseSaver;
use Akeneo\Category\Application\Storage\Save\CategorySaver;
use Akeneo\Category\Application\Storage\Save\CategorySaverRegistry;
use Akeneo\Category\Application\Storage\Save\CategoryTranslationsSaver;
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
    // The order in which the savers will be executed
    private array $saversExecutionOrder = [
        'category' => null,
        'category_translation' => null,
    ];

    public function __construct(
        private CategorySaverRegistry $categorySaverRegistry
    )
    {
    }

    public function save(Category $categoryModel, array $userIntents): void
    {
        foreach ($userIntents as $userIntent) {
            $saver = $this->categorySaverRegistry->fromUserIntent($userIntent);

            if ($saver instanceOf CategoryBaseSaver::class) {
                $this->addSaverInOrderList('category', $saver);
                continue;
            }

            if ($saver instanceOf CategoryTranslationsSaver::class) {
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
