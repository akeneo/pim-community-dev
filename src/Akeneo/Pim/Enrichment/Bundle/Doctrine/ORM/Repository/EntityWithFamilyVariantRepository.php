<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\EntityWithFamilyVariantRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\VariantProductRepositoryInterface;

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

        if ($entity instanceof ProductInterface && $entity->isVariant()) {
            return $this->productRepository->findSiblingsProducts($entity);
        }

        if ($entity instanceof ProductModelInterface) {
            return $this->productModelRepository->findSiblingsProductModels($entity);
        }

        return [];
    }
}
