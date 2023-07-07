<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductGrid;

use Akeneo\Pim\Enrichment\Component\Product\Factory\WriteValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\FetchProductRowsFromUuidsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FetchProductRowsFromUuids implements FetchProductRowsFromUuidsInterface
{
    public function __construct(
        private readonly Connection $connection,
        private readonly WriteValueCollectionFactory $valueCollectionFactory,
        private readonly GetProductCompletenesses $getProductCompletenesses,
    ) {
    }

    /**
     * @param array<string> $uuids
     * @param array<string> $attributeCodes
     * @param string $channelCode
     * @param string $localeCode
     *
     * @return ReadModel\Row[]
     */
    public function __invoke(array $uuids, array $attributeCodes, string $channelCode, string $localeCode): array
    {
        if (empty($uuids)) {
            return [];
        }

        $uuids = array_map(
            fn (string $uuid): UuidInterface =>
                Uuid::fromString(preg_replace('/^product_/', '', $uuid)),
            $uuids
        );

        $valueCollections = $this->getValueCollection($uuids, $attributeCodes, $channelCode, $localeCode);

        $rows = array_replace_recursive(
            $this->getProperties($uuids),
            $this->getLabels($uuids, $valueCollections, $channelCode, $localeCode),
            $this->getImages($uuids, $valueCollections),
            $this->getCompletenesses($uuids, $channelCode, $localeCode),
            $this->getFamilyLabels($uuids, $localeCode),
            $this->getGroups($uuids, $localeCode),
            $valueCollections
        );

        $platform = $this->connection->getDatabasePlatform();

        $products = [];
        foreach ($rows as $row) {
            if (!$this->isExistingProduct($row)) {
                continue;
            }
            $products[] = ReadModel\Row::fromProduct(
                $row['identifier'],
                $row['family_label'],
                $row['groups'],
                Type::getType(Types::BOOLEAN)->convertToPHPValue($row['is_enabled'], $platform),
                Type::getType(Types::DATETIME_MUTABLE)->convertToPhpValue($row['created'], $platform),
                Type::getType(Types::DATETIME_MUTABLE)->convertToPhpValue($row['updated'], $platform),
                $row['label'],
                $row['image'],
                $row['completeness'],
                $row['uuid'],
                $row['product_model_code'],
                $row['value_collection']
            );
        }

        return $products;
    }

    /** @param array<UuidInterface> $uuids */
    private function getProperties(array $uuids): array
    {
        $sql = <<<SQL
            WITH main_identifier AS (
                SELECT id
                FROM pim_catalog_attribute
                WHERE main_identifier = 1
                LIMIT 1
            )
            SELECT 
                BIN_TO_UUID(p.uuid) AS uuid,
                raw_data AS identifier,
                p.family_id,
                p.is_enabled,
                p.created,
                p.updated,
                pm.code as product_model_code
            FROM
                pim_catalog_product p
                LEFT JOIN pim_catalog_product_model pm ON p.product_model_id = pm.id
                LEFT JOIN pim_catalog_product_unique_data pcpud
                    ON pcpud.product_uuid = p.uuid
                    AND pcpud.attribute_id = (SELECT id FROM main_identifier)
            WHERE 
                uuid IN (:uuids)
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            ['uuids' => array_map(fn (UuidInterface $uuid): string => $uuid->getBytes(), $uuids)],
            ['uuids' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['uuid']] = $row;
        }

        return $result;
    }

    /**
     * @param array<UuidInterface> $uuids
     * @param array<string> $attributeCodes
     */
    private function getValueCollection(array $uuids, array $attributeCodes, string $channelCode, string $localeCode): array
    {
        $sql = <<<SQL
            SELECT 
                BIN_TO_UUID(p.uuid) AS uuid,
                a_label.code attribute_as_label_code,
                a_image.code attribute_as_image_code,
                JSON_MERGE(COALESCE(pm1.raw_values, '{}'), COALESCE(pm2.raw_values, '{}'), p.raw_values) as raw_values
            FROM
                pim_catalog_product p
                LEFT JOIN pim_catalog_product_model pm1 ON pm1.id = p.product_model_id
                LEFT JOIN pim_catalog_product_model pm2 on pm2.id = pm1.parent_id
                LEFT JOIN pim_catalog_family f ON f.id = p.family_id
                LEFT JOIN pim_catalog_attribute a_label ON a_label.id = f.label_attribute_id
                LEFT JOIN pim_catalog_attribute a_image ON a_image.id = f.image_attribute_id
            WHERE 
                uuid IN (:uuids)
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            ['uuids' => array_map(fn (UuidInterface $uuid): string => $uuid->getBytes(), $uuids)],
            ['uuids' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        $result = [];
        $products = [];

        foreach ($rows as $row) {
            $values = json_decode($row['raw_values'], true);
            $attributeCodesToKeep = array_filter(
                array_merge(
                    $attributeCodes,
                    [$row['attribute_as_label_code'], $row['attribute_as_image_code']]
                )
            );

            $filteredValues = array_intersect_key($values, array_flip($attributeCodesToKeep));

            $products[$row['uuid']] = $filteredValues;
        }

        $valueCollections = $this->valueCollectionFactory->createMultipleFromStorageFormat($products);

        foreach ($valueCollections as $productUuid => $valueCollection) {
            $result[$productUuid]['value_collection'] = $valueCollection->filter(
                function (ValueInterface $value) use ($channelCode, $localeCode) {
                    return ($value->getScopeCode() === $channelCode || $value->getScopeCode() === null)
                        && ($value->getLocaleCode() === $localeCode || $value->getLocaleCode() === null);
                }
            );
        }

        return $result;
    }

    /** @param array<UuidInterface> $uuids */
    private function getLabels(array $uuids, array $valueCollections, string $channelCode, string $localeCode): array
    {
        $result = [];
        $sql = <<<SQL
            WITH main_identifier AS (
                SELECT id
                FROM pim_catalog_attribute
                WHERE main_identifier = 1
                LIMIT 1
            )
            SELECT 
                BIN_TO_UUID(p.uuid) as uuid,
                pcpud.raw_data as identifier,
                a_label.code as label_code,
                a_label.is_localizable,
                a_label.is_scopable
            FROM
                pim_catalog_product p
                LEFT JOIN pim_catalog_family f ON f.id = p.family_id
                LEFT JOIN pim_catalog_attribute a_label ON a_label.id = f.label_attribute_id
                LEFT JOIN pim_catalog_product_unique_data pcpud
                    ON pcpud.product_uuid = p.uuid
                    AND pcpud.attribute_id = (SELECT id FROM main_identifier)
            WHERE 
                uuid IN (:uuids)
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            ['uuids' => array_map(fn (UuidInterface $uuid): string => $uuid->getBytes(), $uuids)],
            ['uuids' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        foreach ($rows as $row) {
            $labelValue = null;
            if (null !== $row['label_code']) {
                $labelValue = $valueCollections[$row['uuid']]['value_collection']->getByCodes(
                    $row['label_code'],
                    $row['is_scopable'] ? $channelCode : null,
                    $row['is_localizable'] ? $localeCode : null
                );
            }

            if (null !== $labelValue && null !== $labelValue->getData()) {
                $result[$row['uuid']]['label'] = $labelValue->getData();
            } else {
                $result[$row['uuid']]['label'] = sprintf('[%s]', $row['identifier'] ?? $row['uuid']);
            }
        }

        return $result;
    }

    /** @param array<UuidInterface> $uuids */
    private function getImages(array $uuids, array $valueCollections): array
    {
        $result = [];
        foreach ($uuids as $uuid) {
            $result[$uuid->toString()]['image'] = null;
        }

        $sql = <<<SQL
            SELECT 
                BIN_TO_UUID(p.uuid) as uuid,
                a_image.code as image_code
            FROM
                pim_catalog_product p
                JOIN pim_catalog_family f ON f.id = p.family_id
                JOIN pim_catalog_attribute a_image ON a_image.id = f.image_attribute_id
            WHERE 
                uuid IN (:uuids)
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            ['uuids' => array_map(fn (UuidInterface $uuid): string => $uuid->getBytes(), $uuids)],
            ['uuids' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        foreach ($rows as $row) {
            $image = $valueCollections[$row['uuid']]['value_collection']->getByCodes($row['image_code']);
            $result[$row['uuid']]['image'] = $image ?? null;
        }

        return $result;
    }

    /** @param array<UuidInterface> $uuids */
    private function getCompletenesses(array $uuids, string $channelCode, string $localeCode): array
    {
        $completenessCollection = $this->getProductCompletenesses->fromProductUuids($uuids, $channelCode, [$localeCode]);

        $result = [];
        foreach ($uuids as $uuid) {
            $result[$uuid->toString()]['completeness'] = $completenessCollection[$uuid->toString()]?->getCompletenessForChannelAndLocale($channelCode, $localeCode)?->ratio();
        }

        return $result;
    }

    /** @param array<UuidInterface> $uuids */
    private function getFamilyLabels(array $uuids, string $localeCode): array
    {
        $result = [];
        foreach ($uuids as $uuid) {
            $result[$uuid->toString()]['family_label'] = null;
        }

        $sql = <<<SQL
            SELECT 
                BIN_TO_UUID(p.uuid) as uuid,
                COALESCE(ft.label, CONCAT("[", f.code, "]")) as family_label
            FROM
                pim_catalog_product p
                JOIN pim_catalog_family f ON f.id = p.family_id
                LEFT JOIN pim_catalog_family_translation ft ON ft.foreign_key = f.id AND ft.locale = :locale_code
            WHERE 
                uuid IN (:uuids)
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            [
                'uuids' => array_map(fn (UuidInterface $uuid): string => $uuid->getBytes(), $uuids),
                'locale_code' => $localeCode
            ],
            ['uuids' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        foreach ($rows as $row) {
            $result[$row['uuid']]['family_label'] = $row['family_label'];
        }

        return $result;
    }

    /** @param array<UuidInterface> $uuids */
    private function getGroups(array $uuids, string $localeCode): array
    {
        $result = [];
        foreach ($uuids as $uuid) {
            $result[$uuid->toString()]['groups'] = [];
        }

        $sql = <<<SQL
            SELECT 
                BIN_TO_UUID(p.uuid) as uuid,
                JSON_ARRAYAGG(COALESCE(ft.label, CONCAT("[", g.code, "]"))) AS product_groups 
            FROM
                pim_catalog_product p
                JOIN pim_catalog_group_product gp ON gp.product_uuid = p.uuid
                JOIN pim_catalog_group g ON g.id = gp.group_id
                LEFT JOIN pim_catalog_group_translation ft ON ft.foreign_key = g.id AND ft.locale = :locale_code
            WHERE 
                uuid IN (:uuids)
            GROUP BY
                uuid
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            [
                'uuids' => array_map(fn (UuidInterface $uuid): string => $uuid->getBytes(), $uuids),
                'locale_code' => $localeCode
            ],
            ['uuids' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        foreach ($rows as $row) {
            $result[$row['uuid']]['groups'] = json_decode($row['product_groups']);
        }

        return $result;
    }

    /**
     * A product can exist in Elasticsearch but not in Mysql.
     *
     * It occurs, for example, when deleting a product in the datagrid.
     * In that case, the product is deleted in Mysql (which trigger deletion in ES) and then the datagrid is refreshed.
     *
     * The problem is that the refresh of the datagrid still search in ES the products and the product models.
     * The deleted product is still in the ES index because the index is not yet up to date with the deleted product.
     * Therefore, the code of this deleted product is returned but it does not exist anymore in Mysql.
     *
     */
    private function isExistingProduct(array $row): bool
    {
        return isset($row['uuid']);
    }
}
