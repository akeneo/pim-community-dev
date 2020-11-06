<?php

namespace Akeneo\Channel\Bundle\Doctrine\Repository;

use Doctrine\ORM\QueryBuilder;
use Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Currency repository
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CurrencyRepository extends EntityRepository implements CurrencyRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getActivatedCurrencies(): array
    {
        $qb = $this->getActivatedCurrenciesQB();

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getActivatedCurrencyCodes(): array
    {
        $qb = $this->getActivatedCurrenciesQB();
        $qb->select('c.code');

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
    protected function getActivatedCurrenciesQB(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');
        $qb->where($qb->expr()->eq('c.activated', true))
            ->orderBy('c.code');

        return $qb;
    }
}
