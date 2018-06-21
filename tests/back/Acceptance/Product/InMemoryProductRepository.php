<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Product;

use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Doctrine\Common\Collections\ArrayCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;

class InMemoryProductRepository implements
    IdentifiableObjectRepositoryInterface,
    SaverInterface,
    ProductRepositoryInterface,
    CursorableRepositoryInterface
{
    /** @var ArrayCollection */
    private $products;

    public function __construct(array $products = [])
    {
        $this->products = new ArrayCollection($products);
    }

    public function getIdentifierProperties()
    {
        return ['identifier'];
    }

    public function findOneByIdentifier($identifier)
    {
        return $this->products->get($identifier);
    }

    public function save($object, array $options = [])
    {
        if (!$object instanceof ProductInterface) {
            throw new \InvalidArgumentException('The object argument should be a product');
        }

        $this->products->set($object->getIdentifier(), $object);
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

    public function getAvailableAttributeIdsToExport(array $productIds)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function getProductsByGroup(GroupInterface $group, $maxResults)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function getProductCountByGroup(GroupInterface $group)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function countAll(): int
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function hasAttributeInFamily($productId, $attributeCode)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function searchAfter(?ProductInterface $product, int $limit): array
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function getItemsFromIdentifiers(array $identifiers)
    {
        throw new NotImplementedException(__METHOD__);
    }
}
