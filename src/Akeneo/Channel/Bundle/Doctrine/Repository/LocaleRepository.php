<?php

namespace Akeneo\Channel\Bundle\Doctrine\Repository;

use Doctrine\ORM\QueryBuilder;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Locale repository
 * Define a default sort order by code
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleRepository extends EntityRepository implements LocaleRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): array
    {
        if (null === $orderBy) {
            $orderBy = ['code' => 'ASC'];
        }

        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria, array $orderBy = null): ?object
    {
        if (null === $orderBy) {
            $orderBy = ['code' => 'ASC'];
        }

        return parent::findOneBy($criteria, $orderBy);
    }

    /**
     * {@inheritdoc}
     */
    public function getActivatedLocales(): array
    {
        $qb = $this->getActivatedLocalesQB();

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getActivatedLocaleCodes(): array
    {
        $qb = $this->getActivatedLocalesQB();
        $qb->select('l.code');

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
    public function getActivatedLocalesQB(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('l');
        $qb->where($qb->expr()->eq('l.activated', true))
            ->orderBy('l.code');

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getDeletedLocalesForChannel(ChannelInterface $channel): array
    {
        $currentLocaleIds = array_map(
            fn(LocaleInterface $locale) => $locale->getId(),
            $channel->getLocales()->toArray()
        );

        return $this->createQueryBuilder('l')
            ->innerJoin('l.channels', 'lc')
            ->andWhere('lc.id = :channel_id')
            ->andWhere('l.id NOT IN (:current_locale_ids)')
            ->setParameter(':channel_id', $channel->getId())
            ->setParameter(':current_locale_ids', $currentLocaleIds)
            ->getQuery()
            ->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier(string $code): ?object
    {
        return $this->findOneBy(['code' => $code]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties(): array
    {
        return ['code'];
    }

    /**
     * {@inheritdoc}
     */
    public function countAllActivated(): int
    {
        $countQb = $this->createQueryBuilder('l');

        return $countQb
            ->select('COUNT(l.id)')
            ->where($countQb->expr()->eq('l.activated', true))
            ->getQuery()
            ->getSingleScalarResult();
    }
}
