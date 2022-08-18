<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Save\Query;

use Akeneo\Category\Application\Storage\Save\Query\UpsertCategoryTranslations;
use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Doctrine\DBAL\Connection;

/**
 * Save values from model into pim_catalog_category_translation table:
 * The values are inserted if the couple (foreign_key, locale) is new, they are updated if the couple already exists.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlUpsertCategoryTranslations implements UpsertCategoryTranslations
{
    /** @var array<string, string> $cachedLocales */
    private array $cachedLocales = [];

    // All data needed to build the insert query
    /** @var array<string> $insertionQueries */
    private array $insertionQueries = [];
    /** @var array<string, mixed> $insertionParams */
    private array $insertionParams = [];
    /** @var array<string, int> $insertionTypes */
    private array $insertionTypes = [];

    // All data needed to build the update query
    /** @var array<string> $updateQueries */
    private array $updateQueries = [];
    /** @var array<array<string, mixed>> $updateParams */
    private array $updateParams = [];
    /** @var array<array<string, int>> $updateTypes */
    private array $updateTypes = [];

    public function __construct(private Connection $connection)
    {
    }

    public function execute(Category $categoryModel): void
    {
        $this->fetchExistingTranslationsByCategoryCode($categoryModel->getCode());

        foreach ($categoryModel->getLabelCollection() as $localeCode => $label) {
            if ($this->localeAlreadyExists($localeCode)) {
                if (!$this->labelIsTheSame($localeCode, $label)) {
                    $count = count($this->updateQueries);
                    $this->updateQueries[] = \sprintf(
                        'SELECT :category_id AS foreign_key, :label_%d AS label, :locale_%d AS locale',
                        $count,
                        $count
                    );

                    $this->updateParams['label_' . $count] = $label;
                    $this->updateParams['locale_' . $count] = $localeCode;

                    $this->updateTypes['label_' . $count] = \PDO::PARAM_STR;
                    $this->updateTypes['locale_' . $count] = \PDO::PARAM_STR;
                }
            } else {
                $count = count($this->insertionQueries);
                $this->insertionQueries[] = \sprintf(
                    '(:category_id, :label_%d, :locale_%d)',
                    $count,
                    $count
                );

                $this->insertionParams['label_' . (string) $count] = $label;
                $this->insertionParams['locale_' . (string) $count] = $localeCode;

                $this->insertionTypes['label_' . (string) $count] = \PDO::PARAM_STR;
                $this->insertionTypes['locale_' . (string) $count] = \PDO::PARAM_STR;
            }
        }

        if (!empty($this->insertionQueries)) {
            $this->insertCategoryTranslations($categoryModel->getCode());
        }

        if (!empty($this->updateQueries)) {
            $this->updateCategoryTranslations($categoryModel->getCode());
        }

        $this->insertionParams = [];
        $this->insertionTypes = [];
        $this->insertionQueries = [];
    }

    /**
     * @param Code $categoryCode
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    private function insertCategoryTranslations(Code $categoryCode): void
    {
        // First we retrieve the category id
        $categoryId = $this->getCategoryIdFromCode($categoryCode)->getValue();

        // Then we insert the labels
        $query = $this->buildInsertQuery();
        $this->insertionParams['category_id'] = $categoryId;
        $this->insertionTypes['category_id'] = \PDO::PARAM_INT;

        $this->connection->executeQuery(
            $query,
            $this->insertionParams,
            $this->insertionTypes,
        );
    }

    /**
     * @param Code $categoryCode
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    private function updateCategoryTranslations(Code $categoryCode): void
    {
        // First we retrieve the category id
        $categoryId = $this->getCategoryIdFromCode($categoryCode)->getValue();

        // Then we update the translations
        $query = $this->buildUpdateQuery();
        $this->updateParams['category_id'] = $categoryId;
        $this->updateTypes['category_id'] = \PDO::PARAM_INT;

        $this->connection->executeQuery(
            $query,
            $this->updateParams,
            $this->updateTypes,
        );
    }

    private function buildInsertQuery(): string
    {
        $query = <<<SQL
            INSERT INTO pim_catalog_category_translation
                (foreign_key, label, locale)
            VALUES
                
        SQL;

        $query .= \implode(', ', $this->insertionQueries);

        $query .= ';';

        return $query;
    }

    private function buildUpdateQuery(): string
    {
        $query = <<< SQL
            UPDATE pim_catalog_category_translation pcct 
                JOIN( 
        SQL;

        $query .= \implode(' UNION ALL ', $this->updateQueries);

        $query .= <<< SQL
            ) update_data
            ON
                pcct.foreign_key=update_data.foreign_key
                AND pcct.locale=update_data.locale
            SET pcct.label=update_data.label
        SQL;

        return $query;
    }

    /**
     * @param Code $categoryCode
     * @return void
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function fetchExistingTranslationsByCategoryCode(Code $categoryCode): void
    {
        $this->cachedLocales = [];
        $result = $this->connection->executeQuery(
            <<< SQL
                SELECT
                    code,
                    label,
                    locale 
                FROM pim_catalog_category_translation category_translation
                JOIN pim_catalog_category category ON category.id=category_translation.foreign_key
                WHERE 
                    code=:category_code
            SQL,
            [
                'category_code' => (string) $categoryCode,
            ],
            [
                'category_code' => \PDO::PARAM_STR,
            ]
        )->fetchAllAssociative();

        foreach ($result as $data) {
            $this->cachedLocales[$data['locale']] = $data['label'];
        }
    }

    private function getCategoryIdFromCode(Code $code): CategoryId
    {
        $selectQuery = <<< SQL
            SELECT id FROM pim_catalog_category WHERE code=:category_code LIMIT 1
        SQL;

        $categoryData = $this->connection->executeQuery(
            $selectQuery,
            [
                'category_code' => (string) $code,
            ],
            [
                'category_code' => \PDO::PARAM_STR,
            ]
        )->fetchAssociative();

        return new CategoryId((int)$categoryData['id'] ?: null);
    }

    /**
     * @param string $locale
     * @return bool
     */
    private function localeAlreadyExists(string $locale): bool
    {
        return \array_key_exists($locale, $this->cachedLocales);
    }

    /**
     * @param string $locale
     * @param string $label
     * @return bool
     */
    private function labelIsTheSame(string $locale, string $label): bool
    {
        if (\array_key_exists($locale, $this->cachedLocales)) {
            return ($this->cachedLocales[$locale] === $label);
        }
        return false;
    }
}
