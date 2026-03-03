<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Storage\Save\Saver;

use Akeneo\Category\Application\Storage\Save\Query\UpsertCategoryTranslations;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\UpdateCategoryUpdatedDate;

/**
 * This class is used to call the save query for label translations of the category (data stored in translation table).
 * This contains the list of supported user intents which relate to the labels of the category.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryTranslationsSaver implements CategorySaver
{
    /**
     * @param string[] $supportedUserIntents
     */
    public function __construct(
        private readonly UpsertCategoryTranslations $upsertCategoryTranslations,
        private readonly UpdateCategoryUpdatedDate $updateCategoryUpdatedDate,
        private readonly array $supportedUserIntents,
    ) {
    }

    public function save(Category $categoryModel): void
    {
        $this->upsertCategoryTranslations->execute($categoryModel);
        $this->updateCategoryUpdatedDate->execute((string) $categoryModel->getCode());
    }

    public function getSupportedUserIntents(): array
    {
        return $this->supportedUserIntents;
    }
}
