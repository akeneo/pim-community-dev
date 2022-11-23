<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Save\Query;

use Akeneo\Category\Application\Storage\Save\Query\UpsertCategoryTranslations;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Doctrine\DBAL\Connection;

/**
 * Save values from model into pim_catalog_category_translation table:
 * The values are inserted if the couple (foreign_key, locale) is new, they are updated if the couple already exists.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpsertCategoryTranslationsSql implements UpsertCategoryTranslations
{
    public function __construct(
        private Connection $connection,
        private GetCategoryInterface $getCategory,
    ) {
    }

    public function execute(Category $categoryModel): void
    {
        $categoryId = $categoryModel->getId()?->getValue();
        if (null === $categoryId) {
            throw new \InvalidArgumentException('Cannot upsert category translations on null id.');
        }

        $queries = '';
        $params = ['category_id' => $categoryId];
        $types = ['category_id' => \PDO::PARAM_INT];
        $loopIndex = 0;
        foreach ($categoryModel->getLabels() as $localeCode => $label) {
            if (!$this->isIdenticalLabel($categoryModel, $localeCode, $label)) {
                $queries .= $this->buildUpsertQuery($loopIndex);

                $params['label'.$loopIndex] = $label;
                $params['locale'.$loopIndex] = $localeCode;

                $types['label'.$loopIndex] = \PDO::PARAM_STR;
                $types['locale'.$loopIndex] = \PDO::PARAM_STR;

                ++$loopIndex;
            }
        }

        if (empty($queries)) {
            // previous label translation and new one are identical
            return;
        }

        $this->connection->executeQuery(
            $queries,
            $params,
            $types,
        );
    }

    private function buildUpsertQuery(int $loopIndex): string
    {
        return <<<SQL
            INSERT INTO pim_catalog_category_translation (foreign_key, label, locale)
            VALUES (:category_id, :label$loopIndex, :locale$loopIndex)
            ON DUPLICATE KEY UPDATE label = :label$loopIndex;
        
SQL;
    }

    private function isIdenticalLabel(Category $category, string $localeCode, string|null $label): bool
    {
        $existingLabels = $this->getCategory->byCode((string) $category->getCode())?->getLabels()?->getTranslations();

        if ($existingLabels === null || !array_key_exists($localeCode, $existingLabels)) {
            return false;
        }

        return $existingLabels[$localeCode] === $label;
    }
}
