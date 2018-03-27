<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Repository\EntityWithFamilyVariantRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Pim\Component\Catalog\Repository\VariantProductRepositoryInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class EntityWithFamilyVariantRepository implements EntityWithFamilyVariantRepositoryInterface
{
    /** @var ProductModelRepositoryInterface */
    protected $productModelRepository;

    /** @var VariantProductRepositoryInterface */
    protected $productRepository;

    /**
     * @param ProductModelRepositoryInterface   $productModelRepository
     * @param VariantProductRepositoryInterface $productRepository
     */
    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        VariantProductRepositoryInterface $productRepository
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findSiblings(EntityWithFamilyVariantInterface $entity): array
    {
        $familyVariant = $entity->getFamilyVariant();

        if (null === $familyVariant || ($entity instanceof ProductModelInterface && $entity->isRootProductModel())) {
            return [];
        }

        if ($entity instanceof VariantProductInterface) {
            return $this->productRepository->findSiblingsProducts($entity);
        }

        if ($entity instanceof ProductModelInterface) {
            return $this->productModelRepository->findSiblingsProductModels($entity);
        }

        return [];
    }
}
