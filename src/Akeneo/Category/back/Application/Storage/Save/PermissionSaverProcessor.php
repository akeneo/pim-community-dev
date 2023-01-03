<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Category\Application\Storage\Save;

use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Akeneo\Category\Application\Storage\Save\CategorySaverRegistry;
use Akeneo\Category\Application\Storage\Save\SaveCategory;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryBaseSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategorySaver as CategorySaverInterface;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTranslationsSaver;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use AkeneoEnterprise\Category\Application\Storage\Save\Remover\CategoryPermissionRemover;
use AkeneoEnterprise\Category\Application\Storage\Save\Saver\CategoryPermissionSaver;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class PermissionSaverProcessor implements SaveCategory
{
    /**
     * @var array<string, ?CategorySaverInterface>
     */
    private array $saversExecutionOrder = [
        'category' => null,
        'category_translation' => null,
        'add_permission' => null,
        'remove_permission' => null,
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
                $saver instanceof CategoryPermissionSaver => $this->saversExecutionOrder['add_permission'] = $saver,
                $saver instanceof CategoryPermissionRemover => $this->saversExecutionOrder['remove_permission'] = $saver,
                default => throw new \LogicException(\sprintf('This saver was not expected: %s', $saver::class))
            };
        }

        foreach ($this->saversExecutionOrder as $saver) {
            $saver?->save($category);
        }
    }
}
