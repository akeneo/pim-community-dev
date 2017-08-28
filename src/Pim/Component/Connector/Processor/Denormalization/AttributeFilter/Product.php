<?php
declare(strict_types=1);

namespace Pim\Component\Connector\Processor\Denormalization\AttributeFilter;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;

/**
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Product implements AttributeFilterInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $productModelRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $familyRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $productModelRepository
     * @param IdentifiableObjectRepositoryInterface $familyRepository
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $productModelRepository,
        IdentifiableObjectRepositoryInterface $familyRepository
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->familyRepository = $familyRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(array $flatProduct): array
    {
        if (isset($flatProduct['parent'])) {
            $parentProductModel = $this->productModelRepository->findOneByIdentifier($flatProduct['parent']);
            $attributeSet = $parentProductModel->getFamilyVariant()
                ->getVariantAttributeSet($parentProductModel->getVariationLevel() + 1);
            $attributes = $attributeSet->getAttributes();

            return $this->removeUnknownAttributes($flatProduct, $attributes);
        }

        if (isset($flatProduct['family'])) {
            if (null !== $family = $this->familyRepository->findOneByIdentifier($flatProduct['family'])) {
                $attributes = $family->getAttributes();

                return $this->removeUnknownAttributes($flatProduct, $attributes);
            }
        }

        return $flatProduct;
    }

    /**
     * @param array      $flatProduct
     * @param Collection $attributes
     *
     * @return array
     */
    private function removeUnknownAttributes(array $flatProduct, Collection $attributes): array
    {
        foreach ($flatProduct['values'] as $attributeName => $value) {
            $belongToFamily = $attributes->exists(
                function ($key, AttributeInterface $attribute) use ($attributeName) {
                    return $attribute->getCode() === (string)$attributeName;
                }
            );

            if (!$belongToFamily) {
                unset($flatProduct['values'][$attributeName]);
            }
        }

        return $flatProduct;
    }
}
