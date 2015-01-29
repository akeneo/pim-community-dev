<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Doctrine\DBAL\Connection;
use Pim\Bundle\CatalogBundle\Doctrine\ReferableEntityRepository;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;

/**
 * Channel repository
 * Define a default sort order by label
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be moved to Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository in 1.4
 */
class ChannelRepository extends ReferableEntityRepository implements ChannelRepositoryInterface
{
    /**
     * {@inheritdoc}
     *
     */
    public function getChannels()
    {
        // TODO (JJ) findAll ?
        return parent::findBy([]);
    }

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
    public function findOneBy(array $criteria, array $orderBy = array('label' => 'ASC'))
    {
        return parent::findOneBy($criteria, $orderBy);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getDeletedLocaleIdsForChannel(ChannelInterface $channel)
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

    /**
     * {@inheritdoc}
     */
    public function getChannelCodes()
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('c.code')->orderBy('c.code');

        $res = $qb->getQuery()->getScalarResult();

        $codes = [];
        foreach ($res as $row) {
            $codes[] = $row['code'];
        }

        return $codes;
    }

    /**
     * {@inheritdoc}
     */
    public function getFullChannels()
    {
        return $this
            ->createQueryBuilder('ch')
            ->select('ch, lo, cu')
            ->leftJoin('ch.locales', 'lo')
            ->leftJoin('ch.currencies', 'cu')
            ->getQuery()
            ->getResult();
    }
}
