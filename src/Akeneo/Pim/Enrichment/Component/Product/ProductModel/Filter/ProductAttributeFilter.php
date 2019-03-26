<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter;

use Akeneo\Pim\Enrichment\Bundle\Sql\GetFamilyAttributeCodes;
use Akeneo\Pim\Enrichment\Bundle\Sql\GetVariantAttributeSetAttributeCodes;
use Akeneo\Pim\Enrichment\Bundle\Sql\GetVariantAttributeSetAxesCodes;
use Akeneo\Pim\Enrichment\Bundle\Sql\LruArrayAttributeRepository;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

/**
 * Filter data according to attributes defined on the family (for the products)
 * or on the family variant (variant product). All attributes that don't belong
 * to the corresponding family (product) or attribute set (variant product) will
 * be removed.
 *
 * This is because when variant products are exported, they gather the values
 * of their parent, and we should be able to import those export files.
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAttributeFilter implements AttributeFilterInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $productModelRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $familyRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $productRepository;

    /** @var LruArrayAttributeRepository */
    private $attributeRepository;

    /** @var GetFamilyAttributeCodes */
    private $getFamilyAttributeCodes;

    /** @var GetVariantAttributeSetAttributeCodes */
    private $getVariantAttributeSetAttributeCodes;

    /** @var GetVariantAttributeSetAxesCodes */
    private $getVariantAttributeSetAxesCodes;

    /**
     * @param IdentifiableObjectRepositoryInterface $productModelRepository
     * @param IdentifiableObjectRepositoryInterface $familyRepository
     * @param IdentifiableObjectRepositoryInterface $productRepository
     * @param LruArrayAttributeRepository $attributeRepository
     * @param GetFamilyAttributeCodes $getFamilyAttributeCodes
     * @param GetVariantAttributeSetAttributeCodes $getVariantAttributeSetAttributeCodes
     * @param GetVariantAttributeSetAxesCodes $getVariantAttributeSetAxesCodes
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $productModelRepository,
        IdentifiableObjectRepositoryInterface $familyRepository,
        IdentifiableObjectRepositoryInterface $productRepository,
        LruArrayAttributeRepository $attributeRepository,
        GetFamilyAttributeCodes $getFamilyAttributeCodes,
        GetVariantAttributeSetAttributeCodes $getVariantAttributeSetAttributeCodes,
        GetVariantAttributeSetAxesCodes $getVariantAttributeSetAxesCodes
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->familyRepository = $familyRepository;
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
        $this->getFamilyAttributeCodes = $getFamilyAttributeCodes;
        $this->getVariantAttributeSetAttributeCodes = $getVariantAttributeSetAttributeCodes;
        $this->getVariantAttributeSetAxesCodes = $getVariantAttributeSetAxesCodes;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(array $standardProduct): array
    {
        if (!array_key_exists('identifier', $standardProduct)) {
            throw new MissingOptionsException('The "identifier" key is missing');
        }

        if (array_key_exists('values', $standardProduct) && is_array($standardProduct['values'])) {
            foreach ($standardProduct['values'] as $code => $value) {
                if (null === $this->attributeRepository->findOneByIdentifier($code)) {
                    throw UnknownPropertyException::unknownProperty($code);
                }
            }
        }

        $product = $this->productRepository->findOneByIdentifier($standardProduct['identifier']);
        if (null !== $product && $product->isVariant() && null !== $product->getParent()
            && !array_key_exists('parent', $standardProduct)) {
            $standardProduct['parent'] = $product->getParent()->getCode();
        }

        if (isset($standardProduct['parent']) && '' === $standardProduct['parent']) {
            $standardProduct['parent'] = null;
        }

        if (isset($standardProduct['parent']) &&
            null !== $parentProductModel = $this->productModelRepository->findOneByIdentifier($standardProduct['parent'])
        ) {
            /** @var FamilyVariantInterface $familyVariant */
            $familyVariant = $parentProductModel->getFamilyVariant();
            $level = $familyVariant->getNumberOfLevel();
            $attributes = array_merge(
                $this->getVariantAttributeSetAttributeCodes->execute($familyVariant->getCode(), $level),
                $this->getVariantAttributeSetAxesCodes->execute($familyVariant->getCode(), $level)
            );

            return $this->keepOnlyAttributes($standardProduct, $attributes);
        }

        if (isset($standardProduct['family'])) {
            if (null !== $family = $this->familyRepository->findOneByIdentifier($standardProduct['family'])) {
                return $this->keepOnlyAttributes(
                    $standardProduct,
                    $this->getFamilyAttributeCodes->execute($family->getCode())
                );
            }
        }

        return $standardProduct;
    }

    /**
     * @param array $flatProduct
     * @param array $attributeCodesToKeep
     *
     * @return array
     */
    private function keepOnlyAttributes(array $flatProduct, array $attributeCodesToKeep): array
    {
        foreach ($flatProduct['values'] as $attributeName => $value) {
            if (!in_array($attributeName, $attributeCodesToKeep)) {
                unset($flatProduct['values'][$attributeName]);
            }
        }

        return $flatProduct;
    }
}
