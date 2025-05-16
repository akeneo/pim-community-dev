<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

class InMemoryProductRepository implements
    IdentifiableObjectRepositoryInterface,
    SaverInterface,
    ProductRepositoryInterface,
    CursorableRepositoryInterface,
    BulkSaverInterface
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
        foreach ($this->products as $product) {
            if ($product->getIdentifier() === $identifier) {
                return $product;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function save($product, array $options = [])
    {
        if (!$product instanceof ProductInterface) {
            throw new \InvalidArgumentException('The object argument should be a product');
        }

        $this->products->set($product->getUuid()->toString(), $product);
    }

    public function saveAll(array $products, array $options = [])
    {
        foreach ($products as $product) {
            $this->save($product, $options);
        }
    }

    public function find($uuid)
    {
        if ($uuid instanceof UuidInterface) {
            $uuid = $uuid->toString();
        }

        return $this->products->get($uuid);
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
        return $this->products->filter(fn (ProductInterface $product) => $product->getIdentifier() === $criteria['identifier'])->toArray();
    }

    public function findOneBy(array $criteria)
    {
        Assert::count($criteria, 1);
        $criteriaKey = \current(\array_keys($criteria));
        Assert::inArray($criteriaKey, ['id', 'uuid'], 'The criteria only implements `id` and `uuid`');
        $criteriaValue = \current($criteria);
        if ($criteriaValue instanceof UuidInterface) {
            $criteriaValue = $criteriaValue->toString();
        }

        foreach ($this->products as $product) {
            $productValue = 'uuid' === $criteriaKey ? $product->getUuid() : $product->getId();
            if ($productValue instanceof UuidInterface) {
                $productValue = $productValue->toString();
            }
            if ($productValue === $criteriaValue) {
                return $product;
            }
        }

        return null;
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

    public function hasAttributeInFamily($productUuidOrId, $attributeCode)
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

    /**
     * {@inheritdoc}
     */
    public function getItemsFromUuids(array $uuids): array
    {
        return $this->products->filter(
            static fn (ProductInterface $product): bool => \in_array($product->getUuid()->toString(), $uuids)
        )->getValues();
    }
}
