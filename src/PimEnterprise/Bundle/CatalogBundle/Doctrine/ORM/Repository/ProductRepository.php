<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Component\Catalog\Security\Factory\FilteredEntityFactory;
use PimEnterprise\Component\Security\Authorization\DenyNotGrantedCategorizedEntity;

/**
 * Decorates CE product repository to apply permissions.
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ProductRepository extends EntityRepository implements
    ProductRepositoryInterface,
    IdentifiableObjectRepositoryInterface,
    CursorableRepositoryInterface
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var FilteredEntityFactory */
    private $filteredProductFactory;

    /** @var DenyNotGrantedCategorizedEntity */
    private $denyNotGrantedCategorizedEntity;

    /**
     * @param EntityManagerInterface          $em
     * @param ProductRepositoryInterface      $productRepository
     * @param FilteredEntityFactory           $filteredProductFactory
     * @param DenyNotGrantedCategorizedEntity $denyNotGrantedCategorizedEntity
     * @param string                          $entityName
     */
    public function __construct(
        EntityManagerInterface $em,
        ProductRepositoryInterface $productRepository,
        FilteredEntityFactory $filteredProductFactory,
        DenyNotGrantedCategorizedEntity $denyNotGrantedCategorizedEntity,
        string $entityName
    ) {
        parent::__construct($em, $em->getClassMetadata($entityName));

        $this->productRepository = $productRepository;
        $this->filteredProductFactory = $filteredProductFactory;
        $this->denyNotGrantedCategorizedEntity = $denyNotGrantedCategorizedEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        $product = $this->productRepository->find($id);
        if (null === $product) {
            return null;
        }

        return $this->getFilteredProduct($product);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        $products = $this->productRepository->findAll();

        return $this->getFilteredProducts($products);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $products = $this->productRepository->findBy($criteria, $orderBy, $limit, $offset);

        return $this->getFilteredProducts($products);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        $product = $this->productRepository->findOneBy($criteria);
        if (null === $product) {
            return null;
        }

        return $this->getFilteredProduct($product);
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableAttributeIdsToExport(array $productIds)
    {
        return $this->productRepository->getAvailableAttributeIdsToExport($productIds);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);
        if (null === $product) {
            return null;
        }

        return $this->getFilteredProduct($product);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsByGroup(GroupInterface $group, $maxResults)
    {
        return $this->productRepository->getProductsByGroup($group, $maxResults);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductCountByGroup(GroupInterface $group)
    {
        return $this->productRepository->getProductCountByGroup($group);
    }

    /**
     * {@inheritdoc}
     */
    public function countAll()
    {
        return $this->productRepository->countAll();
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttributeInFamily($productId, $attributeCode)
    {
        return $this->productRepository->hasAttributeInFamily($productId, $attributeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsFromIdentifiers(array $identifiers)
    {
        $products = $this->productRepository->getItemsFromIdentifiers($identifiers);

        return $this->getFilteredProducts($products);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return $this->productRepository->getIdentifierProperties();
    }

    /**
     * {@inheritdoc}
     */
    public function searchAfter(?ProductInterface $product, int $limit): array
    {
        $products = $this->productRepository->searchAfter($product, $limit);

        return $this->getFilteredProducts($products);
    }

    /**
     * Get a single product filtered with only granted data
     *
     * @param ProductInterface $product
     *
     * @return ProductInterface
     */
    private function getFilteredProduct(ProductInterface $product): ProductInterface
    {
        $this->denyNotGrantedCategorizedEntity->denyIfNotGranted($product);

        return $this->filteredProductFactory->create($product);
    }

    /**
     * Get products filtered with only granted data
     *
     * @param ProductInterface[] $products
     *
     * @return array
     */
    private function getFilteredProducts(array $products): array
    {
        $filteredProducts = [];
        foreach ($products as $product) {
            $this->denyNotGrantedCategorizedEntity->denyIfNotGranted($product);
            $filteredProducts[] = $this->filteredProductFactory->create($product);
        }

        return $filteredProducts;
    }
}
