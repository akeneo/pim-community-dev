<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CurrencyInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;

/**
 * Channel repository
 * Define a default sort order by label
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelRepository extends EntityRepository implements ChannelRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        if (null === $orderBy) {
            $orderBy = ['label' => 'ASC'];
        }

        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria, array $orderBy = null)
    {
        if (null === $orderBy) {
            $orderBy = ['label' => 'ASC'];
        }

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
            [
                ':channel_id'         => $channel->getId(),
                ':current_locale_ids' => $currentLocaleIds,
            ],
            [
                ':current_locale_ids' => Connection::PARAM_INT_ARRAY,
            ]
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

    /**
     * {@inheritdoc}
     */
    public function getChannelCountUsingCurrency(CurrencyInterface $currency)
    {
        return (int) $this->createQueryBuilder('c')
                ->select('count(c.id)')
                ->innerJoin('c.currencies', 'cu')
                ->where('cu.id = :currencies')
                ->setParameter('currencies', [$currency])
                ->getQuery()
                ->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($code)
    {
        return $this->findOneBy(['code' => $code]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }

    /**
     * {@inheritdoc}
     */
    public function getLabelsIndexedByCode()
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('c.code, c.label');
        $channels = $qb->getQuery()->getArrayResult();
        $choices = [];
        foreach ($channels as $channel) {
            $choices[$channel['code']] = $channel['label'];
        }

        return $choices;
    }
}
