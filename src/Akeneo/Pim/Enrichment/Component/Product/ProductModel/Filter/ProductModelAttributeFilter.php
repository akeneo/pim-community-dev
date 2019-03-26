<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter;

use Akeneo\Pim\Enrichment\Bundle\Sql\GetFamilyAttributeCodes;
use Akeneo\Pim\Enrichment\Bundle\Sql\GetVariantAttributeSetAttributeCodes;
use Akeneo\Pim\Enrichment\Bundle\Sql\GetVariantAttributeSetAxesCodes;
use Akeneo\Pim\Enrichment\Bundle\Sql\LruArrayAttributeRepository;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyVariantRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;

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

    /** @var LruArrayAttributeRepository */
    private $attributeRepository;

    /** @var GetFamilyAttributeCodes */
    private $getFamilyAttributeCodes;

    /** @var GetVariantAttributeSetAttributeCodes */
    private $getVariantAttributeSetAttributeCodes;

    /** @var GetVariantAttributeSetAxesCodes */
    private $getVariantAttributeSetAxesCodes;

    /**
     * @param FamilyVariantRepositoryInterface $familyVariantRepository
     * @param ProductModelRepositoryInterface $productModelRepository
     * @param LruArrayAttributeRepository $attributeRepository
     * @param GetFamilyAttributeCodes $getFamilyAttributeCodes
     * @param GetVariantAttributeSetAttributeCodes $getVariantAttributeSetAttributeCodes
     * @param GetVariantAttributeSetAxesCodes $getVariantAttributeSetAxesCodes
     */
    public function __construct(
        FamilyVariantRepositoryInterface $familyVariantRepository,
        ProductModelRepositoryInterface $productModelRepository,
        LruArrayAttributeRepository $attributeRepository,
        GetFamilyAttributeCodes $getFamilyAttributeCodes,
        GetVariantAttributeSetAttributeCodes $getVariantAttributeSetAttributeCodes,
        GetVariantAttributeSetAxesCodes $getVariantAttributeSetAxesCodes
    ) {
        $this->familyVariantRepository = $familyVariantRepository;
        $this->productModelRepository = $productModelRepository;
        $this->attributeRepository = $attributeRepository;
        $this->getFamilyAttributeCodes = $getFamilyAttributeCodes;
        $this->getVariantAttributeSetAttributeCodes = $getVariantAttributeSetAttributeCodes;
        $this->getVariantAttributeSetAxesCodes = $getVariantAttributeSetAxesCodes;
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

        /** @var FamilyVariantInterface $familyVariant */
        $familyVariant = $this->familyVariantRepository->findOneByIdentifier($familyVariant);
        if (empty($parent) && null !== $familyVariant) {
            $commonAttributes = array_diff(
                $this->getFamilyAttributeCodes->execute($familyVariant->getFamily()->getCode()),
                $this->getVariantAttributeSetAttributeCodes->execute($familyVariant->getCode(), 1),
                $this->getVariantAttributeSetAttributeCodes->execute($familyVariant->getCode(), 2),
                $this->getVariantAttributeSetAxesCodes->execute($familyVariant->getCode(), 1),
                $this->getVariantAttributeSetAxesCodes->execute($familyVariant->getCode(), 2)
            );
            return $this->keepOnlyAttributes($standardProductModel, $commonAttributes);
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

        return $this->keepOnlyAttributes($standardProductModel, array_merge(
            $this->getVariantAttributeSetAttributeCodes->execute($familyVariant->getCode(), $variantAttributeSet->getLevel()),
            $this->getVariantAttributeSetAxesCodes->execute($familyVariant->getCode(), $variantAttributeSet->getLevel())
        ));
    }

    /**
     * @param array $flatProductModel
     * @param array $attributeCodesToKeep
     *
     * @return array
     */
    private function keepOnlyAttributes(array $flatProductModel, array $attributeCodesToKeep): array
    {
        foreach ($flatProductModel['values'] as $attributeName => $value) {
            $shortAttributeName = explode('-', $attributeName);
            $shortAttributeName = $shortAttributeName[0];

            if (!in_array($shortAttributeName, $attributeCodesToKeep)) {
                unset($flatProductModel['values'][$attributeName]);
            }
        }

        return $flatProductModel;
    }
}
