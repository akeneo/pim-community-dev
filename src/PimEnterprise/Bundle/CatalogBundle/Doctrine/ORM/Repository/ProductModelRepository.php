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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use PimEnterprise\Component\Catalog\Security\Factory\FilteredEntityFactory;
use PimEnterprise\Component\Security\Authorization\DenyNotGrantedCategorizedEntity;

/**
 * Decorates CE product model repository to apply permissions.
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ProductModelRepository extends EntityRepository implements ProductModelRepositoryInterface
{
    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var FilteredEntityFactory */
    private $filteredProductFactory;

    /** @var FilteredEntityFactory */
    private $filteredProductModelFactory;

    /** @var DenyNotGrantedCategorizedEntity */
    private $denyNotGrantedCategorizedEntity;

    /**
     * @param EntityManagerInterface          $em
     * @param ProductModelRepositoryInterface $productModelRepository
     * @param FilteredEntityFactory           $filteredProductModelFactory
     * @param FilteredEntityFactory           $filteredProductFactory
     * @param DenyNotGrantedCategorizedEntity $denyNotGrantedCategorizedEntity
     * @param string                          $entityName
     */
    public function __construct(
        EntityManagerInterface $em,
        ProductModelRepositoryInterface $productModelRepository,
        FilteredEntityFactory $filteredProductModelFactory,
        FilteredEntityFactory $filteredProductFactory,
        DenyNotGrantedCategorizedEntity $denyNotGrantedCategorizedEntity,
        string $entityName
    ) {
        parent::__construct($em, $em->getClassMetadata($entityName));

        $this->productModelRepository = $productModelRepository;
        $this->filteredProductFactory = $filteredProductFactory;
        $this->filteredProductModelFactory = $filteredProductModelFactory;
        $this->denyNotGrantedCategorizedEntity = $denyNotGrantedCategorizedEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsFromIdentifiers(array $identifiers)
    {
        $productModels = $this->productModelRepository->getItemsFromIdentifiers($identifiers);

        return $this->getFilteredProductModels($productModels);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return $this->productModelRepository->getIdentifierProperties();
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        $productModel = $this->productModelRepository->findOneByIdentifier($identifier);
        if (null === $productModel) {
            return null;
        }

        return $this->getFilteredProductModel($productModel);
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        $productModel = $this->productModelRepository->find($id);
        if (null === $productModel) {
            return  null;
        }

        return $this->getFilteredProductModel($productModel);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        $productModels = $this->productModelRepository->findAll();

        return $this->getFilteredProductModels($productModels);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $productModels = $this->productModelRepository->findBy($criteria, $orderBy, $limit, $offset);

        return $this->getFilteredProductModels($productModels);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        $productModel = $this->productModelRepository->findOneBy($criteria);
        if (null === $productModel) {
            return null;
        }

        return $this->getFilteredProductModel($productModel);
    }

    /**
     * {@inheritdoc}
     */
    public function findSiblingsProductModels(ProductModelInterface $productModel): array
    {
        $productModels = $this->productModelRepository->findSiblingsProductModels($productModel);

        return $this->getFilteredProductModels($productModels);
    }

    /**
     * {@inheritdoc}
     */
    public function countRootProductModels(): int
    {
        return $this->productModelRepository->countRootProductModels();
    }

    /**
     * {@inheritdoc}
     */
    public function findChildrenProductModels(ProductModelInterface $productModel): array
    {
        $productModels = $this->productModelRepository->findChildrenProductModels($productModel);

        return $this->getFilteredProductModels($productModels);
    }

    /**
     * {@inheritdoc}
     */
    public function findDescendantProductIdentifiers(ProductModelInterface $productModel): array
    {
        return $this->productModelRepository->findDescendantProductIdentifiers($productModel);
    }

    /**
     * {@inheritdoc}
     */
    public function findByIdentifiers(array $codes): array
    {
        $productModels = $this->productModelRepository->findByIdentifiers($codes);

        return $this->getFilteredProductModels($productModels);
    }

    /**
     * {@inheritdoc}
     */
    public function findChildrenProducts(ProductModelInterface $productModel): array
    {
        $products = $this->productModelRepository->findChildrenProducts($productModel);

        return $this->getFilteredProducts($products);
    }

    /**
     * {@inheritdoc}
     */
    public function searchRootProductModelsAfter(?ProductModelInterface $product, int $limit): array
    {
        $productModels = $this->productModelRepository->searchRootProductModelsAfter($product, $limit);

        return $this->getFilteredProductModels($productModels);
    }

    /**
     * {@inheritdoc}
     */
    public function findSubProductModels(FamilyVariantInterface $familyVariant): array
    {
        $productModels = $this->productModelRepository->findSubProductModels($familyVariant);

        return $this->getFilteredProductModels($productModels);
    }

    /**
     * {@inheritdoc}
     */
    public function findRootProductModels(FamilyVariantInterface $familyVariant): array
    {
        $productModels = $this->productModelRepository->findRootProductModels($familyVariant);

        return $this->getFilteredProductModels($productModels);
    }

    /**
     * {@inheritdoc}
     */
    public function searchLastLevelByCode(FamilyVariantInterface $familyVariant, string $search, int $limit, int $page = 0): array
    {
        $productModels = $this->productModelRepository->searchLastLevelByCode($familyVariant, $search, $limit, $page);

        return $this->getFilteredProductModels($productModels);
    }

    /**
     * Get a single product model filtered with only granted data
     *
     * @param ProductModelInterface $productModel
     *
     * @return ProductModelInterface
     */
    private function getFilteredProductModel(ProductModelInterface $productModel): ProductModelInterface
    {
        $this->denyNotGrantedCategorizedEntity->denyIfNotGranted($productModel);

        return $this->filteredProductModelFactory->create($productModel);
    }

    /**
     * Get product models filtered with only granted data
     *
     * @param ProductModelInterface[] $productModels
     *
     * @return array
     */
    private function getFilteredProductModels(array $productModels): array
    {
        $filteredProductModels = [];
        foreach ($productModels as $productModel) {
            $this->denyNotGrantedCategorizedEntity->denyIfNotGranted($productModel);
            $filteredProductModels[] = $this->filteredProductModelFactory->create($productModel);
        }

        return $filteredProductModels;
    }

    /**
     * Get product filtered with only granted data
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
