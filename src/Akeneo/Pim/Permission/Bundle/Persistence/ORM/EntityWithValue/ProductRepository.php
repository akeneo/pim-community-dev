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

namespace Akeneo\Pim\Permission\Bundle\Persistence\ORM\EntityWithValue;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Permission\Component\Authorization\DenyNotGrantedCategorizedEntity;
use Akeneo\Pim\Permission\Component\Factory\FilteredEntityFactory;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

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
    public function find($id, $lockMode = null, $lockVersion = null)
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
    public function findOneBy(array $criteria, array $orderBy = null)
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

    public function findOneByUuid(UuidInterface $uuid): ?ProductInterface
    {
        Assert::methodExists($this->productRepository, 'findOneByUuid');
        $product = $this->productRepository->findOneByUuid($uuid);
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
    public function countAll(): int
    {
        return $this->productRepository->countAll();
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttributeInFamily($productUuidOrId, $attributeCode)
    {
        return $this->productRepository->hasAttributeInFamily($productUuidOrId, $attributeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsFromIdentifiers(array $identifiers)
    {
        Assert::implementsInterface($this->productRepository, CursorableRepositoryInterface::class);
        $products = $this->productRepository->getItemsFromIdentifiers($identifiers);

        return $this->getFilteredProducts($products);
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsFromUuids(array $uuids): array
    {
        $products = $this->productRepository->getItemsFromUuids($uuids);

        return $this->getFilteredProducts($products);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        Assert::implementsInterface($this->productRepository, IdentifiableObjectRepositoryInterface::class);

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

        $product = $this->filteredProductFactory->create($product);
        $product->cleanup();

        return $product;
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
            $filteredProduct = $this->filteredProductFactory->create($product);
            $filteredProduct->cleanup();
            $filteredProducts[] = $filteredProduct;
        }

        return $filteredProducts;
    }
}
