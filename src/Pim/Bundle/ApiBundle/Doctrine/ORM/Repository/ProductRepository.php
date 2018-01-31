<?php

namespace Pim\Bundle\ApiBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Pim\Component\Api\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface as CatalogProductRepositoryInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRepository extends EntityRepository implements ProductRepositoryInterface
{
    /** @var CatalogProductRepositoryInterface */
    protected $productRepository;

    /**
     * @param EntityManager                     $em
     * @param string                            $className
     * @param CatalogProductRepositoryInterface $productRepository
     */
    public function __construct(EntityManager $em, $className, CatalogProductRepositoryInterface $productRepository)
    {
        parent::__construct($em, $em->getClassMetadata($className));

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
    public function getIdentifierProperties()
    {
        return $this->productRepository->getIdentifierProperties();
    }
}
