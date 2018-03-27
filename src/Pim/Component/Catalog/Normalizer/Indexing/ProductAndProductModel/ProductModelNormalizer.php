<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductModel;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a product model to the "indexing_product_and_product_model" format.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelNormalizer implements NormalizerInterface
{
    public const INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX = 'indexing_product_and_product_model';
    private const FIELD_ATTRIBUTES_IN_LEVEL = 'attributes_for_this_level';
    private const FIELD_DOCUMENT_TYPE = 'document_type';

    /** @var NormalizerInterface */
    private $propertiesNormalizer;

    /**
     * @param NormalizerInterface $propertiesNormalizer
     */
    public function __construct(NormalizerInterface $propertiesNormalizer)
    {
        $this->propertiesNormalizer = $propertiesNormalizer;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function normalize($productModel, $format = null, array $context = [])
    {
        $data = $this->propertiesNormalizer->normalize($productModel, $format, $context);

        $data[self::FIELD_DOCUMENT_TYPE] = ProductModelInterface::class;
        $data[self::FIELD_ATTRIBUTES_IN_LEVEL] = $this->getAttributeCodesForOwnLevel($productModel);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductModelInterface && self::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX === $format;
    }

    /**
     * Get attribute codes for the product model level.
     * We index all attribute codes to be able to search product models on attributes with operators like "is empty".
     *
     * At the end, we sort to reindex attributes correctly (if index keys are not sorted correctly, ES will throw an exception)
     *
     * @param ProductModelInterface $productModel
     *
     * @return array
     */
    private function getAttributeCodesForOwnLevel(ProductModelInterface $productModel): array
    {
        $attributeCodes = array_keys($productModel->getRawValues());

        $variationLevel = $productModel->getVariationLevel();

        if (ProductModel::ROOT_VARIATION_LEVEL === $variationLevel) {
            $familyAttributes = $productModel->getFamilyVariant()->getCommonAttributes()->toArray();
        } else {
            $attributeSet = $productModel->getFamilyVariant()->getVariantAttributeSet($variationLevel);

            if (null === $attributeSet) {
                return $attributeCodes;
            }

            $familyAttributes = array_merge($attributeSet->getAttributes()->toArray(), $attributeSet->getAxes()->toArray());
        }

        $familyAttributesCodes = array_map(function (AttributeInterface $attribute) {
            return $attribute->getCode();
        }, $familyAttributes);

        $attributeCodes = array_unique(array_merge($familyAttributesCodes, $attributeCodes));

        sort($attributeCodes);

        return $attributeCodes;
    }
}
