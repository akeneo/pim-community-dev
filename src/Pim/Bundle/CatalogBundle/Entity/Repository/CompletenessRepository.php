<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Pim\Bundle\CatalogBundle\Doctrine\EntityRepository;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Product;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Completeness repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessRepository extends EntityRepository
{
    /** @var string */
    private static $sql = <<<SQL
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
                        JOIN pim_catalog_product p ON p.family_id = r.family_id
                        JOIN pim_catalog_product_value v ON v.attribute_id = r.attribute_id
                            AND (v.scope_code = c.code OR v.scope_code IS NULL)
                            AND (v.locale_code = l.code OR v.locale_code IS NULL)
                            AND v.entity_id = p.id
                        LEFT JOIN pim_catalog_value_option o ON o.value_id = v.id
                        LEFT JOIN pim_catalog_product_value_price m ON m.value_id = v.id
                        JOIN (
                            SELECT p.id as product_id, ch.id as channel_id FROM pim_catalog_channel ch
                                JOIN pim_catalog_product p
                                LEFT JOIN pim_catalog_completeness c
                                    ON c.product_id = p.id
                                    AND c.channel_id = ch.id
                                    WHERE c.id IS NULL
                        ) as pending_product ON pending_product.product_id = p.id AND pending_product.channel_id = c.id
                    WHERE (
                            v.option_id         IS NOT NULL
                            OR v.media_id       IS NOT NULL
                            OR v.metric_id      IS NOT NULL
                            OR v.value_string   IS NOT NULL
                            OR v.value_integer  IS NOT NULL
                            OR v.value_decimal  IS NOT NULL
                            OR v.value_boolean  IS NOT NULL
                            OR v.value_text     IS NOT NULL
                            OR v.value_date     IS NOT NULL
                            OR v.value_datetime IS NOT NULL
                            OR o.value_id       IS NOT NULL
                            OR m.data           IS NOT NULL
                        )
                        AND r.required = true
SQL;

    /**
     * Insert missing completenesses for a given channel
     *
     * @param Channel $channel
     */
    public function createChannelCompletenesses(Channel $channel)
    {
        $this->createCompletenesses(array('channel' => $channel->getId()));
    }

    /**
     * Insert missing completenesses for a given product
     *
     * @param Product $product
     */
    public function createProductCompletenesses(Product $product)
    {
        $this->createCompletenesses(array('product' => $product->getId()));
    }

    /**
     * Insert n missing completenesses
     *
     * @param int $limit
     */
    public function createAllCompletenesses($limit = 100)
    {
        $this->createCompletenesses(array(), $limit);
    }

    /**
     * Schedule recalculation of completenesses for a product
     *
     * @param ProductInterface $product
     */
    public function schedule(ProductInterface $product)
    {
        if ($product->getId()) {
            $query = $this->_em->createQuery(
                'DELETE FROM Pim\Bundle\CatalogBundle\Entity\Completeness c WHERE c.product = :product'
            );
            $query->setParameter('product', $product);
            $query->execute();
        }
    }

    /**
     * Insert missing completeness according to the criteria
     *
     * @param array   $criteria
     * @param inreger $limit
     */
    protected function createCompletenesses(array $criteria, $limit = null)
    {
        $sql = $this->getInsertCompletenessSQL($criteria, $limit);

        $stmt = $this
            ->getEntityManager()
            ->getConnection()
            ->prepare($sql);

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
    private function getInsertCompletenessSQL(array $criteria, $limit)
    {
        $sql = $this->getInsertCompletenessSQLCondition($criteria) . ' GROUP BY p.id, c.id, l.id';

        if ($limit) {
            $sql .= ' LIMIT ' . $limit;
        }

        return $sql . ';';
    }

    /**
     * Get the sql condition to insert completeness
     *
     * @param array $criteria
     *
     * @return string
     */
    private function getInsertCompletenessSQLCondition(array $criteria)
    {
        $sql = self::$sql;

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
                                JOIN pim_catalog_product p
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
}
