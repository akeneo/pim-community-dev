<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM\Repository;

use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Catalog\Security\Factory\FilteredEntityFactory;
use PimEnterprise\Component\Workflow\Repository\PublishedProductRepositoryInterface;

/**
 * Published products repository with permission applied
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class PublishedProductWithPermissionRepository extends EntityRepository implements
    PublishedProductRepositoryInterface,
    IdentifiableObjectRepositoryInterface,
    CursorableRepositoryInterface
{
    /** @var PublishedProductRepositoryInterface */
    private $publishedProductRepository;

    /** @var FilteredEntityFactory */
    private $filteredProductFactory;

    /**
     * @param EntityManagerInterface              $em
     * @param PublishedProductRepositoryInterface $publishedProductRepository
     * @param FilteredEntityFactory               $filteredProductFactory
     * @param string                              $entityName
     */
    public function __construct(
        EntityManagerInterface $em,
        PublishedProductRepositoryInterface $publishedProductRepository,
        FilteredEntityFactory $filteredProductFactory,
        string $entityName
    ) {
        parent::__construct($em, $em->getClassMetadata($entityName));

        $this->publishedProductRepository = $publishedProductRepository;
        $this->filteredProductFactory = $filteredProductFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByOriginalProduct(ProductInterface $originalProduct)
    {
        return $this->findOneByOriginalProductId($originalProduct->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function find($id, $lockMode = null, $lockVersion = null)
    {
        $publishedProduct = $this->publishedProductRepository->find($id, $lockMode, $lockVersion);
        if (null === $publishedProduct) {
            return null;
        }

        return $this->getFilteredPublishedProduct($publishedProduct);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        $publishedProducts = $this->publishedProductRepository->findAll();

        return $this->getFilteredPublishedProducts($publishedProducts);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $publishedProducts = $this->publishedProductRepository->findBy($criteria, $orderBy, $limit, $offset);

        return $this->getFilteredPublishedProducts($publishedProducts);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        $publishedProduct = $this->publishedProductRepository->findOneBy($criteria);
        if (null === $publishedProduct) {
            return null;
        }

        return $this->getFilteredPublishedProduct($publishedProduct);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByOriginalProductId($originalProductId)
    {
        $publishedProduct = $this->publishedProductRepository->findOneBy(['originalProduct' => $originalProductId]);
        if (null === $publishedProduct) {
            return null;
        }

        return $this->getFilteredPublishedProduct($publishedProduct);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByVersionId($versionId)
    {
        $publishedProduct = $this->findOneBy(['version' => $versionId]);
        if (null === $publishedProduct) {
            return null;
        }

        return $this->getFilteredPublishedProduct($publishedProduct);
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

        $publishedProducts = $qb->getQuery()->getResult();

        return $this->getFilteredPublishedProducts($publishedProducts);
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

        $rootAlias = current($qb->getRootAliases());
        $qb->select(sprintf("COUNT(%s.id)", $rootAlias));

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsFromIdentifiers(array $identifiers)
    {
        $publishedProducts = $this->publishedProductRepository->getItemsFromIdentifiers($identifiers);

        return $this->getFilteredPublishedProducts($publishedProducts);
    }

    /**
     * {@inheritdoc}
     */
    public function searchAfter(?ProductInterface $product, int $limit): array
    {
        $publishedProducts = $this->publishedProductRepository->searchAfter($product, $limit);

        return $this->getFilteredPublishedProducts($publishedProducts);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        $publishedProduct = $this->publishedProductRepository->findOneByIdentifier($identifier);
        if (null === $publishedProduct) {
            return null;
        }

        return $this->getFilteredPublishedProduct($publishedProduct);
    }


    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        return $this->publishedProductRepository->getClassName();
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableAttributeIdsToExport(array $productIds)
    {
        return $this->publishedProductRepository->getAvailableAttributeIdsToExport($productIds);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsByGroup(GroupInterface $group, $maxResults)
    {
        $publishedProducts = $this->publishedProductRepository->getProductsByGroup($group, $maxResults);

        return $this->getFilteredPublishedProducts($publishedProducts);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductCountByGroup(GroupInterface $group)
    {
        return $this->publishedProductRepository->getProductCountByGroup($group);
    }

    /**
     * {@inheritdoc}
     */
    public function countAll()
    {
        return $this->publishedProductRepository->countAll();
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttributeInFamily($productId, $attributeCode)
    {
        return $this->publishedProductRepository->hasAttributeInFamily($productId, $attributeCode);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return $this->publishedProductRepository->getIdentifierProperties();
    }

    /**
     * Get a single published product filtered with only granted data
     *
     * @param ProductInterface $publishedProduct
     *
     * @return ProductInterface
     */
    private function getFilteredPublishedProduct(ProductInterface $publishedProduct): ProductInterface
    {
        return $this->filteredProductFactory->create($publishedProduct);
    }

    /**
     * Get published products filtered with only granted data
     *
     * @param ProductInterface[] $publishedProducts
     *
     * @return array
     */
    private function getFilteredPublishedProducts(array $publishedProducts): array
    {
        $filteredPublishedProducts = [];
        foreach ($publishedProducts as $publishedProduct) {
            $filteredPublishedProducts[] = $this->filteredProductFactory->create($publishedProduct);
        }

        return $filteredPublishedProducts;
    }
}
