<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\VariantProductRepositoryInterface;

/**
 * For a given ProductModel, this class retrieves the ValueInterface of its attribute as image,
 * defined in its family. This value can come from ascendant or descendant entities (from product models above
 * or variant product below).
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ImageAsLabel
{
    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var VariantProductRepositoryInterface */
    private $productRepository;

    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        VariantProductRepositoryInterface $productRepository
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * Get the closest attribute as image value for the given $productModel. It can be its own value,
     * or the value of one of its parent or children.
     *
     * @param ProductModelInterface $productModel
     *
     * @return null|ValueInterface
     */
    public function value(ProductModelInterface $productModel): ?ValueInterface
    {
        $attributeAsImage = $productModel->getFamily()->getAttributeAsImage();
        $attributeSets = $productModel->getFamilyVariant()->getVariantAttributeSets();
        $levelContainingAttribute = 0;

        foreach ($attributeSets as $attributeSet) {
            if ($attributeSet->getAttributes()->contains($attributeAsImage)) {
                $levelContainingAttribute = $attributeSet->getLevel();
            }
        }

        if ($levelContainingAttribute <= $productModel->getVariationLevel()) {
            return $productModel->getImage();
        }

        $currentLevel = $productModel->getVariationLevel();
        $entity = $productModel;

        do {
            $entity = $this->findFirstCreatedEntityWithFamilyVariantByParent($entity);
            if (null === $entity) {
                return null;
            }

            $currentLevel++;
        } while ($currentLevel < $levelContainingAttribute);

        return $entity->getImage();
    }

    /**
     * @return ProductModelInterface | ProductInterface | null
     */
    private function findFirstCreatedEntityWithFamilyVariantByParent(ProductModelInterface $productModel)
    {
        $productChild = $this->productRepository->findLastCreatedByParent($productModel);
        if (null !== $productChild) {
            return $productChild;
        }

        return $this->productModelRepository->findFirstCreatedVariantProductModel($productModel);
    }
}
