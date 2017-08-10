<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel;

use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Normalizer\Standard\Product\PropertiesNormalizer as StandardPropertiesNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

/**
 * Transform the properties of a product model object (fields and product values)
 * to the indexing_product_and_model format.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelPropertiesNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    private const FIELD_FAMILY_VARIANT = 'family_variant';
    private const FIELD_ID = 'id';

    /**
     * {@inheritdoc}
     */
    public function normalize($productModel, $format = null, array $context = [])
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $data = [];

        $data[self::FIELD_ID] = 'product_model_' . (string) $productModel->getId();
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

        $productModelValues = !$productModel->getValues()->isEmpty()
            ? $this->serializer->normalize(
                $productModel->getValues(),
                ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
                $context
            ) : [];

        $parentValues = $this->getAllParentsValues($productModel->getParent(), $context);
        $data[StandardPropertiesNormalizer::FIELD_VALUES] = array_merge($productModelValues, $parentValues);

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

    /**
     * Normalizes all the values of a product model and its parents.
     *
     * @param null|ProductModelInterface $productModel
     * @param array                      $context
     *
     * @return mixed
     */
    private function getAllParentsValues($productModel, array $context): array
    {
        if (null === $productModel || $productModel->getValues()->isEmpty()) {
            return [];
        }

        $productModelNormalizedValues = $this->serializer->normalize(
            $productModel->getValues(),
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
            $context
        );

        return array_merge(
            $productModelNormalizedValues,
            $this->getAllParentsValues($productModel->getParent(), $context)
        );
    }
}
