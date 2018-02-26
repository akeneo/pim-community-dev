<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Repository\VariantProductRepositoryInterface;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantProductRepository implements VariantProductRepositoryInterface
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
    public function findSiblingsProducts(ProductInterface $product): array
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb
            ->select('vp')
            ->from(ProductInterface::class, 'vp')
            ->where('vp.parent = :parent')
            ->setParameter('parent', $product->getParent());

        if (null !== $id = $product->getId()) {
            $qb->andWhere('vp.id != :id')->setParameter('id', $id);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function findLastCreatedByParent(ProductModelInterface $parent): ?ProductInterface
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb
            ->select('vp')
            ->from(ProductInterface::class, 'vp')
            ->where('vp.parent = :parent')
            ->setParameter('parent', $parent->getParent())
            ->orderBy('vp.created', 'ASC')
            ->addOrderBy('vp.identifier', 'ASC')
            ->setMaxResults(1)
        ;

        $results = $qb->getQuery()->execute();

        if (empty($results)) {
            return null;
        }

        return current($results);
    }
}
