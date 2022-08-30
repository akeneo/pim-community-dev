<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\EntityRepository;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

/**
 * Product repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRepository extends EntityRepository implements
    ProductRepositoryInterface,
    IdentifiableObjectRepositoryInterface,
    CursorableRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getItemsFromIdentifiers(array $uuids): array
    {
        Assert::allIsInstanceOf($uuids, UuidInterface::class);

        $qb = $this->createQueryBuilder('p')
            ->where('p.uuid IN (:uuids)')
            ->setParameter('uuids', array_map(fn(UuidInterface $uuid) => $uuid->getBytes(), $uuids));

        $query = $qb->getQuery();
        $query->useQueryCache(false);

        $result = $query->execute();

        return $result;
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
        return $this->findOneBy(['identifier' => $identifier]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableAttributeIdsToExport(array $productIds)
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->select('a.id')
            ->innerJoin('p.values', 'v')
            ->innerJoin('v.attribute', 'a')
            ->where($qb->expr()->in('p.id', $productIds))
            ->distinct(true);

        $attributes = $qb->getQuery()->getArrayResult();
        $attributeIds = [];
        foreach ($attributes as $attribute) {
            $attributeIds[] = $attribute['id'];
        }

        return $attributeIds;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsByGroup(GroupInterface $group, $maxResults)
    {
        $products = $this
            ->createQueryBuilder('p')
            ->innerJoin('p.groups', 'g', 'WITH', 'g=:group')
            ->setParameter('group', $group)
            ->getQuery()
            ->setMaxResults($maxResults)
            ->execute();

        return $products;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductCountByGroup(GroupInterface $group)
    {
        $count = $this->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->innerJoin('p.groups', 'g', 'WITH', 'g=:group')
            ->setParameter('group', $group)
            ->getQuery()
            ->getSingleScalarResult();

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function countAll(): int
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)');

        return (int)$qb
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttributeInFamily($productUuid, $attributeCode)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->leftJoin('p.family', 'f')
            ->leftJoin('f.attributes', 'a')
            ->where('p.uuid = :uuid')
            ->andWhere('a.code = :code')
            ->setParameters([
                'uuid' => $productUuid->getBytes(),
                'code' => $attributeCode,
            ])
            ->setMaxResults(1);

        return count($queryBuilder->getQuery()->getArrayResult()) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function searchAfter(?ProductInterface $product, int $limit): array
    {
        $qb = $this->createQueryBuilder('p')
            ->orderBy('p.uuid', 'ASC')
            ->setMaxResults($limit);

        if (null !== $product) {
            $qb->where('p.uuid > :productUuid')
                ->setParameter(':productUuid', $product->getUuid());
        }

        return $qb->getQuery()->execute();
    }
}
