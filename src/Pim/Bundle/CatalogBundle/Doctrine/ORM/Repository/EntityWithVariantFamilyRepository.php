<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Repository\EntityWithVariantFamilyRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class EntityWithVariantFamilyRepository implements EntityWithVariantFamilyRepositoryInterface
{
    /** @var ProductModelRepositoryInterface */
    protected $productModelRepository;

    /**
     * @param ProductModelRepositoryInterface $productModelRepository
     */
    public function __construct(ProductModelRepositoryInterface $productModelRepository)
    {
        $this->productModelRepository = $productModelRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findSiblings(EntityWithFamilyVariantInterface $entity): array
    {
        $familyVariant = $entity->getFamilyVariant();

        if (null === $familyVariant || (null !== $familyVariant && $entity->isRootVariation())) {
            return [];
        }

        if ($entity instanceof ProductInterface) {
            // TODO: link between product & family variant
        }

        if ($entity instanceof ProductModelInterface) {
            return $this->productModelRepository->findSiblingsProductModels($entity);
        }

        return [];
    }
}
