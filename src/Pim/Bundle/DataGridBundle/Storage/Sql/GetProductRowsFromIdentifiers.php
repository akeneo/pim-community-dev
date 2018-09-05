<?php

declare(strict_types=1);

namespace Pim\Bundle\DataGridBundle\Storage\Sql;

use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueCollectionFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Pim\Bundle\DataGridBundle\Normalizer\IdEncoder;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductRowsFromIdentifiers implements CursorableRepositoryInterface
{
    /** @var Connection */
    private $connection;

    /** @var ValueCollectionFactoryInterface */
    private $valueCollectionFactory;

    public function __construct(Connection $connection, ValueCollectionFactoryInterface $valueCollectionFactory)
    {
        $this->connection = $connection;
        $this->valueCollectionFactory = $valueCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function getItemsFromIdentifiers(array $identifiers)
    {
        $valueCollections = $this->getValueCollection($identifiers);

        $rows = array_replace_recursive(
            $this->getProperties($identifiers),
            $this->getAttributeAsLabel($identifiers, $valueCollections),
            $this->getAttributeAsImage($identifiers, $valueCollections),
            $this->getCompletenesses($identifiers),
            $this->getFamilyLabels($identifiers),
            $this->getGroups($identifiers),
            $valueCollections
        );

        $platform = $this->connection->getDatabasePlatform();

        $products = [];
        foreach ($rows as $row) {
            $products[] = new ReadModel\Row(
                $row['identifier'],
                $row['family_label'],
                $row['groups'],
                Type::getType(Type::BOOLEAN)->convertToPHPValue($row['is_enabled'], $platform),
                Type::getType(Type::DATETIME)->convertToPhpValue($row['created'], $platform),
                Type::getType(Type::DATETIME)->convertToPhpValue($row['updated'], $platform),
                $row['label'],
                $row['image'],
                $row['completeness'],
                IdEncoder::PRODUCT_TYPE,
                (int) $row['id'],
                IdEncoder::encode(IdEncoder::PRODUCT_TYPE, (int) $row['id']),
                true,
                null,
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

    private function getValueCollection(array $identifiers): array
    {
        // TODO : handle recursivity when level > 2?
        $sql = <<<SQL
            SELECT 
                p.identifier,
                JSON_MERGE(COALESCE(pm1.raw_values, '{}'), COALESCE(pm2.raw_values, '{}'), p.raw_values) as raw_values
            FROM
                pim_catalog_product p
                LEFT JOIN pim_catalog_product_model pm1 ON pm1.id = p.product_model_id
                LEFT JOIN pim_catalog_product_model pm2 on pm2.id = pm1.parent_id
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
            $result[$row['identifier']]['value_collection'] = $this->valueCollectionFactory->createFromStorageFormat(
                json_decode($row['raw_values'], true)
            );
        }

        return $result;
    }

    private function getAttributeAsLabel(array $identifiers, array $valueCollections): array
    {
        // TDOO: pass it in parameters
        $channel = 'ecommerce';
        $locale = 'en_US';

        $result = [];
        foreach ($identifiers as $identifier) {
            $result[$identifier]['label'] = null;
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
                $row['is_scopable'] ? $channel : null,
                $row['is_localizable'] ? $locale : null
            );

            $result[$row['identifier']]['label'] = $label ?? null;
        }

        return $result;
    }

    private function getAttributeAsImage(array $identifiers, array $valueCollections): array
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

    private function getCompletenesses(array $identifiers): array
    {
        // TODO: pass it in parameters
        $channel = 'ecommerce';
        $locale = 'en_US';

        $result = [];
        foreach ($identifiers as $identifier) {
            $result[$identifier]['completeness'] = null;
        }

        $sql = <<<SQL
            SELECT 
                p.identifier,
                c.ratio
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
            ['identifiers' => $identifiers, 'locale_code' => $locale, 'channel_code' => $channel],
            ['identifiers' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        foreach ($rows as $row) {
            $result[$row['identifier']]['completeness'] = (int) $row['ratio'];
        }

        return $result;
    }

    private function getFamilyLabels(array $identifiers): array
    {
        // TODO: pass it in parameters
        $locale = 'en_US';

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
            ['identifiers' => $identifiers, 'locale_code' => $locale],
            ['identifiers' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        foreach ($rows as $row) {
            $result[$row['identifier']]['family_label'] = $row['family_label'];
        }

        return $result;
    }

    private function getGroups(array $identifiers): array
    {
        // TODO: pass it in parameters
        $locale = 'en_US';

        $result = [];
        foreach ($identifiers as $identifier) {
            $result[$identifier]['groups'] = [];
        }

        $sql = <<<SQL
            SELECT 
                p.identifier,
                JSON_ARRAYAGG(COALESCE(ft.label, CONCAT("[", g.code, "]"))) as groups 
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
            ['identifiers' => $identifiers, 'locale_code' => $locale],
            ['identifiers' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        foreach ($rows as $row) {
            $result[$row['identifier']]['groups'] = json_decode($row['groups']);
        }

        return $result;
    }
}
