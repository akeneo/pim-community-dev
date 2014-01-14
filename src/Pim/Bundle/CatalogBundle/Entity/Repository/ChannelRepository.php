<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

/**
 * Channel repository
 * Define a default sort order by label
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelRepository extends ReferableEntityRepository
{
    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = array('label' => 'ASC'), $limit = null, $offset = null)
    {
        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria, array $orderBy = array('label' =>'ASC'))
    {
        return parent::findOneBy($criteria, $orderBy);
    }

    /**
     * Return the number of existing channels
     *
     * @return interger
     */
    public function countAll()
    {
        $qb = $this->createQueryBuilder('c');

        return $qb
            ->select('count(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countProducts()
    {
        $sql = <<<SQL
SELECT ch.label, count(p.id) as total FROM pim_catalog_channel ch
    JOIN pim_catalog_category ca ON ca.root = ch.category_id
    JOIN pim_catalog_category_product cp ON cp.category_id = ca.id
    JOIN pim_catalog_product p ON p.id = cp.product_id
    WHERE p.is_enabled = 1
    GROUP BY ch.id
SQL;

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countCompleteProducts()
    {
        $sql = <<<SQL
SELECT ch.label, lo.code as locale, count(co.product_id) as total FROM pim_catalog_channel ch
    JOIN pim_catalog_category ca ON ca.root = ch.category_id
    JOIN pim_catalog_category_product cp ON cp.category_id = ca.id
    JOIN pim_catalog_product p ON p.id = cp.product_id
    JOIN pim_catalog_channel_locale cl ON cl.channel_id = ch.id
    JOIN pim_catalog_locale lo ON lo.id = cl.locale_id
    LEFT JOIN pim_catalog_completeness co ON co.locale_id = lo.id AND co.channel_id = ch.id AND co.product_id = p.id AND co.ratio = 100
    WHERE p.is_enabled = 1
    GROUP BY ch.id, lo.id
SQL;

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
