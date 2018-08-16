<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CountEntityWithFamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Find the number of product and product models count belonging to the given family variant
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CountEntityWithFamilyVariant implements CountEntityWithFamilyVariantInterface
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
     * @param FamilyVariantInterface $familyVariant
     *
     * @return int
     */
    public function belongingToFamilyVariant(FamilyVariantInterface $familyVariant): int
    {
        $productModelCount = $this->countProductModels($familyVariant);
        $productCount = $this->countVariantProducts($familyVariant);

        return $productModelCount + $productCount;
    }

    /**
     * @param FamilyVariantInterface $familyVariant
     *
     * @return int
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function countProductModels(FamilyVariantInterface $familyVariant): int
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $productModelCount = $queryBuilder->select('COUNT(pm)')
            ->from(ProductModelInterface::class, 'pm')
            ->where('pm.familyVariant = :family_variant_id')
            ->setParameter(':family_variant_id', $familyVariant->getId())
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $productModelCount;
    }

    /**
     * @param FamilyVariantInterface $familyVariant
     *
     * @return int
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function countVariantProducts(FamilyVariantInterface $familyVariant): int
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $productCount = $queryBuilder->select('COUNT(p)')
            ->from(ProductInterface::class, 'p')
            ->where('p.familyVariant = :family_variant_id')
            ->setParameter(':family_variant_id', $familyVariant->getId())
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $productCount;
    }
}
