<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\ArrayCollection;

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

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['identifier'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        return $this->products->get($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function save($product, array $options = [])
    {
        if (!$product instanceof ProductInterface) {
            throw new \InvalidArgumentException('The object argument should be a product');
        }

        // We need to emulate the database coupling (id is generated by the database)
        if (null === $product->getId()) {
            $product->setId(mt_rand());
        }
        $this->products->set($product->getIdentifier(), $product);
    }

    public function find($id)
    {
        $product = $this->products->filter(
            function (ProductInterface $product) use ($id) {
                return $product->getId() === $id;
            })->first();

        return (false === $product) ? null : $product;
    }

    public function findAll()
    {
        return $this->products->toArray();
    }

    /**
     * We implement this method for onboarder v1 because we need it for an event subscriber
     * and there is some integration tests that are using inmemory implem
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $products = [];
        foreach ($this->products as $product) {
            $keepThisProduct= true;
            foreach ($criteria as $value) {
                if ($product->getIdentifier() !== $value) {
                    $keepThisProduct = false;
                    break;
                }
            }
            if ($keepThisProduct) {
                $products[] = $product;
            }
        }
        return $products;
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
        $items = [];

        foreach ($identifiers as $identifier) {
            $items[] = $this->findOneByIdentifier($identifier);
        }

        return $items;
    }
}
