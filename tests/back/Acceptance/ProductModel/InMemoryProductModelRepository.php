<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\ArrayCollection;

class InMemoryProductModelRepository implements IdentifiableObjectRepositoryInterface, SaverInterface, ProductModelRepositoryInterface
{
    /** @var ArrayCollection */
    private $productModels;

    public function __construct(array $productModels = [])
    {
        $this->productModels = new ArrayCollection($productModels);
    }

    public function getIdentifierProperties()
    {
        return ['code'];
    }

    public function findOneByIdentifier($identifier)
    {
        return $this->productModels->get($identifier);
    }

    public function save($object, array $options = [])
    {
        if (!$object instanceof ProductModelInterface) {
            throw new \InvalidArgumentException('The object argument should be a ProductModel');
        }

        $this->productModels->set($object->getCode(), $object);
    }

    public function getItemsFromIdentifiers(array $identifiers)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function find($id)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findAll()
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findOneBy(array $criteria)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function getClassName()
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findSiblingsProductModels(ProductModelInterface $productModel): array
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function countRootProductModels(): int
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findChildrenProductModels(ProductModelInterface $productModel): array
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findDescendantProductIdentifiers(ProductModelInterface $productModel): array
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findByIdentifiers(array $codes): array
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findChildrenProducts(ProductModelInterface $productModel): array
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function searchRootProductModelsAfter(?ProductModelInterface $product, int $limit): array
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findSubProductModels(FamilyVariantInterface $familyVariant): array
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findRootProductModels(FamilyVariantInterface $familyVariant): array
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findProductModelsForFamilyVariant(FamilyVariantInterface $familyVariant, ?string $search = null): array
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function searchLastLevelByCode(
        FamilyVariantInterface $familyVariant,
        string $search,
        int $limit,
        int $page = 0
    ): array {
        throw new NotImplementedException(__METHOD__);
    }
}
