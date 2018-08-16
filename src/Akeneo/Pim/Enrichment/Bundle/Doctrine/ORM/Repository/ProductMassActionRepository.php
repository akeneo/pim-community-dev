<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductMassActionRepositoryInterface;
use Doctrine\ORM\EntityManager;

/**
 * Mass action repository for product entities
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductMassActionRepository implements ProductMassActionRepositoryInterface
{
    /** @var string */
    protected $entityName;

    /** @var EntityManager */
    protected $em;

    /**
     * @param EntityManager $em
     * @param string        $entityName
     */
    public function __construct(EntityManager $em, $entityName)
    {
        $this->em = $em;
        $this->entityName = $entityName;
    }

    /**
     * {@inheritdoc}
     *
     * @param ProductQueryBuilder $queryBuilder
     */
    public function applyMassActionParameters($queryBuilder, $inset, array $values)
    {
        if (!empty($values)) {
            $condition = $inset ? Operators::IN_LIST : Operators::NOT_IN_LIST;
            $queryBuilder->addFilter('id', $condition, $values);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteFromIds(array $identifiers)
    {
        if (empty($identifiers)) {
            throw new \LogicException('No products to remove');
        }

        $qb = $this->em->createQueryBuilder();
        $qb
            ->delete($this->entityName, 'p')
            ->where($qb->expr()->in('p.id', $identifiers));

        return $qb->getQuery()->execute();
    }
}
