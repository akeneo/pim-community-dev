<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel;

use Pim\Component\Catalog\FamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize products to the 'indexing_product_and_product_model' format.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer implements NormalizerInterface
{
    private const FIELD_ATTRIBUTES_OF_ANCESTORS = 'attributes_of_ancestors';
    private const FIELD_DOCUMENT_TYPE = 'document_type';

    /** @var NormalizerInterface */
    private $propertiesNormalizer;

    /** @var EntityWithFamilyVariantAttributesProvider */
    private $attributesProvider;

    /**
     * @param NormalizerInterface                       $propertiesNormalizer
     * @param EntityWithFamilyVariantAttributesProvider $attributesProvider
     */
    public function __construct(
        NormalizerInterface $propertiesNormalizer,
        EntityWithFamilyVariantAttributesProvider $attributesProvider
    ) {
        $this->propertiesNormalizer = $propertiesNormalizer;
        $this->attributesProvider = $attributesProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        $data = $this->propertiesNormalizer->normalize($product, $format, $context);

        $data[self::FIELD_DOCUMENT_TYPE] = ProductInterface::class;
        $data[self::FIELD_ATTRIBUTES_OF_ANCESTORS] = $this->getAttributeCodesOfAncestors($product);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX === $format &&
            $data instanceof ProductInterface;
    }

    /**
     * Get the attribute codes of the family if product is not a variant.
     * If variant, get attribute codes of the family variant.
     *
     * We index all attribute codes to be able to search products on attributes with operators like "is empty".
     * At the end, we sort to reindex attributes correctly (if index keys are not sorted correctly, ES will throw an exception)
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    private function getAttributeCodesOfAncestors(ProductInterface $product): array
    {
        if (!$product->isVariant()) {
            return [];
        }

        $ancestorsAttributesCodes = [];
        $entityWithFamilyVariant = $product;

        while ($this->hasParent($entityWithFamilyVariant)) {
            $parent = $entityWithFamilyVariant->getParent();

            $attributes = $this->attributesProvider->getAttributes($parent);
            $attributeCodes = array_map(
                function (AttributeInterface $attribute) {
                    return $attribute->getCode();
                },
                $attributes
            );
            $ancestorsAttributesCodes = array_merge($ancestorsAttributesCodes, $attributeCodes);

            $entityWithFamilyVariant = $parent;
        }

        sort($ancestorsAttributesCodes);

        return $ancestorsAttributesCodes;
    }

    private function hasParent(EntityWithFamilyVariantInterface $entityWithFamilyVariant): bool
    {
        return null !== $entityWithFamilyVariant->getParent();
    }
}
