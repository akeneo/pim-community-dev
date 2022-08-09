<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Storage;

use Akeneo\Category\Application\Storage\Update\CategoryUpdater;
use Akeneo\Category\Application\Storage\Update\CategoryUpdaterRegistry;
use Akeneo\Category\Application\Storage\Update\UpsertCategoryProperties;
use Akeneo\Category\Application\Storage\Update\UpsertCategoryTranslations;
use Akeneo\Category\Domain\Model\Category;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProcessCategoryUpdate
{
    // The order in which the updater will be executed
    private array $updaterExecutionOrder = [
        'properties' => null,
        'translations' => null,
    ];

    public function __construct(
        private CategoryUpdaterRegistry $categoryUpdaterRegistry
    )
    {
    }

    public function update(Category $categoryModel, array $userIntents):void
    {
        $updaters = [];
        foreach ($userIntents as $userIntent) {
            $updater = $this->categoryUpdaterRegistry->fromUserIntent($userIntent);
            if (!\in_array($updater, $updaters)) {
                $updaters[] = $updater;
            }
        }

        if (\empty($updater)) {
            return;
        }

        // sort the updaters to be executed based on order list
        foreach($updaters as $updater) {
            if ($updater instanceOf UpsertCategoryProperties::class) {
                $this->addUpdaterInOrderList('properties', $updater);
                continue;
            }

            if ($updater instanceOf UpsertCategoryTranslations::class) {
                $this->addUpdaterInOrderList('translations', $updater);
                continue;
            }

            throw new \LogicException(\sprintf('Tis updater was not expected: %s', $updater::class));
        }

        foreach ($this->updaterExecutionOrder as $updater)
        {
            /** @var CategoryUpdater $updater */
            $updater->update($categoryModel);
        }
    }

    private function addUpdaterInOrderList(string $key, CategoryUpdater $updater)
    {
        if (
            !\array_key_exists($key, $this->updaterExecutionOrder)
            || null !== $this->updaterExecutionOrder[$key]
        ) {
            throw new \LogicException(\sprintf("The updater has already been set for execution: %s", $updater::class));
        }

        $this->updaterExecutionOrder[$key] = $updater;
    }
}
