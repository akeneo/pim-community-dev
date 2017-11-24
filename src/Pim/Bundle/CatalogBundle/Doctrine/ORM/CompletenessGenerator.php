<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Pim\Component\Catalog\Completeness\CompletenessGeneratorInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Generate the completeness when Product are in ORM
 * storage
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessGenerator implements CompletenessGeneratorInterface
{
    /** @staticvar string */
    const COMPLETE_PRICES_TABLE = 'complete_price';

    /** @staticvar string */
    const MISSING_TABLE = 'missing_completeness';

    /** @var Connection */
    protected $connection;

    /** @var EntityManagerInterface */
    protected $manager;

    /** @var string */
    protected $productClass;

    /** @var string */
    protected $productValueClass;

    /** @var string */
    protected $attributeClass;

    /** @var int */
    protected $commitBatchSize;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $manager
     * @param string                 $productClass
     * @param string                 $productValueClass
     * @param string                 $attributeClass
     * @param int                    $commitBatchSize
     */
    public function __construct(
        EntityManagerInterface $manager,
        $productClass,
        $productValueClass,
        $attributeClass,
        $commitBatchSize = 1000
    ) {
        $this->manager = $manager;
        $this->connection = $manager->getConnection();
        $this->productClass = $productClass;
        $this->productValueClass = $productValueClass;
        $this->attributeClass = $attributeClass;
        $this->commitBatchSize = (int)$commitBatchSize;
    }

    /**
     * {@inheritdoc}
     */
    public function generateMissingForProduct(ProductInterface $product)
    {
        $this->generate(['productId' => $product->getId()]);
    }

    /**
     * {@inheritdoc}
     */
    public function generateMissingForChannel(ChannelInterface $channel)
    {
        $this->generate(['channelId' => $channel->getId()]);
    }

    /**
     * {@inheritdoc}
     */
    public function generateMissing()
    {
        $this->generate();
    }

    /**
     * Generate completeness for product where it's missing,
     * applying the criteria if provided to reduce the product set.
     *
     * If the completeness is being calculated for only one product, we
     * do not create the missing_completeness and complete_price temporary tables.
     * This allows to drastically reduce the IO waits on non SSD disks.
     *
     * For completeness recalculation of channels or families, we keep
     * the temporary table to reduce the amount of memory used.
     *
     * @param array $criteria
     */
    protected function generate(array $criteria = [])
    {
        if (!isset($criteria['productId'])) {
            $this->prepareCompletePrices($criteria);
            $this->prepareMissingCompletenesses($criteria);
        }

        $this->insertCompleteness($criteria);
    }

    /**
     * Generate a temporary table that will contains
     * a line for each required and complete prices.
     *
     * This temporary table allows to define indexes
     * that speed up the linking with this table.
     *
     * @param array $criteria
     */
    protected function prepareCompletePrices($criteria = [])
    {
        $cleanupSql = 'DROP TABLE IF EXISTS ' . self::COMPLETE_PRICES_TABLE . PHP_EOL;
        $cleanupStmt = $this->connection->prepare($cleanupSql);
        $cleanupStmt->execute();

        $tempTableName = self::COMPLETE_PRICES_TABLE;
        $createSQL = <<<SQL
    CREATE TEMPORARY TABLE {$tempTableName}
    (locale_id int, channel_id int, value_id int, primary key(locale_id, channel_id, value_id))
SQL;

        $createSQL = $this->applyTableNames($createSQL);
        $tempTableStmt = $this->connection->prepare($createSQL);
        $tempTableStmt->execute();

        $sql = $this->getCompletePricesSQL();
        $sql = $this->applyCriteria($sql, $criteria);
        $sql = $this->applyTableNames($sql);

        $fetchStmt = $this->connection->prepare($sql);
        foreach ($criteria as $placeholder => $value) {
            $fetchStmt->bindValue($placeholder, $value);
        }
        $fetchStmt->execute();

        $insertSql = <<<SQL
    INSERT INTO {$tempTableName} (locale_id, channel_id, value_id) 
    VALUES (:locale_id, :channel_id, :value_id)
SQL;
        $insertStmt = $this->connection->prepare($insertSql);
        $count = 0;

        try {
            while ($completeness = $fetchStmt->fetch()) {
                if ($count === 0) {
                    $this->connection->beginTransaction();
                }

                $insertStmt->bindValue('locale_id', $completeness['locale_id']);
                $insertStmt->bindValue('channel_id', $completeness['channel_id']);
                $insertStmt->bindValue('value_id', $completeness['value_id']);
                $insertStmt->execute();
                $count++;

                if ($count === $this->commitBatchSize) {
                    $this->connection->commit();
                    $count = 0;
                }
            }

            if ($count > 0) {
                $this->connection->commit();
            }
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    /**
     * Generate a temporary table that will contains
     * the list of completeness that needs to be regenerated
     *
     * @param array $criteria
     */
    protected function prepareMissingCompletenesses(array $criteria = [])
    {
        $cleanupSql = 'DROP TABLE IF EXISTS ' . self::MISSING_TABLE . PHP_EOL;
        $cleanupStmt = $this->connection->prepare($cleanupSql);
        $cleanupStmt->execute();

        $tempTableName = self::MISSING_TABLE;
        $createSql = <<<SQL
    CREATE TEMPORARY TABLE {$tempTableName} (locale_id int, channel_id int, product_id int)
SQL;

        $createSql = $this->applyTableNames($createSql);
        $tempTableStmt = $this->connection->prepare($createSql);
        $tempTableStmt->execute();

        $sql = $this->getMissingCompletenessesSQL();
        $sql = $this->applyCriteria($sql, $criteria);
        $sql = $this->applyTableNames($sql);

        $fetchStmt = $this->connection->prepare($sql);
        foreach ($criteria as $placeholder => $value) {
            $fetchStmt->bindValue($placeholder, $value);
        }
        $fetchStmt->execute();

        $insertSql = <<<SQL
    INSERT INTO {$tempTableName} (locale_id, channel_id, product_id) 
    VALUES (:locale_id, :channel_id, :product_id)
SQL;
        $insertStmt = $this->connection->prepare($insertSql);
        $count = 0;

        try {
            while ($completeness = $fetchStmt->fetch()) {
                if ($count === 0) {
                    $this->connection->beginTransaction();
                }

                $insertStmt->bindValue('locale_id', $completeness['locale_id']);
                $insertStmt->bindValue('channel_id', $completeness['channel_id']);
                $insertStmt->bindValue('product_id', $completeness['product_id']);
                $insertStmt->execute();
                $count++;

                if ($count === $this->commitBatchSize) {
                    $this->connection->commit();
                    $count = 0;
                }
            }

            if ($count > 0) {
                $this->connection->commit();
            }
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    /**
     * Provides the SQL that allows to aggregate multiple price-currency
     * tuple in one line only if all the tuples for one product value are complete.
     *
     * The price is complete when all  for
     * all currency are present for the channel and locale.
     *
     * This allow to link with only complete prices
     *
     * @return string
     */
    protected function getCompletePricesSQL()
    {
        return <<<COMPLETE_PRICES_SQL
            (SELECT l.id AS locale_id, c.id AS channel_id, v.id AS value_id
                FROM pim_catalog_attribute_requirement r
                JOIN %attribute_table% att ON att.id = r.attribute_id AND att.backend_type = "prices"
                JOIN pim_catalog_channel c ON c.id = r.channel_id %channel_conditions%
                JOIN pim_catalog_channel_locale cl ON cl.channel_id = c.id
                JOIN pim_catalog_locale l ON l.id = cl.locale_id
                JOIN pim_catalog_channel_currency ccur ON ccur.channel_id = c.id
                JOIN pim_catalog_currency cur ON cur.id = ccur.currency_id
                JOIN %product_table% p ON p.family_id = r.family_id %product_conditions%
                JOIN %product_value_table% v
                    ON (v.scope_code = c.code OR v.scope_code IS NULL)
                    AND (v.locale_code = l.code OR v.locale_code IS NULL)
                    AND v.attribute_id = att.id
                    AND v.entity_id = p.id
                LEFT JOIN pim_catalog_product_value_price price
                    ON price.value_id = v.id
                    AND price.currency_code = cur.code
                GROUP BY l.id, c.id, v.id
                HAVING COUNT(price.data) = COUNT(ccur.currency_id))
COMPLETE_PRICES_SQL;
    }

    /**
     * Provides the SQL that generate the list of
     * missing completeness from the existing completeness
     * and the expected completeness.
     * Note that we use a subquery to get only family in order to boost
     * the process comparing to joining with all attributes from the requirements
     * table
     *
     * @return string
     */
    protected function getMissingCompletenessesSQL()
    {
        return <<<MISSING_SQL
            (SELECT l.id AS locale_id, c.id AS channel_id, p.id AS product_id
            FROM
                (SELECT c.id, r.family_id
                FROM pim_catalog_attribute_requirement r
                JOIN pim_catalog_channel c ON c.id = r.channel_id %channel_conditions%
                GROUP BY c.id, r.family_id) AS c
            JOIN pim_catalog_channel_locale cl ON cl.channel_id = c.id
            JOIN pim_catalog_locale l ON l.id = cl.locale_id
            JOIN %product_table% p ON p.family_id = c.family_id %product_conditions%
            LEFT JOIN pim_catalog_completeness co
                ON co.product_id = p.id
                AND co.channel_id = c.id
                AND co.locale_id = l.id
            WHERE co.id IS NULL)
MISSING_SQL;
    }

    /**
     * Apply criteria to the provided SQL.
     *
     * @param string $sql
     * @param string $criteria
     *
     * @return string $sqlWithCriteria
     */
    protected function applyCriteria($sql, $criteria)
    {
        $productConditions = '';
        $channelConditions = '';

        if (array_key_exists('productId', $criteria)) {
            $productConditions = 'AND p.id = :productId';
        }

        if (array_key_exists('channelId', $criteria)) {
            $channelConditions = 'AND c.id = :channelId';
        }

        $sql = str_replace('%product_conditions%', $productConditions, $sql);
        $sql = str_replace('%channel_conditions%', $channelConditions, $sql);

        return $sql;
    }

    /**
     * Insert completeness in DB
     *
     * @param array $criteria
     */
    protected function insertCompleteness(array $criteria)
    {
        $sql = $this->getMainSqlPart();

        $sql = strtr($sql, $this->getQueryPartReplacements($criteria));
        $sql = $this->applyCriteria($sql, $criteria);
        $sql = $this->applyTableNames($sql);

        $fetchStmt = $this->connection->prepare($sql);
        foreach ($criteria as $placeholder => $value) {
            $fetchStmt->bindValue($placeholder, $value);
        }
        $fetchStmt->execute();

        $insertSql = <<<SQL
    REPLACE pim_catalog_completeness (locale_id, channel_id, product_id, ratio, missing_count, required_count) 
    VALUES (:locale_id, :channel_id, :product_id, :ratio, :missing_count, :required_count)
SQL;
        $insertStmt = $this->connection->prepare($insertSql);
        $count = 0;

        try {
            while ($completeness = $fetchStmt->fetch()) {
                if ($count === 0) {
                    $this->connection->beginTransaction();
                }

                $insertStmt->bindValue('locale_id', $completeness['locale_id']);
                $insertStmt->bindValue('channel_id', $completeness['channel_id']);
                $insertStmt->bindValue('product_id', $completeness['product_id']);
                $insertStmt->bindValue('ratio', $completeness['ratio']);
                $insertStmt->bindValue('missing_count', $completeness['missing_count']);
                $insertStmt->bindValue('required_count', $completeness['required_count']);
                $insertStmt->execute();
                $count++;

                if ($count === $this->commitBatchSize) {
                    $this->connection->commit();
                    $count = 0;
                }
            }

            if ($count > 0) {
                $this->connection->commit();
            }
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    /**
     * Provides the main SQL part
     *
     * @return string
     */
    protected function getMainSqlPart()
    {
        return <<<MAIN_SQL
    SELECT
        locale_id,
        channel_id,
        product_id,
        (req_values_filled / required_count * 100) as ratio,
        (required_count - req_values_filled) as missing_count,
        required_count
    FROM (
        SELECT
            values_filled.locale_id,
            values_filled.channel_id,
            values_filled.product_id,
            values_filled.req_values_filled,
            (
                SELECT COUNT(*)
                    FROM pim_catalog_attribute_requirement r
                    LEFT JOIN pim_catalog_attribute_locale al ON al.attribute_id = r.attribute_id
                    WHERE r.family_id = values_filled.family_id
                        AND r.channel_id = values_filled.channel_id
                        AND r.required = TRUE
                        AND (al.locale_id = values_filled.locale_id OR al.locale_id IS NULL)
            ) AS required_count
        FROM (
            SELECT
                l.id AS locale_id,
                c.id AS channel_id,
                p.id AS product_id,
                p.family_id AS family_id,
                COUNT(DISTINCT v.id) AS req_values_filled
            FROM %missing_completeness% AS m
            JOIN pim_catalog_channel c ON c.id = m.channel_id
            JOIN pim_catalog_locale l ON l.id = m.locale_id
            JOIN %product_table% p ON p.id = m.product_id
            JOIN pim_catalog_attribute_requirement r ON r.family_id = p.family_id AND r.channel_id = c.id
            JOIN %product_value_table% v ON v.attribute_id = r.attribute_id
                AND (v.scope_code = c.code OR v.scope_code IS NULL)
                AND (v.locale_code = l.code OR v.locale_code IS NULL)
                AND v.entity_id = p.id
            %product_value_joins%
            %extra_joins%
            LEFT JOIN %complete_price% AS cp
                ON cp.value_id = v.id
                AND cp.channel_id = c.id
                AND cp.locale_id = l.id
            LEFT JOIN pim_catalog_attribute_locale al ON al.attribute_id = v.attribute_id
            WHERE (
                %product_value_conditions%
                %extra_conditions%
                OR cp.value_id IS NOT NULL
            )
            AND (al.locale_id = l.id OR al.locale_id IS NULL)
            AND r.required = true
            GROUP BY p.id, c.id, l.id
        ) AS values_filled
    ) AS results
MAIN_SQL;
    }

    /**
     * Returns an array of replacements for some part of the query
     * Essentially joins. If the completeness is being calculated for only one product, we
     * do not create the missing_completeness temporary table.
     *
     * @param array $criteria
     *
     * @return array
     */
    protected function getQueryPartReplacements(array $criteria)
    {
        return [
            '%product_value_conditions%' => implode(' OR ', $this->getProductValueConditions()),
            '%product_value_joins%'      => implode(' ', $this->getProductValueJoins()),
            '%extra_joins%'              => implode(' ', $this->getExtraJoins($criteria)),
            '%extra_conditions%'         => implode(' ', $this->getExtraConditions($criteria)),
            '%missing_completeness%'     => isset($criteria['productId']) ?
                    $this->getMissingCompletenessesSQL() :
                    self::MISSING_TABLE,
            '%complete_price%'           => isset($criteria['productId']) ?
                    $this->getCompletePricesSQL() :
                    self::COMPLETE_PRICES_TABLE,
        ];
    }

    /**
     * Replace tables placeholders by their real name in the DB
     *
     * @param string $sql
     *
     * @return array
     */
    protected function applyTableNames($sql)
    {
        $categoryMapping = $this->getClassMetadata($this->productClass)->getAssociationMapping('categories');
        $categoryMetadata = $this->getClassMetadata($categoryMapping['targetEntity']);

        $valueMapping = $this->getClassMetadata($this->productClass)->getAssociationMapping('values');
        $valueMetadata = $this->getClassMetadata($valueMapping['targetEntity']);

        $attributeMapping = $valueMetadata->getAssociationMapping('attribute');
        $attributeMetadata = $this->getClassMetadata($attributeMapping['targetEntity']);

        return strtr(
            $sql,
            [
                '%category_table%'      => $categoryMetadata->getTableName(),
                '%category_join_table%' => $categoryMapping['joinTable']['name'],
                '%product_table%'       => $this->getClassMetadata($this->productClass)->getTableName(),
                '%product_value_table%' => $valueMetadata->getTableName(),
                '%attribute_table%'     => $attributeMetadata->getTableName()
            ]
        );
    }

    /**
     * Returns an array of SQL conditions for the ProductValue entity
     *
     * @return array
     */
    protected function getProductValueConditions()
    {
        $associationMappings = $this->getClassMetadata($this->productValueClass)->getAssociationMappings();

        // extract aliased foreign keys from associated fields
        $productForeignKeys = $this->getForeignKeysFromMappings($associationMappings);

        $notNullFields = array_merge($this->getClassContentFields($this->productValueClass, 'v'), $productForeignKeys);

        return array_map(
            function ($field) {
                return sprintf('%s IS NOT NULL', $field);
            },
            $notNullFields
        );
    }

    /**
     * @param array $mappings
     *
     * @return string[]
     */
    protected function getForeignKeysFromMappings($mappings)
    {
        $index = 0;

        $productForeignKeys = array_reduce(
            $mappings,
            function ($fields, $mapping) use (&$index) {
                $index++;

                return array_merge(
                    $fields,
                    $this->getAssociationFields($mapping, $this->getAssociationAlias($index))
                );
            },
            []
        );

        return $productForeignKeys;
    }

    /**
     * Returns the fields for an association
     *
     * @param array  $mapping
     * @param string $prefix
     *
     * @return array
     */
    protected function getAssociationFields($mapping, $prefix)
    {
        if (in_array($mapping['fieldName'], $this->getSkippedMappings())) {
            return [];
        }

        switch ($mapping['type']) {
            case ClassMetadataInfo::MANY_TO_MANY:
                return [
                    sprintf(
                        '%s.%s',
                        $prefix,
                        $mapping['joinTable']['inverseJoinColumns'][0]['name']
                    )
                ];

            case ClassMetadataInfo::MANY_TO_ONE:
                return [sprintf('v.%s', $mapping['joinColumns'][0]['name'])];

            case ClassMetadataInfo::ONE_TO_MANY:
            case ClassMetadataInfo::ONE_TO_ONE:
                return $this->getClassContentFields($mapping['targetEntity'], $prefix);

            default:
                return [];
        }
    }

    /**
     * Returns the content fields for a class
     *
     * Override this method if some fields of a related entity should not be considered
     *
     * @param string $className
     * @param string $prefix
     *
     * @return array
     */
    protected function getClassContentFields($className, $prefix)
    {
        switch ($className) {
            case 'Pim\Component\Catalog\Model\Metric':
                return [sprintf('%s.%s', $prefix, 'data')];
            case 'Pim\Component\Catalog\Model\ProductPrice':
                return [];
            default:
                return array_map(
                    function ($name) use ($prefix) {
                        return sprintf('%s.%s', $prefix, $name);
                    },
                    array_filter(
                        $this->getClassMetadata($className)->getColumnNames(),
                        function ($value) {
                            return (strpos($value, 'value_') === 0);
                        }
                    )
                );
        }
    }

    /**
     * Returns the SQL joins for the ProductValue entity
     *
     * @return array
     */
    protected function getProductValueJoins()
    {
        $index = 0;

        return array_reduce(
            $this->getClassMetadata($this->productValueClass)->getAssociationMappings(),
            function ($joins, $mapping) use (&$index) {
                $index++;

                return array_merge($joins, $this->getAssociationJoins($mapping, $this->getAssociationAlias($index)));
            },
            []
        );
    }

    /**
     * Returns the SQL joins for an association
     *
     * @param array  $mapping
     * @param string $prefix
     *
     * @return array
     */
    protected function getAssociationJoins($mapping, $prefix)
    {
        if (in_array($mapping['fieldName'], $this->getSkippedMappings())) {
            return [];
        }

        if ($mapping['targetEntity'] === 'Pim\Component\Catalog\Model\ProductPrice') {
            return [];
        }

        switch ($mapping['type']) {
            case ClassMetadataInfo::MANY_TO_MANY:
                return [
                    sprintf(
                        'LEFT JOIN %s %s ON %s.%s = v.id ',
                        $mapping['joinTable']['name'],
                        $prefix,
                        $prefix,
                        $mapping['joinTable']['joinColumns'][0]['name']
                    )
                ];

            case ClassMetadataInfo::ONE_TO_MANY:
                $relatedMetadata = $this->getClassMetadata($mapping['targetEntity']);
                $relatedMapping = $relatedMetadata->getAssociationMapping($mapping['mappedBy']);

                return [
                    sprintf(
                        'LEFT JOIN %s %s ON %s.%s = v.id',
                        $relatedMetadata->getTableName(),
                        $prefix,
                        $prefix,
                        $relatedMapping['joinColumns'][0]['name']
                    )
                ];
            case ClassMetadataInfo::ONE_TO_ONE:
                $relatedMetadata = $this->getClassMetadata($mapping['targetEntity']);

                $joinPattern = 'LEFT JOIN %s %s ON %s.id = v.%s';
                $joinColumn = $mapping['joinColumns'][0]['name'];

                return [
                    sprintf(
                        $joinPattern,
                        $relatedMetadata->getTableName(),
                        $prefix,
                        $prefix,
                        $joinColumn
                    )
                ];

            default:
                return [];
        }
    }

    /**
     * Returns the name of ProductValue mappings which should be skipped
     *
     * @return array
     */
    protected function getSkippedMappings()
    {
        return ['attribute', 'entity'];
    }

    /**
     * Returns the alias for an association
     *
     * @param int $index
     *
     * @return string
     */
    protected function getAssociationAlias($index)
    {
        return sprintf('_rel_%d', $index);
    }

    /**
     * Returns the meta data for a class
     *
     * @param string $className
     *
     * @return ClassMetadataInfo
     */
    protected function getClassMetadata($className)
    {
        return $this->manager->getClassMetadata($className);
    }

    /**
     * {@inheritdoc}
     */
    public function schedule(ProductInterface $product)
    {
        $sql = '
            DELETE c FROM pim_catalog_completeness c
            JOIN %product_table% p ON p.id = c.product_id
            WHERE p.id = :product_id';

        $sql = $this->applyTableNames($sql);

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('product_id', $product->getId());

        $stmt->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function scheduleForFamily(FamilyInterface $family)
    {
        $sql = '
            DELETE c FROM pim_catalog_completeness c
            JOIN %product_table% p ON p.id = c.product_id
            WHERE p.family_id = :family_id';

        $sql = $this->applyTableNames($sql);

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('family_id', $family->getId());

        $stmt->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function scheduleForChannelAndLocale(ChannelInterface $channel, LocaleInterface $locale)
    {
        $sql = <<<SQL
            DELETE c FROM pim_catalog_completeness c
            WHERE c.channel_id = :channel_id
            AND c.locale_id = :locale_id
SQL;

        $sql = $this->applyTableNames($sql);

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('channel_id', $channel->getId());
        $stmt->bindValue('locale_id', $locale->getId());

        $stmt->execute();
    }

    /**
     * @param array $criteria
     *
     * @return string[]
     */
    protected function getExtraJoins(array $criteria)
    {
        return [];
    }

    /**
     * @param array $criteria
     *
     * @return string[]
     */
    protected function getExtraConditions(array $criteria)
    {
        return [];
    }
}
