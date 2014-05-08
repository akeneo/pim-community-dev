<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query;
use Pim\Bundle\CatalogBundle\Doctrine\ReferableEntityRepository;
use Pim\Bundle\CatalogBundle\Entity\Channel;

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

    /**
     * @return QueryBuilder
     */
    public function createDatagridQueryBuilder()
    {
        $qb = $this->createQueryBuilder('c');
        $rootAlias = $qb->getRootAlias();

        $treeExpr = '(CASE WHEN ct.label IS NULL THEN category.code ELSE ct.label END)';

        $qb
            ->addSelect($rootAlias)
            ->addSelect('category')
            ->addSelect(sprintf('%s AS categoryLabel', $treeExpr))
            ->addSelect('ct.label');

        $qb
            ->innerJoin(sprintf('%s.category', $rootAlias), 'category')
            ->leftJoin('category.translations', 'ct', 'WITH', 'ct.locale = :localeCode');

        $qb->groupBy($rootAlias);

        return $qb;
    }

    /**
     * Get the deleted locales of a channel (the channel is updated but not flushed yet).
     *
     * @param Channel $channel
     *
     * @return array the list of deleted locales
     */
    public function getDeletedLocaleIdsForChannel(Channel $channel)
    {
        $currentLocaleIds = array_map(
            function ($locale) {
                return $locale->getId();
            },
            $channel->getLocales()->toArray()
        );

        $sql = <<<SQL
    SELECT cl.locale_id
    FROM pim_catalog_channel_locale cl
    WHERE cl.channel_id = :channel_id
      AND cl.locale_id NOT IN (:current_locale_ids)
SQL;

        $stmt = $this->getEntityManager()->getConnection()->executeQuery(
            $sql,
            array(
                ':channel_id' => $channel->getId(),
                ':current_locale_ids' => $currentLocaleIds,
            ),
            array(
                ':current_locale_ids' => Connection::PARAM_INT_ARRAY,
            )
        );

        $rows = $stmt->fetchAll();

        $locales = array_map(
            function ($row) {
                return (int) $row['locale_id'];
            },
            $rows
        );

        return $locales;
    }
}
