<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
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
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        if (null === $orderBy) {
            $orderBy = ['code' => 'ASC'];
        }

        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria, array $orderBy = null)
    {
        if (null === $orderBy) {
            $orderBy = ['code' => 'ASC'];
        }

        return parent::findOneBy($criteria, $orderBy);
    }

    /**
     * {@inheritdoc}
     */
    public function countAll()
    {
        $qb = $this->createQueryBuilder('c');

        return (int) $qb
            ->select('count(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
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
        $qb->leftJoin('c.translations', 'tr', 'WITH', 'tr.locale = :userLocaleCode');
        $qb->select('c.code, COALESCE(NULLIF(tr.label, \'\'), CONCAT(\'[\', c.code, \']\')) as label');

        $qb->setParameter('userLocaleCode', $localeCode);

        $channels = $qb->getQuery()->getArrayResult();
        $choices = [];
        foreach ($channels as $channel) {
            $choices[$channel['code']] = null !== $channel['label'] ? $channel['label'] : '[' . $channel['code'] . ']';
        }

        return $choices;
    }
}
