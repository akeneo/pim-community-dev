<?php
declare(strict_types=1);

namespace Pim\Component\Catalog\ProductModel\Filter;

use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Repository\FamilyVariantRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;

/**
 * Filter data according to attributes defined on the family variant. All
 * attributes that don't belong to the corresponding attribute set will be
 * removed.
 *
 * This is because when product models are exported, they gather the values
 * of their parent, and we should be able to import those export files.
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelAttributeFilter implements AttributeFilterInterface
{
    /** @var FamilyVariantRepositoryInterface */
    private $familyVariantRepository;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $familyVariantRepository
     * @param IdentifiableObjectRepositoryInterface $productModelRepository
     * @param IdentifiableObjectRepositoryInterface $attributeRepository
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $familyVariantRepository,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->familyVariantRepository = $familyVariantRepository;
        $this->productModelRepository = $productModelRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(array $standardProductModel): array
    {
        if (array_key_exists('values', $standardProductModel) && is_array($standardProductModel['values'])) {
            foreach ($standardProductModel['values'] as $code => $value) {
                if (null === $this->attributeRepository->findOneByIdentifier($code)) {
                    throw UnknownPropertyException::unknownProperty($code);
                }
            }
        }

        if (!array_key_exists('code', $standardProductModel)) {
            return $standardProductModel;
        }

        $productModel = $this->productModelRepository->findOneByIdentifier($standardProductModel['code']);
        if (null !== $productModel && !isset($standardProductModel['family_variant'])) {
            $standardProductModel['family_variant'] = $productModel->getFamilyVariant()->getCode();
        }

        if (null !== $productModel && null !== $productModel->getParent() && !array_key_exists('parent', $standardProductModel)) {
            $standardProductModel['parent'] = $productModel->getParent()->getCode();
            $standardProductModel['family_variant'] = $productModel->getFamilyVariant()->getCode();
        }

        $parent = $standardProductModel['parent'] ?? '';
        $familyVariant = $standardProductModel['family_variant'] ?? '';

        $familyVariant = $this->familyVariantRepository->findOneByIdentifier($familyVariant);
        if (empty($parent) && null !== $familyVariant) {
            return $this->keepOnlyAttributes($standardProductModel, $familyVariant->getCommonAttributes());
        }

        $parentProductModel = $this->productModelRepository->findOneByIdentifier($parent);
        // Skip the attribute filtration if the parent does not exist, updater/validation will raise error.
        if (null === $parentProductModel) {
            return $standardProductModel;
        }

        // Family variant field is not mandatory for sub product models.
        if (null === $familyVariant) {
            $familyVariant = $parentProductModel->getFamilyVariant();
        }

        $variantAttributeSet = $familyVariant->getVariantAttributeSet($parentProductModel->getVariationLevel() + 1);

        if (null === $variantAttributeSet) {
            return $standardProductModel;
        }

        return $this->keepOnlyAttributes($standardProductModel, $variantAttributeSet->getAttributes());
    }

    /**
     * @param array      $flatProductModel
     * @param Collection $attributesToKeep
     *
     * @return array
     */
    private function keepOnlyAttributes(array $flatProductModel, Collection $attributesToKeep): array
    {
        foreach ($flatProductModel['values'] as $attributeName => $value) {
            $shortAttributeName = explode('-', $attributeName);
            $shortAttributeName = $shortAttributeName[0];

            $keepedAttributeCodes = $attributesToKeep->exists(
                function ($key, AttributeInterface $attribute) use ($shortAttributeName) {
                    return $attribute->getCode() === $shortAttributeName;
                }
            );

            if (!$keepedAttributeCodes) {
                unset($flatProductModel['values'][$attributeName]);
            }
        }

        return $flatProductModel;
    }
}
