<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Storage\Update;

use Akeneo\Category\Domain\Model\Category;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryTranslationsUpdater implements CategoryUpdater
{
    public function __construct(
        private UpsertCategoryTranslations $updateCategorytranslations,
        private array $supportedUserIntents
    )
    {
    }

    public function update(Category $categoryModel): void
    {
        //TODO: Should we use try/catch ?
        $this->updateCategorytranslations->execute($categoryModel);
    }

    public function getSupportedUserIntents(): array
    {
        return $this->supportedUserIntents;
    }
}
