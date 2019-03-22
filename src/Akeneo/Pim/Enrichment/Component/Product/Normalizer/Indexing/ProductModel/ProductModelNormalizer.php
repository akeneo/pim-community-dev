<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductModel;

use Akeneo\Pim\Enrichment\Bundle\Sql\GetFamilyAttributeCodes;
use Akeneo\Pim\Enrichment\Bundle\Sql\GetVariantAttributeSetAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a product model to the "indexing_product_model" format.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelNormalizer implements NormalizerInterface
{
    public const INDEXING_FORMAT_PRODUCT_MODEL_INDEX = 'indexing_product_model';
    private const FIELD_ATTRIBUTES_IN_LEVEL = 'attributes_of_ancestors';
    private const FIELD_DOCUMENT_TYPE = 'document_type';

    /** @var NormalizerInterface */
    private $propertiesNormalizer;

    /** @var GetFamilyAttributeCodes */
    private $getFamilyAttributeCodes;

    /** @var GetVariantAttributeSetAttributeCodes */
    private $getVariantAttributeSetAttributeCodes;

    /**
     * @param NormalizerInterface $propertiesNormalizer
     * @param GetFamilyAttributeCodes $getFamilyAttributeCodes
     * @param GetVariantAttributeSetAttributeCodes $getVariantAttributeSetAttributeCodes
     */
    public function __construct(
        NormalizerInterface $propertiesNormalizer,
        GetFamilyAttributeCodes $getFamilyAttributeCodes,
        GetVariantAttributeSetAttributeCodes $getVariantAttributeSetAttributeCodes
    ) {
        $this->propertiesNormalizer = $propertiesNormalizer;
        $this->getFamilyAttributeCodes = $getFamilyAttributeCodes;
        $this->getVariantAttributeSetAttributeCodes = $getVariantAttributeSetAttributeCodes;
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
        $data[self::FIELD_ATTRIBUTES_IN_LEVEL] = $this->getAttributesOfAncestors($productModel);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductModelInterface && self::INDEXING_FORMAT_PRODUCT_MODEL_INDEX === $format;
    }

    /**
     * Get attribute codes of the product model ancestors
     *
     * @param ProductModelInterface $productModel
     *
     * @return array
     */
    private function getAttributesOfAncestors(ProductModelInterface $productModel): array
    {
        if (null === $productModel->getFamilyVariant()) {
            return [];
        }

        if (ProductModel::ROOT_VARIATION_LEVEL === $productModel->getVariationLevel()) {
            return [];
        }

        $attributesOfAncestors = array_diff(
            $this->getFamilyAttributeCodes->execute($productModel->getFamily()->getCode()),
            $this->getVariantAttributeSetAttributeCodes->execute($productModel->getFamilyVariant()->getCode(), 1),
            $this->getVariantAttributeSetAttributeCodes->execute($productModel->getFamilyVariant()->getCode(), 2)
        );

        sort($attributesOfAncestors);

        return $attributesOfAncestors;
    }
}
