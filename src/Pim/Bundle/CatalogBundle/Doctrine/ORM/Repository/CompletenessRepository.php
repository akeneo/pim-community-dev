<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Pim\Component\Catalog\Repository\CompletenessRepositoryInterface;

/**
 * Completeness Repository for ORM
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessRepository implements CompletenessRepositoryInterface
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $productClass;

    /**
     * @param EntityManager $entityManager
     * @param string        $productClass
     */
    public function __construct(
        EntityManager $entityManager,
        $productClass
    ) {
        $this->entityManager = $entityManager;
        $this->productClass = $productClass;
    }

    /**
     * {@inheritdoc}
     *
     * The request selects at first in an optimised subquery all the enabled product for a given channel.
     * It eliminates duplicates in this subquery for performance concern, by using DISTINCT instead of GROUP BY, which is faster in that case.
     * After that, it joins with the table channel to get the label. It does not get the label in the subquery for performance concern.
     */
    public function getProductsCountPerChannels($localeCode)
    {
        $sql = <<<SQL
        SELECT co.label, co.total FROM
        (
            SELECT t.foreign_key, t.label, COUNT(p.id) as total
            FROM (
                SELECT DISTINCT ch.id as channel_id, p.id FROM pim_catalog_channel ch
                JOIN %category_table% ca ON ca.root = ch.category_id
                JOIN %category_join_table% cp ON cp.category_id = ca.id
                JOIN %product_table% p ON p.id = cp.product_id
                WHERE p.is_enabled = 1
            ) as p
            JOIN pim_catalog_channel_translation t on t.foreign_key = p.channel_id AND t.locale = :locale
            GROUP BY t.foreign_key, t.label
        ) as co;
SQL;

        $sql = $this->applyTableNames($sql);
        $stmt = $this->entityManager->getConnection()->executeQuery($sql, ['locale' => $localeCode]);

        return $stmt->fetchAll();
    }

    /**
     * {@inheritdoc}
     *
     * The request selects at first in an optimised subquery all the enabled product for a given channel.
     * It eliminates duplicates in this subquery for performance concern, by using DISTINCT and then using GROUP BY, which is faster.
     * After that, it joins with the other tables to get the locale code, the channel label, and filter to get only the complete products.
     */
    public function getCompleteProductsCountPerChannels($localeCode)
    {
        $sql = <<<SQL
            SELECT IFNULL(t.label, ch.code),lo.code as locale, COALESCE(co.total,0)
            FROM pim_catalog_channel ch
            LEFT JOIN pim_catalog_channel_translation t on t.foreign_key = ch.id and t.locale = :locale
            LEFT JOIN pim_catalog_channel_locale cl ON cl.channel_id = ch.id
            LEFT JOIN pim_catalog_locale lo ON lo.id = cl.locale_id
            LEFT JOIN (
                SELECT p.channel_id,lo.id as locale_id, COUNT(co.product_id) as total
                FROM (
                    SELECT DISTINCT ch.id as channel_id, cp.product_id as id
                    FROM pim_catalog_channel ch
                    JOIN %category_table% ca ON ca.root = ch.category_id
                    JOIN %category_join_table% cp ON cp.category_id = ca.id
                    JOIN %product_table% p ON p.id = cp.product_id
                    WHERE p.is_enabled = 1
                ) as p
                JOIN pim_catalog_channel_locale cl ON cl.channel_id = p.channel_id
                JOIN pim_catalog_locale lo ON lo.id = cl.locale_id
                JOIN pim_catalog_completeness co ON co.locale_id = lo.id AND co.channel_id = p.channel_id AND co.product_id = p.id AND co.ratio = 100
                GROUP BY p.channel_id, lo.id
            ) co on ch.id = co.channel_id and co.locale_id = lo.id;
SQL;

        $sql = $this->applyTableNames($sql);
        $stmt = $this->entityManager->getConnection()->executeQuery($sql, ['locale' => $localeCode]);

        return $stmt->fetchAll();
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
        $categoryMapping = $this->entityManager
            ->getClassMetadata($this->productClass)
            ->getAssociationMapping('categories');

        $categoryMetadata = $this->entityManager->getClassMetadata($categoryMapping['targetEntity']);

        return strtr(
            $sql,
            [
                '%category_table%'      => $categoryMetadata->getTableName(),
                '%category_join_table%' => $categoryMapping['joinTable']['name'],
                '%product_table%'       => $this->entityManager->getClassMetadata($this->productClass)->getTableName(),
            ]
        );
    }
}
