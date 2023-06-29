<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductGrid;

use Akeneo\Pim\Enrichment\Component\Product\Factory\WriteValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FetchProductModelRowsFromCodes
{
    public function __construct(
        private readonly Connection $connection,
        private readonly WriteValueCollectionFactory $valueCollectionFactory,
        private readonly ProductModelImagesFromCodes $productModelImagesFromCodes
    ) {
    }

    /**
     * @param array  $codes
     * @param array  $attributeCodes
     * @param string $channelCode
     * @param string $localeCode
     *
     * @return ReadModel\Row[]
     */
    public function __invoke(array $codes, array $attributeCodes, string $channelCode, string $localeCode): array
    {
        if (empty($codes)) {
            return [];
        }

        $valueCollections = $this->getValueCollection($codes, $attributeCodes, $channelCode, $localeCode);

        $rows = array_replace_recursive(
            $this->getProperties($codes),
            $this->getLabels($codes, $valueCollections, $channelCode, $localeCode),
            ($this->productModelImagesFromCodes)($codes, $channelCode, $localeCode),
            $this->getChildrenCompletenesses($codes, $channelCode, $localeCode),
            $this->getFamilyLabels($codes, $localeCode),
            $valueCollections
        );

        $platform = $this->connection->getDatabasePlatform();

        $productModels = [];
        foreach ($rows as $row) {
            if (!$this->isExistingProductModel($row)) {
                continue;
            }
            $productModels[] = ReadModel\Row::fromProductModel(
                $row['code'],
                $row['family_label'],
                Type::getType(Types::DATETIME_MUTABLE)->convertToPhpValue($row['created'], $platform),
                Type::getType(Types::DATETIME_MUTABLE)->convertToPhpValue($row['updated'], $platform),
                $row['label'],
                $row['image'],
                (int) $row['id'],
                $row['children_completeness'],
                $row['parent_code'],
                $row['value_collection']
            );
        }

        return $productModels;
    }

    private function getProperties(array $codes): array
    {
        $sql = <<<SQL
            SELECT 
                pm.id,
                pm.code,
                pm.created,
                pm.updated,
                parent.code as parent_code
            FROM
                pim_catalog_product_model pm
                LEFT JOIN pim_catalog_product_model parent ON parent.id = pm.parent_id
            WHERE 
                pm.code IN (:codes)
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            ['codes' => $codes],
            ['codes' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['code']] = $row;
        }

        return $result;
    }

    private function getLabels(array $codes, array $valueCollections, string $channelCode, string $localeCode): array
    {
        $result = [];
        foreach ($codes as $code) {
            $result[$code]['label'] = sprintf('[%s]', $code);
        }

        $sql = <<<SQL
            SELECT 
                pm.code,
                a_label.code as label_code,
                a_label.is_localizable,
                a_label.is_scopable
            FROM
                pim_catalog_product_model pm
                JOIN pim_catalog_family_variant fv ON fv.id = pm.family_variant_id
                JOIN pim_catalog_family f ON f.id = fv.family_id
                JOIN pim_catalog_attribute a_label ON a_label.id = f.label_attribute_id
            WHERE 
                pm.code IN (:codes)
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            ['codes' => $codes],
            ['codes' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        foreach ($rows as $row) {
            $label = $valueCollections[$row['code']]['value_collection']->getByCodes(
                $row['label_code'],
                $row['is_scopable'] ? $channelCode : null,
                $row['is_localizable'] ? $localeCode : null
            );

            if (null !== $label && null !== $label->getData()) {
                $result[$row['code']]['label'] = $label->getData();
            }
        }

        return $result;
    }

    private function getChildrenCompletenesses(array $codes, string $channelCode, string $localeCode): array
    {
        $result = [];
        foreach ($codes as $code) {
            $result[$code]['children_completeness'] = [
                'total'    => 0,
                'complete' => 0,
            ];
        }

        $completenessByProductModelCode = $this->getCompletenessesFor($codes);

        foreach ($completenessByProductModelCode as $value) {
            $code = $value['code'];
            $completeness = \json_decode($value['completeness'], true);

            $result[$code]['children_completeness']['total'] += 1;

            if (0 === ($completeness[$channelCode][$localeCode]['missing'] ?? null)) {
                $result[$code]['children_completeness']['complete'] += 1;
            }
        }

        return $result;
    }

    private function getCompletenessesFor(array $productModelCodes): array
    {
        $sql = <<<SQL
WITH descendant_product_uuids as ( 
    SELECT code, product.uuid
    FROM pim_catalog_product product
        INNER JOIN pim_catalog_product_model product_model ON product_model.id = product.product_model_id
    WHERE product_model.code IN (:codes)
    UNION ALL
    SELECT root_product_model.code, product.uuid
    FROM pim_catalog_product product
        INNER JOIN pim_catalog_product_model sub_product_model ON sub_product_model.id = product.product_model_id
        INNER JOIN pim_catalog_product_model root_product_model ON root_product_model.id = sub_product_model.parent_id
    WHERE root_product_model.code IN (:codes)
)          
    SELECT descendant_product_uuids.code, completeness
    FROM pim_catalog_product_completeness completeness
        JOIN descendant_product_uuids ON descendant_product_uuids.uuid = completeness.product_uuid
SQL;

        return $this->connection->fetchAllAssociative(
            $sql,
            [
                'codes' => $productModelCodes,
            ],
            [
                'codes' => Connection::PARAM_STR_ARRAY,
            ]
        );
    }

    private function getFamilyLabels(array $codes, string $localeCode): array
    {
        $result = [];
        foreach ($codes as $code) {
            $result[$code]['family_label'] = null;
        }

        $sql = <<<SQL
            SELECT 
                pm.code,
                COALESCE(ft.label, CONCAT("[", f.code, "]")) as family_label
            FROM
                pim_catalog_product_model pm
                JOIN pim_catalog_family_variant fv ON fv.id = pm.family_variant_id
                JOIN pim_catalog_family f ON f.id = fv.family_id
                LEFT JOIN pim_catalog_family_translation ft ON ft.foreign_key = f.id AND ft.locale = :locale_code
            WHERE 
                pm.code IN (:codes)
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            ['codes' => $codes, 'locale_code' => $localeCode],
            ['codes' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        foreach ($rows as $row) {
            $result[$row['code']]['family_label'] = $row['family_label'];
        }

        return $result;
    }

    private function getValueCollection(array $codes, array $attributeCodes, string $channelCode, string $localeCode): array
    {
        $sql = <<<SQL
            SELECT 
                pm.code,
                a_label.code attribute_as_label_code,
                JSON_MERGE(COALESCE(parent.raw_values, '{}'), pm.raw_values) as raw_values
            FROM
                pim_catalog_product_model pm
                JOIN pim_catalog_family_variant fv ON fv.id = pm.family_variant_id
                JOIN pim_catalog_family f ON f.id = fv.family_id
                LEFT JOIN pim_catalog_attribute a_label ON a_label.id = f.label_attribute_id
                LEFT JOIN pim_catalog_product_model parent on parent.id = pm.parent_id
            WHERE 
                pm.code IN (:codes)
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            ['codes' => $codes],
            ['codes' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        $result = [];
        $productModels = [];

        foreach ($rows as $row) {
            $values = json_decode($row['raw_values'], true);
            // filter attributes directly on raw_values for performance reason
            $attributeCodesToKeep = array_filter(
                array_merge(
                    $attributeCodes,
                    [$row['attribute_as_label_code']]
                )
            );

            $filteredValues = array_intersect_key($values, array_flip($attributeCodesToKeep));
            $productModels[$row['code']] = $filteredValues;
        }

        $valueCollections = $this->valueCollectionFactory->createMultipleFromStorageFormat($productModels);

        foreach ($valueCollections as $productModelCode => $valueCollection) {
            $result[$productModelCode]['value_collection'] = $valueCollection->filter(
                function (ValueInterface $value) use ($channelCode, $localeCode) {
                    return ($value->getScopeCode() === $channelCode || $value->getScopeCode() === null)
                        && ($value->getLocaleCode() === $localeCode || $value->getLocaleCode() === null);
                }
            );
        }

        return $result;
    }

    /**
     * A product model can exist in Elasticsearch but not in Mysql.
     *
     * It occurs, for example, when deleting a product model in the datagrid.
     * In that case, the product model is deleted in Mysql (which trigger deletion in ES) and then the datagrid is refreshed.
     *
     * The problem is that the refresh of the datagrid still search in ES the products and the product models.
     * The deleted product model is still in the ES index because the index is not yet up to date with the deleted product model.
     * Therefore, the code of this deleted product model is returned but it does not exist anymore in Mysql.
     *
     */
    private function isExistingProductModel(array $row): bool
    {
        return isset($row['code']);
    }
}
