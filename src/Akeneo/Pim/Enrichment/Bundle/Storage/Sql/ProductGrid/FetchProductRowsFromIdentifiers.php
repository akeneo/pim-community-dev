<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductGrid;

use Akeneo\Pim\Enrichment\Component\Product\Factory\WriteValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FetchProductRowsFromIdentifiers
{
    /** @var Connection */
    private $connection;

    /** @var WriteValueCollectionFactory */
    private $valueCollectionFactory;

    /**
     * @param Connection                      $connection
     * @param WriteValueCollectionFactory $valueCollectionFactory
     */
    public function __construct(Connection $connection, WriteValueCollectionFactory $valueCollectionFactory)
    {
        $this->connection = $connection;
        $this->valueCollectionFactory = $valueCollectionFactory;
    }

    /**
     * @param array  $identifiers
     * @param array  $attributeCodes
     * @param string $channelCode
     * @param string $localeCode
     *
     * @return ReadModel\Row[]
     */
    public function __invoke(array $identifiers, array $attributeCodes, string $channelCode, string $localeCode): array
    {
        if (empty($identifiers)) {
            return [];
        }

        $valueCollections = $this->getValueCollection($identifiers, $attributeCodes, $channelCode, $localeCode);

        $rows = array_replace_recursive(
            $this->getProperties($identifiers),
            $this->getLabels($identifiers, $valueCollections, $channelCode, $localeCode),
            $this->getImages($identifiers, $valueCollections),
            $this->getCompletenesses($identifiers, $channelCode, $localeCode),
            $this->getFamilyLabels($identifiers, $localeCode),
            $this->getGroups($identifiers, $localeCode),
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
                Type::getType(Type::BOOLEAN)->convertToPHPValue($row['is_enabled'], $platform),
                Type::getType(Type::DATETIME)->convertToPhpValue($row['created'], $platform),
                Type::getType(Type::DATETIME)->convertToPhpValue($row['updated'], $platform),
                $row['label'],
                $row['image'],
                $row['completeness'],
                (int) $row['id'],
                $row['product_model_code'],
                $row['value_collection']
            );
        }

        return $products;
    }

    private function getProperties(array $identifiers): array
    {
        $sql = <<<SQL
            SELECT 
                p.id,
                p.identifier,
                p.family_id,
                p.is_enabled,
                p.created,
                p.updated,
                pm.code as product_model_code
            FROM
                pim_catalog_product p
                LEFT JOIN pim_catalog_product_model pm ON p.product_model_id = pm.id 
            WHERE 
                identifier IN (:identifiers)
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            ['identifiers' => $identifiers],
            ['identifiers' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['identifier']] = $row;
        }

        return $result;
    }

    private function getValueCollection(array $identifiers, array $attributeCodes, string $channelCode, string $localeCode): array
    {
        $sql = <<<SQL
            SELECT 
                p.identifier,
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
                identifier IN (:identifiers)
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            ['identifiers' => $identifiers],
            ['identifiers' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAll();

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

            $products[$row['identifier']] = $filteredValues;
        }

        $valueCollections = $this->valueCollectionFactory->createMultipleFromStorageFormat($products);

        foreach ($valueCollections as $productIdentifier => $valueCollection) {
            $result[$productIdentifier]['value_collection'] = $valueCollection->filter(
                function (ValueInterface $value) use ($channelCode, $localeCode) {
                    return ($value->getScopeCode() === $channelCode || $value->getScopeCode() === null)
                        && ($value->getLocaleCode() === $localeCode || $value->getLocaleCode() === null);
                }
            );
        }

        return $result;
    }

    private function getLabels(array $identifiers, array $valueCollections, string $channelCode, string $localeCode): array
    {
        $result = [];
        foreach ($identifiers as $identifier) {
            $result[$identifier]['label'] = sprintf('[%s]', $identifier);
        }

        $sql = <<<SQL
            SELECT 
                p.identifier,
                a_label.code as label_code,
                a_label.is_localizable,
                a_label.is_scopable
            FROM
                pim_catalog_product p
                JOIN pim_catalog_family f ON f.id = p.family_id
                JOIN pim_catalog_attribute a_label ON a_label.id = f.label_attribute_id
            WHERE 
                identifier IN (:identifiers)
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            ['identifiers' => $identifiers],
            ['identifiers' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        foreach ($rows as $row) {
            $label = $valueCollections[$row['identifier']]['value_collection']->getByCodes(
                $row['label_code'],
                $row['is_scopable'] ? $channelCode : null,
                $row['is_localizable'] ? $localeCode : null
            );

            if (null !== $label && null !== $label->getData()) {
                $result[$row['identifier']]['label'] = $label->getData();
            }
        }

        return $result;
    }

    private function getImages(array $identifiers, array $valueCollections): array
    {
        $result = [];
        foreach ($identifiers as $identifier) {
            $result[$identifier]['image'] = null;
        }

        $sql = <<<SQL
            SELECT 
                p.identifier,
                a_image.code as image_code
            FROM
                pim_catalog_product p
                JOIN pim_catalog_family f ON f.id = p.family_id
                JOIN pim_catalog_attribute a_image ON a_image.id = f.image_attribute_id
            WHERE 
                identifier IN (:identifiers)
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            ['identifiers' => $identifiers],
            ['identifiers' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        foreach ($rows as $row) {
            $image = $valueCollections[$row['identifier']]['value_collection']->getByCodes($row['image_code']);
            $result[$row['identifier']]['image'] = $image ?? null;
        }

        return $result;
    }

    private function getCompletenesses(array $identifiers, string $channelCode, string $localeCode): array
    {
        $result = [];
        foreach ($identifiers as $identifier) {
            $result[$identifier]['completeness'] = null;
        }

        $sql = <<<SQL
            SELECT 
                p.identifier,
                FLOOR(100 * (c.required_count - c.missing_count) / c.required_count) AS ratio
            FROM
                pim_catalog_product p
                JOIN pim_catalog_completeness c ON c.product_id = p.id
                JOIN pim_catalog_locale l ON l.id = c.locale_id
                JOIN pim_catalog_channel ch ON ch.id = c.channel_id
            WHERE 
                identifier IN (:identifiers)
                AND l.code = :locale_code
                AND ch.code = :channel_code
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            ['identifiers' => $identifiers, 'locale_code' => $localeCode, 'channel_code' => $channelCode],
            ['identifiers' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        foreach ($rows as $row) {
            $result[$row['identifier']]['completeness'] = (int) $row['ratio'];
        }

        return $result;
    }

    private function getFamilyLabels(array $identifiers, string $localeCode): array
    {
        $result = [];
        foreach ($identifiers as $identifier) {
            $result[$identifier]['family_label'] = null;
        }

        $sql = <<<SQL
            SELECT 
                p.identifier,
                COALESCE(ft.label, CONCAT("[", f.code, "]")) as family_label
            FROM
                pim_catalog_product p
                JOIN pim_catalog_family f ON f.id = p.family_id
                LEFT JOIN pim_catalog_family_translation ft ON ft.foreign_key = f.id AND ft.locale = :locale_code
            WHERE 
                identifier IN (:identifiers)
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            ['identifiers' => $identifiers, 'locale_code' => $localeCode],
            ['identifiers' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        foreach ($rows as $row) {
            $result[$row['identifier']]['family_label'] = $row['family_label'];
        }

        return $result;
    }

    private function getGroups(array $identifiers, string $localeCode): array
    {
        $result = [];
        foreach ($identifiers as $identifier) {
            $result[$identifier]['groups'] = [];
        }

        $sql = <<<SQL
            SELECT 
                p.identifier,
                JSON_ARRAYAGG(COALESCE(ft.label, CONCAT("[", g.code, "]"))) AS product_groups 
            FROM
                pim_catalog_product p
                JOIN pim_catalog_group_product gp ON gp.product_id = p.id
                JOIN pim_catalog_group g ON g.id = gp.group_id
                LEFT JOIN pim_catalog_group_translation ft ON ft.foreign_key = g.id AND ft.locale = :locale_code
            WHERE 
                identifier IN (:identifiers)
            GROUP BY
                p.identifier
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            ['identifiers' => $identifiers, 'locale_code' => $localeCode],
            ['identifiers' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        foreach ($rows as $row) {
            $result[$row['identifier']]['groups'] = json_decode($row['product_groups']);
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
        return isset($row['identifier']);
    }
}
