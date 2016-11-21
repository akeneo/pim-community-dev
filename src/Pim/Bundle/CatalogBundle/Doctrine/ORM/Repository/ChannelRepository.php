<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CurrencyInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;

/**
 * Channel repository
 * Define a default sort order by code
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
    public function findBy(array $criteria, array $orderBy = ['code' => 'ASC'], $limit = null, $offset = null)
    {
        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria, array $orderBy = ['code' => 'ASC'])
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

        $labelExpr = sprintf(
            '(CASE WHEN translation.label IS NULL THEN %s.code ELSE translation.label END)',
            $rootAlias
        );

        $qb
            ->addSelect($rootAlias)
            ->addSelect('category')
            ->addSelect(sprintf('%s AS categoryLabel', $treeExpr))
            ->addSelect(sprintf('%s AS channelLabel', $labelExpr))
            ->addSelect('translation.label');

        $qb
            ->innerJoin(sprintf('%s.category', $rootAlias), 'category')
            ->leftJoin('category.translations', 'ct', 'WITH', 'ct.locale = :localeCode')
            ->leftJoin($rootAlias . '.translations', 'translation', 'WITH', 'translation.locale = :localeCode');

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
            ->select('ch, lo, cu, tr')
            ->leftJoin('ch.locales', 'lo')
            ->leftJoin('ch.currencies', 'cu')
            ->leftJoin('ch.translations', 'tr')
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
    public function getLabelsIndexedByCode($localeCode)
    {
        $qb = $this->createQueryBuilder('c');
        $qb->leftJoin('c.translations', 'tr');
        $qb->select('c.code, tr.label');

        $qb->where('tr.locale = :userLocaleCode');
        $qb->setParameter('userLocaleCode', $localeCode);

        $channels = $qb->getQuery()->getArrayResult();
        $choices = [];
        foreach ($channels as $channel) {
            $choices[$channel['code']] = null !== $channel['label'] ? $channel['label'] : '[' . $channel['code'] . ']';
        }

        return $choices;
    }
}
