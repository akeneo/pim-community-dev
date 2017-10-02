<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\ProductModel;

use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;

/**
 * For a given ProductModel, this class retrieves the ValueInterface of its attribute as image,
 * defined in its family. This value can come from ascendant or descendant entities (from product models above
 * or variant product below).
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductModelImageAsLabel
{
    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @param ProductModelRepositoryInterface $productModelRepository
     * @param ProductRepositoryInterface      $productRepository
     */
    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        ProductRepositoryInterface $productRepository
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
    public function getImage(ProductModelInterface $productModel): ?ValueInterface
    {
        $attributeAsImage = $productModel->getFamily()->getAttributeAsImage();
        $attributeSets = $productModel->getFamilyVariant()->getVariantAttributeSets();
        $levelContainingAttribute = 0;

        foreach ($attributeSets as $attributeSet) {
            if ($attributeSet->getAttributes()->contains($attributeAsImage)) {
                $levelContainingAttribute = $attributeSet->getLevel();
            }
        }

        if ($levelContainingAttribute <= $productModel->getLevel()) {
            return $productModel->getImage();
        }

        $currentLevel = $productModel->getLevel();
        $entity = $productModel;

        do {
            $modelChild = current($this->productModelRepository->findBy(
                ['parent' => $entity],
                ['created' => 'DESC', 'code' => 'ASC'],
                1
            ));

            $productChild = current($this->productRepository->findBy(
                ['parent' => $entity],
                ['created' => 'DESC', 'identifier' => 'ASC'],
                1
            ));

            if (false !== $modelChild) {
                $entity = $modelChild;
            }

            if (false !== $productChild) {
                $entity = $productChild;
            }

            if (false === $modelChild && false === $productChild) {
                return null;
            }

            $currentLevel++;
        } while ($currentLevel < $levelContainingAttribute);

        return $entity->getImage();
    }
}
