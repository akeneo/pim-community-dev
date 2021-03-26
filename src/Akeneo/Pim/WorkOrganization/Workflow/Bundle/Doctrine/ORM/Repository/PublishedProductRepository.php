<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ProductRepository;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedProductRepositoryInterface;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

/**
 * Published products repository
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class PublishedProductRepository extends ProductRepository implements PublishedProductRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findOneByOriginalProduct(ProductInterface $originalProduct)
    {
        return $this->findOneBy(['originalProduct' => $originalProduct->getId()]);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByOriginalProductId($originalProductId)
    {
        return $this->findOneBy(['originalProduct' => $originalProductId]);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByVersionId($versionId)
    {
        return $this->findOneBy(['version' => $versionId]);
    }

    /**
     * {@inheritdoc}
     */
    public function findByOriginalProducts(array $originalProducts)
    {
        $originalIds = [];
        foreach ($originalProducts as $product) {
            $originalIds[] = $product->getId();
        }

        $qb = $this->createQueryBuilder('pp');
        $qb
            ->where($qb->expr()->in('pp.originalProduct', ':originalIds'))
            ->setParameter(':originalIds', $originalIds);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getPublishedVersionIdByOriginalProductId($originalId)
    {
        $qb = $this->createQueryBuilder('pp');
        $qb
            ->select('IDENTITY(pp.version) AS version_id')
            ->where('pp.originalProduct = :originalId')
            ->setParameter('originalId', $originalId);

        try {
            $versionId = (int) $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException $e) {
            $versionId = null;
        }

        return $versionId;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductIdsMapping(array $originalIds = [])
    {
        $qb = $this->createQueryBuilder('pp');
        $qb->select('pp.id AS published_id, IDENTITY(pp.originalProduct) AS original_id');
        if (!empty($originalIds)) {
            $qb->andWhere($qb->expr()->in('pp.originalProduct', $originalIds));
        }

        $ids = [];
        foreach ($qb->getQuery()->getScalarResult() as $row) {
            $ids[intval($row['original_id'])] = intval($row['published_id']);
        }

        return $ids;
    }

    /**
     * {@inheritdoc}
     */
    public function countPublishedProductsForAssociationType(AssociationTypeInterface $associationType)
    {
        $qb = $this->createQueryBuilder('pp');
        $qb
            ->innerJoin('pp.associations', 'ppa')
            ->andWhere('ppa.associationType = :association_type')
            ->setParameter('association_type', $associationType);

        return $this->getCountFromQB($qb);
    }

    /**
     * Count published variant products for a product model
     *
     * @param ProductModelInterface $productModel
     *
     * @return int
     */
    public function countPublishedVariantProductsForProductModel(ProductModelInterface $productModel): int
    {
        $qb = $this->createQueryBuilder('pp');

        $productModelIds = [$productModel->getId()];

        /** @var ProductModelInterface $child **/
        foreach ($productModel->getProductModels() as $child) {
            $productModelIds[] = $child->getId();
        }

        $qb
            ->innerJoin('pp.originalProduct', 'p')
            ->andWhere($qb->expr()->in('p.parent', ':product_model_ids'))
            ->setParameter('product_model_ids', $productModelIds);

        return $this->getCountFromQB($qb);
    }

    /**
     * Return the result count from a query builder object
     *
     * @param QueryBuilder $qb
     *
     * @return mixed
     */
    protected function getCountFromQB(QueryBuilder $qb)
    {
        $rootAlias = current($qb->getRootAliases());
        $qb->select(sprintf("COUNT(%s.id)", $rootAlias));

        return $qb->getQuery()->getSingleScalarResult();
    }
}
