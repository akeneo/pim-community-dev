<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProduct;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductModelRepository extends EntityRepository implements ProductModelRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getItemsFromIdentifiers(array $identifiers): array
    {
        $qb = $this
            ->createQueryBuilder('pm')
            ->where('pm.code IN (:codes)')
            ->setParameter('codes', $identifiers);

        return $qb->getQuery()->execute();
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
    public function findOneByIdentifier($identifier): ?ProductModelInterface
    {
        return $this->findOneBy(['code' => $identifier]);
    }

    /**
     * {@inheritdoc}
     */
    public function findSiblingsProductModels(ProductModelInterface $productModel): array
    {
        $qb = $this
            ->createQueryBuilder('pm')
            ->where('pm.parent = :parent')
            ->setParameter('parent', $productModel->getParent());

        if (null !== $id = $productModel->getId()) {
            $qb->andWhere('pm.id != :id')
                ->setParameter('id', $id);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function countRootProductModels(): int
    {
        $count = $this->createQueryBuilder('pm')
            ->select('COUNT(pm.id)')
            ->andWhere('pm.parent IS NULL')
            ->getQuery()
            ->getSingleScalarResult();

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function findChildrenProductModels(ProductModelInterface $productModel): array
    {
        $qb = $this
            ->createQueryBuilder('pm')
            ->where('pm.parent = :parent')
            ->setParameter('parent', $productModel);

        return $qb->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function findDescendantProductIdentifiers(ProductModelInterface $productModel): array
    {
        $qb = $this
            ->_em
            ->createQueryBuilder()
            ->select('p.identifier')
            ->from(VariantProduct::class, 'p')
            ->innerJoin('p.parent', 'pm', 'WITH', 'p.parent = pm.id')
            ->where('p.parent = :parent')
            ->orWhere('pm.parent = :parent')
            ->setParameter('parent', $productModel);

        return $qb->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function findByIdentifiers(array $codes): array
    {
        return $this->findBy(['code' => $codes]);
    }

    /**
     * {@inheritdoc}
     */
    public function findChildrenProducts(ProductModelInterface $productModel): array
    {
        $qb = $this
            ->_em
            ->createQueryBuilder()
            ->select('p')
            ->from(VariantProduct::class, 'p')
            ->where('p.parent = :parent')
            ->setParameter('parent', $productModel);

        return $qb->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function searchRootProductModelsAfter(?ProductModelInterface $productModel, int $limit): array
    {
        $qb = $this->createQueryBuilder('pm')
            ->andWhere('pm.parent IS NULL')
            ->orderBy('pm.id', 'ASC')
            ->setMaxResults($limit);
        ;

        if (null !== $productModel) {
            $qb->andWhere('pm.id > :productModelId')
                ->setParameter(':productModelId', $productModel->getId());
        }

        return $qb->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function findSubProductModels(FamilyVariantInterface $familyVariant): array
    {
        $qb = $this
            ->createQueryBuilder('pm')
            ->where('pm.parent IS NOT NULL')
            ->andWhere('pm.familyVariant = :familyVariant')
            ->setParameter('familyVariant', $familyVariant->getId())
        ;

        return $qb->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function findRootProductModels(FamilyVariantInterface $familyVariant): array
    {
        $qb = $this
            ->createQueryBuilder('pm')
            ->where('pm.parent IS NULL')
            ->andWhere('pm.familyVariant = :familyVariant')
            ->setParameter('familyVariant', $familyVariant->getId())
        ;

        return $qb->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function searchLastLevelByCode(
        FamilyVariantInterface $familyVariant,
        string $search,
        int $limit,
        int $page = 0
    ): array {
        $qb = $this
            ->createQueryBuilder('pm');

        $qb->where($qb->expr()->like('pm.code', '?1'))
            ->setParameter(1, '%' . $search . '%')
            ->setParameter('familyVariant', $familyVariant->getId())
            ->setFirstResult($page * $limit)
            ->setMaxResults($limit);

        $qb = ($familyVariant->getNumberOfLevel() <= 1) ?
            $qb->andWhere('pm.parent IS NULL')->andWhere('pm.familyVariant = :familyVariant') :
            $qb->innerJoin('pm.parent', 'ppm')->andWhere('ppm.familyVariant = :familyVariant');

        return $qb->getQuery()->execute();
    }
}
