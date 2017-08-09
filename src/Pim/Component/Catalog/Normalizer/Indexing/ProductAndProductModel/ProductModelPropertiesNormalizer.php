<?php

namespace Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel;

use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Normalizer\Standard\Product\PropertiesNormalizer as StandardPropertiesNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

/**
 * Transform the properties of a product model object (fields and product values)
 * to the indexing_product_and_model format.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelPropertiesNormalizer extends SerializerAwareNormalizer implements NormalizerInterface
{
    protected const FIELD_FAMILY_VARIANT = 'family_variant';
    protected const FIELD_IN_GROUP = 'in_group';
    protected const FIELD_ID = 'id';

    /**
     * {@inheritdoc}
     */
    public function normalize($productModel, $format = null, array $context = [])
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $data = [];

        $data[self::FIELD_ID] = (string) $productModel->getId();
        $data[StandardPropertiesNormalizer::FIELD_IDENTIFIER] = $productModel->getCode();
        $data[StandardPropertiesNormalizer::FIELD_CREATED] = $this->serializer->normalize(
            $productModel->getCreated(),
            $format
        );
        $data[StandardPropertiesNormalizer::FIELD_UPDATED] = $this->serializer->normalize(
            $productModel->getUpdated(),
            $format
        );
        $data[StandardPropertiesNormalizer::FIELD_FAMILY] = $this->serializer->normalize(
            $productModel->getFamilyVariant()->getFamily(),
            $format
        );
        $data[self::FIELD_FAMILY_VARIANT] = $productModel->getFamilyVariant()->getCode();

        $data[StandardPropertiesNormalizer::FIELD_VALUES] = !$productModel->getValues()->isEmpty()
            ? $this->serializer->normalize(
                $productModel->getValues(),
                ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
                $context
            ) : [];

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductModelInterface
            && ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX === $format;
    }
}
