<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Storage\Save\Saver;

use Akeneo\Category\Application\Storage\Save\Query\UpsertCategoryBase;
use Akeneo\Category\Domain\Model\Enrichment\Category;

/**
 * This class is used to call the save query for base data of the category (data stored in category table).
 * This contains the list of supported user intents which relate to the base data of the category.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryBaseSaver implements CategorySaver
{
    /**
     * @param string[] $supportedUserIntents
     */
    public function __construct(
        private UpsertCategoryBase $upsertCategoryBase,
        private array $supportedUserIntents,
    ) {
    }

    public function save(Category $categoryModel): void
    {
        $this->upsertCategoryBase->execute($categoryModel);
    }

    public function getSupportedUserIntents(): array
    {
        return $this->supportedUserIntents;
    }
}
