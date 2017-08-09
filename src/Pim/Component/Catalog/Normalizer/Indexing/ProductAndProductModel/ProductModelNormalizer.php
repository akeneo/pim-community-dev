<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel;

use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a product model to the "indexing_product_and_product_model" format.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelNormalizer implements NormalizerInterface
{
    public const INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX = 'indexing_product_and_product_model';
    private const FIELD_ATTRIBUTES_IN_LEVEL = 'attributes_for_this_level';
    private const FIELD_PRODUCT_TYPE = 'product_type';

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
     */
    public function normalize($productModel, $format = null, array $context = [])
    {
        $data = $this->propertiesNormalizer->normalize($productModel, $format, $context);

        $data[self::FIELD_PRODUCT_TYPE] = $this->getVariationLevelCode($productModel);
        $data[self::FIELD_ATTRIBUTES_IN_LEVEL] = array_keys($productModel->getRawValues());

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
     * Returns the product_type of the given product model.
     *
     * @param EntityWithFamilyVariantInterface $productModel
     *
     * @return string
     */
    private function getVariationLevelCode(ProductModelInterface $productModel): string
    {
        $level = $productModel->getVariationLevel();
        switch ($level) {
            case 0:
                return 'PimCatalogRootProductModel';
            case 1:
                return 'PimCatalogSubProductModel';
            default:
                throw new \LogicException(sprintf('Invalid variant level. %s given', $level));
        }
    }
}
