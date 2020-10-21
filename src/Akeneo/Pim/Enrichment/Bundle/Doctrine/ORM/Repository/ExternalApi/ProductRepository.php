<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ExternalApi\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface as CatalogProductRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRepository extends EntityRepository implements ProductRepositoryInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $productRepository;

    public function __construct(
        EntityManager $em,
        string $className,
        IdentifiableObjectRepositoryInterface $productRepository
    ) {
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
