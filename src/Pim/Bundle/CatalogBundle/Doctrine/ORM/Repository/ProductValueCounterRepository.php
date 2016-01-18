<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Pim\Component\Catalog\Repository\ProductValueCounterRepositoryInterface;

/**
 * Product value repository used to retrieve the number of product values. This number can be used
 * to know whether MongoDB support should be enabled or not.
 *
 * @author    Remy Betus <remy.betus@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueCounterRepository implements ProductValueCounterRepositoryInterface
{
    /** @var EntityManager */
    protected $entityManager;

    /** @var string */
    protected $productValueClass;

    /**
     * @param EntityManager $entityManager
     * @param string        $productValueClass
     */
    public function __construct(EntityManager $entityManager, $productValueClass)
    {
        $this->entityManager = $entityManager;
        $this->productValueClass = $productValueClass;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $productValueRepository = $this->entityManager->getRepository($this->productValueClass);
        $qb = $productValueRepository->createQueryBuilder('pv');
        $qb->select('count(pv.id)');

        return $qb->getQuery()->getSingleScalarResult();
    }
}
