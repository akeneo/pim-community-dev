<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM;

use Pim\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

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
    /**
     * @var RegistryInterface
     */
    protected $doctrine;
    /**
     * @var string
     */
    protected $productClass;

    /**
     * @var string
     */
    protected $productValueClass;

    /**
     * Constructor
     *
     * @param ManagerRegistry $doctrine
     * @param string          $productClass
     * @param string          $productValueClass
     */
    public function __construct(ManagerRegistry $doctrine, $productClass, $productValueClass)
    {
        $this->doctrine = $doctrine;
        $this->productClass = $productClass;
        $this->productValueClass = $productValueClass;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $criteria, $limit = null)
    {
        $sql = $this->getInsertCompletenessSQL($criteria, $limit);

        $stmt = $this->doctrine->getConnection()->prepare($sql);

        foreach ($criteria as $placeholder => $value) {
            $stmt->bindValue($placeholder, $value);
        }
        $stmt->execute();
    }

    /**
     * Get the sql query to insert completeness
     *
     * @param array   $criteria
     * @param inreger $limit
     *
     * @return string
     */
    protected function getInsertCompletenessSQL(array $criteria, $limit)
    {
        $sql = $this->getInsertCompletenessSQLCondition($criteria) . ' GROUP BY p.id, c.id, l.id';

        if ($limit) {
            $sql .= ' LIMIT ' . $limit;
        }

        return strtr($sql, $this->getReplacements()) . ';';
    }

    /**
     * Returns the raw SQL query, containing placeholders
     *
     * @return string
     */
    protected function getRawQuery()
    {
        return <<<SQL
            INSERT INTO pim_catalog_completeness (
                locale_id, channel_id, product_id, ratio, missing_count, required_count
            )
                SELECT
                    l.id, c.id, p.id,
                    (
                        COUNT(distinct v.id)
                        / (
                            SELECT count(*)
                                FROM pim_catalog_attribute_requirement
                                WHERE family_id = p.family_id
                                    AND channel_id = c.id
                                    AND required = true
                        )
                        * 100
                    ),
                    (
                        (
                            SELECT count(*)
                                FROM pim_catalog_attribute_requirement
                                WHERE family_id = p.family_id
                                    AND channel_id = c.id
                                    AND required = true
                        ) - COUNT(distinct v.id)
                    ),
                    (
                        SELECT count(*)
                            FROM pim_catalog_attribute_requirement
                            WHERE family_id = p.family_id
                                AND channel_id = c.id
                                AND required = true
                    )
                    FROM pim_catalog_attribute_requirement r
                        JOIN pim_catalog_channel c ON c.id = r.channel_id
                        JOIN pim_catalog_channel_locale cl ON cl.channel_id = c.id
                        JOIN pim_catalog_locale l ON l.id = cl.locale_id
                        JOIN %product_interface% p ON p.family_id = r.family_id
                        JOIN %product_value_interface% v ON v.attribute_id = r.attribute_id
                            AND (v.scope_code = c.code OR v.scope_code IS NULL)
                            AND (v.locale_code = l.code OR v.locale_code IS NULL)
                            AND v.entity_id = p.id
                        LEFT JOIN pim_catalog_completeness co ON co.product_id = p.id AND co.channel_id = c.id
                            AND co.locale_id = l.id
                        %product_value_joins%
                    WHERE (%product_value_conditions%) AND r.required = true AND co.id IS NULL
SQL;
    }

    /**
     * Get the sql condition to insert completeness
     *
     * @param array $criteria
     *
     * @return string
     */
    protected function getInsertCompletenessSQLCondition(array $criteria)
    {
        $sql = $this->getRawQuery();

        if (array_key_exists('product', $criteria) && array_key_exists('channel', $criteria)) {
            $sql .= <<<SQL
                        AND p.id = :product
                        AND c.id = :channel
SQL;
        }

        if (array_key_exists('product', $criteria) && !array_key_exists('channel', $criteria)) {
            $sql .= <<<SQL
                        AND p.id = :product
SQL;
        }

        if (!array_key_exists('product', $criteria) && array_key_exists('channel', $criteria)) {
            $sql .= <<<SQL
                        AND p.id IN (
                            SELECT p.id FROM pim_catalog_channel ch
                                JOIN %product_interface% p
                                LEFT JOIN pim_catalog_completeness c
                                    ON c.product_id = p.id
                                    AND c.channel_id = ch.id
                                    WHERE ch.id = :channel AND c.id IS NULL
                        )
                        AND c.id = :channel
SQL;
        }

        return $sql;
    }

    /**
     * Returns an array of replacements for the query
     *
     * @return array
     */
    protected function getReplacements()
    {
        return array_map(
            function ($className) {
                return $this->getClassMetadata($className)->getTableName();
            },
            array(
                '%product_interface%'       => $this->productClass,
                '%product_value_interface%' => $this->productValueClass
            )
        ) + array(
            '%product_value_conditions%' => implode(' OR ', $this->getProductValueConditions()),
            '%product_value_joins%'      => implode(' ', $this->getProductValueJoins())
        );
    }

    /**
     * Returns an array of SQL conditions for the ProductValue entity
     *
     * @return array
     */
    protected function getProductValueConditions()
    {
        $index = 0;

        return array_map(
            function ($field) {
                return sprintf('%s IS NOT NULL', $field);
            },
            array_merge(
                $this->getClassContentFields($this->productValueClass, 'v'),
                array_reduce(
                    $this->getClassMetadata($this->productValueClass)->getAssociationMappings(),
                    function ($fields, $mapping) use (&$index) {
                        $index++;

                        return array_merge(
                            $fields,
                            $this->getAssociationFields($mapping, $this->getAssociationAlias($index))
                        );
                    },
                    array()
                )
            )
        );
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
            return array();
        }

        switch ($mapping['type']) {
            case ClassMetadataInfo::MANY_TO_MANY:
                return array(
                    sprintf(
                        '%s.%s',
                        $prefix,
                        $mapping['joinTable']['inverseJoinColumns'][0]['name']
                    )
                );

            case ClassMetadataInfo::MANY_TO_ONE:
                return array(sprintf('v.%s', $mapping['joinColumns'][0]['name']));

            case ClassMetadataInfo::ONE_TO_MANY:
            case ClassMetadataInfo::ONE_TO_ONE:
                return $this->getClassContentFields($mapping['targetEntity'], $prefix);

            default:
                return array();
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
        if ('Pim\Bundle\CatalogBundle\Model\Metric' == $className ||
            'Pim\Bundle\CatalogBundle\Model\ProductPrice' == $className) {
            return array(sprintf('%s.%s', $prefix, 'data'));
        } elseif ('Pim\Bundle\CatalogBundle\Model\Media' == $className) {
            return array(sprintf('%s.%s', $prefix, 'filename'));
        } else {
            return array_map(
                function ($name) use ($prefix) {
                    return sprintf('%s.%s', $prefix, $name);
                },
                array_diff(
                    $this->getClassMetadata($className)->getColumnNames(),
                    array('id', 'locale_code', 'scope_code')
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
            array()
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
            return array();
        }

        switch ($mapping['type']) {
            case ClassMetadataInfo::MANY_TO_MANY:
                return array(
                    sprintf(
                        'LEFT JOIN %s %s ON %s.%s=v.id ',
                        $mapping['joinTable']['name'],
                        $prefix,
                        $prefix,
                        $mapping['joinTable']['joinColumns'][0]['name']
                    )
                );

            case ClassMetadataInfo::ONE_TO_MANY:
                $relatedMetadata = $this->getClassMetadata($mapping['targetEntity']);
                $relatedMapping = $relatedMetadata->getAssociationMapping($mapping['mappedBy']);

                return array(
                    sprintf(
                        'LEFT JOIN %s %s ON %s.%s=v.id',
                        $relatedMetadata->getTableName(),
                        $prefix,
                        $prefix,
                        $relatedMapping['joinColumns'][0]['name']
                    )
                );
            case ClassMetadataInfo::ONE_TO_ONE:
                $relatedMetadata = $this->getClassMetadata($mapping['targetEntity']);

                $joinPattern = 'LEFT JOIN %s %s ON %s.id=v.%s';
                $joinColumn = $mapping['joinColumns'][0]['name'];

                return array(
                    sprintf(
                        $joinPattern,
                        $relatedMetadata->getTableName(),
                        $prefix,
                        $prefix,
                        $joinColumn
                    )
                );

            default:
                return array();
        }
    }

    /**
     * Returns the name of ProductValue mappings which should be skipped
     *
     * @return array
     */
    protected function getSkippedMappings()
    {
        return array('attribute', 'entity');
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
     * @return array
     */
    protected function getClassMetadata($className)
    {
        return $this->doctrine->getManager()->getClassMetadata($className);
    }
}
