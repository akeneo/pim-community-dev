<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog\Product;

use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetCategoryLabelsForALocaleQueryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCategoryLabelsForALocaleQuery implements GetCategoryLabelsForALocaleQueryInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function execute(array $categoryCodes, string $locale): array
    {
        $query = <<<SQL
            SELECT
                category.code category_code,
                IF(
                    ISNULL(translation.label) OR translation.label LIKE '',
                    CONCAT('[', category.code, ']'),
                    translation.label
                ) category_label
            FROM pim_catalog_category category
            LEFT JOIN pim_catalog_category_translation translation on category.id = translation.foreign_key
            WHERE category.code IN (:category_codes) AND translation.locale = :locale
        SQL;

        /** @var array<array-key, string[]> */
        $results = $this->connection->executeQuery(
            $query,
            [
                'category_codes' => $categoryCodes,
                'locale' => $locale,
            ],
            [
                'category_codes' => Connection::PARAM_STR_ARRAY,
            ],
        )->fetchAllAssociative();
        return $results;
    }
}
