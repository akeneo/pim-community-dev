<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

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
    private ?string $mainIdentifier = null;

    /**
     * {@inheritdoc}
     */
    public function getItemsFromIdentifiers(array $identifiers)
    {
        $mainIdentifier = $this->getMainIdentifier();
        $identifiers = \array_map(
            static fn (string $identifier): string => \sprintf('%s#%s', $mainIdentifier, $identifiers),
            $identifiers
        );
        $uuids = $this->getEntityManager()->getConnection()->fetchFirstColumn(
            <<<SQL
            SELECT BIN_TO_UUID(product_uuid) 
            FROM pim_catalog_product_identifiers
            WHERE JSON_OVERLAPS(JSON_ARRAY(:identifiers), identifiers)
            SQL,
            ['identifiers' => $identifiers],
            ['identifiers' => Connection::PARAM_STR_ARRAY]
        );

        return $this->findBy(['uuid' => $uuids]);
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsFromUuids(array $uuids): array
    {
        if ([] === $uuids) {
            return [];
        }

        $uuidsAsBytes = [];
        foreach ($uuids as $uuid) {
            if (Uuid::isValid($uuid)) {
                $uuidsAsBytes[] = Uuid::fromString($uuid)->getBytes();
            } else {
                $uuidsAsBytes[] = $uuid;
            }
        }

        return $this->findBy(['uuid' => $uuidsAsBytes]);
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
        if (null === $identifier) {
            return null;
        }
        $uuid = $this->getEntityManager()->getConnection()->fetchOne(
            <<<SQL
                SELECT BIN_TO_UUID(product_uuid)
                FROM pim_catalog_product_identifiers
                WHERE :identifier MEMBER OF(identifiers)
            SQL,
            ['identifier' => \sprintf('%s#%s', $this->getMainIdentifier(), $identifier)]
        );

        return $uuid ? $this->find($uuid) : null;
    }

    public function findOneByUuid(UuidInterface $uuid): ?ProductInterface
    {
        return $this->find($uuid);
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

        return (int) $qb
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
        ;

        if (null !== $product) {
            $qb->where('p.uuid > :productUuid')
                ->setParameter(':productUuid', $product->getUuid());
        }

        return $qb->getQuery()->execute();
    }

    private function getMainIdentifier(): string
    {
        if (null === $this->mainIdentifier) {
            $this->mainIdentifier = $this->getEntityManager()->getConnection()->fetchOne(
                'SELECT code FROM pim_catalog_attribute WHERE main_identifier IS TRUE;'
            );
        }

        return $this->mainIdentifier;
    }
}
