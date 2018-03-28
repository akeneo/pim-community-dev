<?php

namespace Pim\Bundle\AnalyticsBundle\Doctrine\Query;

use Akeneo\Component\StorageUtils\Repository\CountableRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Count the number of variant products
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CountVariantProduct implements CountableRepositoryInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function countAll(): int
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb
            ->from(ProductInterface::class, 'vp')
            ->select('COUNT(vp.id)')
            ->where('vp.parent IS NOT NULL');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
