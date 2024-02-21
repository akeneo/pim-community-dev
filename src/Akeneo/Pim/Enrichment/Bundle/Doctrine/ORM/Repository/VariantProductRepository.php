<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\VariantProductRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

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

        if (null !== $uuid = $product->getUuid()) {
            $qb->andWhere('vp.uuid != :uuid')->setParameter('uuid', $uuid);
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
            ->select('vp.identifier')
            ->from(ProductInterface::class, 'vp')
            ->where('vp.parent = :parent')
            ->setParameter('parent', $parent)
            ->orderBy('vp.created', 'ASC')
            ->addOrderBy('vp.identifier', 'ASC')
            ->setMaxResults(1);

        $results = $qb->getQuery()->getOneOrNullResult();

        if (null === $results || !isset($results['identifier'])) {
            return null;
        }

        return $this->findByIdentifier($results['identifier']);
    }

    private function findByIdentifier(string $identifier): ?ProductInterface
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb
            ->select('vp')
            ->from(ProductInterface::class, 'vp')
            ->where('vp.identifier = :identifier')
            ->setParameter('identifier', $identifier);

        $results = $qb->getQuery()->execute();

        if (empty($results)) {
            return null;
        }

        return current($results);
    }
}
