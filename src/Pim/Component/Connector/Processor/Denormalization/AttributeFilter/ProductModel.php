<?php
declare(strict_types=1);

namespace Pim\Component\Connector\Processor\Denormalization\AttributeFilter;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Repository\FamilyVariantRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;

/**
 * Filter the attribute depending on the variant family, we will remove all attributes that don't belong to your level.
 * This is because when product models are exported, they gather the values of their parent.
 * And we should be able to import those export files.
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModel implements AttributeFilterInterface
{
    /** @var FamilyVariantRepositoryInterface */
    private $familyVariantRepository;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $familyVariantRepository
     * @param IdentifiableObjectRepositoryInterface $productModelRepository
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $familyVariantRepository,
        IdentifiableObjectRepositoryInterface $productModelRepository
    ) {
        $this->familyVariantRepository = $familyVariantRepository;
        $this->productModelRepository = $productModelRepository;
    }

    /**
     * @param array $flatProductModel
     *
     * @return array
     */
    public function filter(array $flatProductModel): array
    {
        $familyVariant = $flatProductModel['family_variant'] ?? '';
        // Skip the attribute filtration if there is no family variant, updater/validation will raise error.
        if (empty($familyVariant)) {
            return $flatProductModel;
        }

        $familyVariant = $this->familyVariantRepository->findOneByIdentifier($familyVariant);
        $parent = $flatProductModel['parent'] ?? '';
        if (empty($parent) && null !== $familyVariant) {
            return $this->removeUnknownAttributes($flatProductModel, $familyVariant->getCommonAttributes());
        }

        $parentProductModel = $this->productModelRepository->findOneByIdentifier($parent);
        // Skip the attribute filtration if the parent does not exist, updater/validation will raise error.
        if (null === $parentProductModel) {
            return $flatProductModel;
        }

        $variantAttributeSet = $familyVariant->getVariantAttributeSet($parentProductModel->getVariationLevel() + 1);

        return $this->removeUnknownAttributes($flatProductModel, $variantAttributeSet->getAttributes());
    }

    /**
     * @param array      $flatProductModel
     * @param Collection $familyVariantAttribute
     *
     * @return array
     */
    private function removeUnknownAttributes(array $flatProductModel, Collection $familyVariantAttribute): array
    {
        foreach ($flatProductModel['values'] as $attributeName => $value) {
            $shortAttributeName = explode('-', $attributeName);
            $shortAttributeName = $shortAttributeName[0];

            $belongToFamilyVariant = $familyVariantAttribute->exists(
                function ($key, AttributeInterface $attribute) use ($shortAttributeName) {
                    return $attribute->getCode() === $shortAttributeName;
                }
            );

            if (!$belongToFamilyVariant) {
                unset($flatProductModel['values'][$attributeName]);
            }
        }

        return $flatProductModel;
    }
}
