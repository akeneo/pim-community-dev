<?php
declare(strict_types=1);

namespace Pim\Bundle\ApiBundle\Doctrine\ORM\Repository;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface as CatalogProductModelRepositoryInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelRepository extends EntityRepository implements IdentifiableObjectRepositoryInterface
{
    /** @var CatalogProductModelRepositoryInterface */
    protected $productModelRepository;

    public function __construct(
        EntityManager $em,
        $className,
        CatalogProductModelRepositoryInterface $productModelRepository
    ) {
        parent::__construct($em, $em->getClassMetadata($className));

        $this->productModelRepository = $productModelRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return $this->productModelRepository->getIdentifierProperties();
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        return $this->productModelRepository->findOneByIdentifier($identifier);
    }
}
