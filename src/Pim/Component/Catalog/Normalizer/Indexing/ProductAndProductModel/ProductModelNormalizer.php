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
    private const FIELD_ATTRIBUTES_OF_ANCESTORS = 'attributes_of_ancestors';
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
        $data[self::FIELD_ATTRIBUTES_OF_ANCESTORS] = $this->getAttributesOfAncestors($productModel);

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

        $attributesOfAncestors = $productModel->getFamilyVariant()
            ->getCommonAttributes()
            ->map(
                function (AttributeInterface $attribute) {
                    return $attribute->getCode();
                }
            )->toArray();

        sort($attributesOfAncestors);

        return $attributesOfAncestors;
    }
}
