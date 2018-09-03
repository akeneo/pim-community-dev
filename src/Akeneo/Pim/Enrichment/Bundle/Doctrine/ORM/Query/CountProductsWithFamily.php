<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CountProductsWithFamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Count the number of products belonging to the given family
 *
 * @author    Julian Prud'homme <julian.prudhomme@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CountProductsWithFamily implements CountProductsWithFamilyInterface
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
     * @param FamilyInterface $family
     *
     * @return int
     */
    public function count(FamilyInterface $family): int
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $productCount = $queryBuilder->select('COUNT(p)')
            ->from(ProductInterface::class, 'p')
            ->where('p.family = :family_id')
            ->setParameter(':family_id', $family->getId())
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $productCount;
    }
}
