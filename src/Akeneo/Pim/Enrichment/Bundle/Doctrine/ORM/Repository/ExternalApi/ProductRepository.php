<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ExternalApi\ProductRepositoryInterface;
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
    public function __construct(
        EntityManager $em,
        string $className,
        protected IdentifiableObjectRepositoryInterface $productRepository
    ) {
        parent::__construct($em, $em->getClassMetadata($className));
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier): ?ProductInterface
    {
        return $this->productRepository->findOneByIdentifier($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties(): array
    {
        return $this->productRepository->getIdentifierProperties();
    }
}
