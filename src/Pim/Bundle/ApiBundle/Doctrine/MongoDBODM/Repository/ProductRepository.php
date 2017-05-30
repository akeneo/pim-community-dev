<?php

namespace Pim\Bundle\ApiBundle\Doctrine\MongoDBODM\Repository;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Pim\Component\Api\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRepository extends DocumentRepository implements ProductRepositoryInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $productRepository;

    /**
     * @param DocumentManager                       $em
     * @param string                                $className
     * @param IdentifiableObjectRepositoryInterface $productRepository
     */
    public function __construct(
        DocumentManager $em,
        $className,
        IdentifiableObjectRepositoryInterface $productRepository
    ) {
        parent::__construct($em, $em->getUnitOfWork(), $em->getClassMetadata($className));

        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        return $this->productRepository->findOneByIdentifier($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function searchAfterOffset(ProductQueryBuilderInterface $pqb, $limit, $offset)
    {
        $qb = clone $pqb->getQueryBuilder();

        return $qb
            ->sort('_id', 'ASC')
            ->limit($limit)
            ->skip($offset)
            ->getQuery()
            ->execute();
    }

    /**
     * @inheritDoc
     */
    public function searchAfterIdentifier(ProductQueryBuilderInterface $pqb, $limit, $searchAfterIdentifier)
    {
        $qb = clone $pqb->getQueryBuilder();

        if (null !== $searchAfterIdentifier) {
            $qb->field('_id')->gt($searchAfterIdentifier);
        }

        return $qb
            ->sort('_id', 'ASC')
            ->limit($limit)
            ->getQuery()
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function count(ProductQueryBuilderInterface $pqb)
    {
        $qb = clone $pqb->getQueryBuilder();

        return (int) $qb->select('_id')
            ->hydrate(false)
            ->getQuery()
            ->execute()
            ->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return $this->productRepository->getIdentifierProperties();
    }
}
