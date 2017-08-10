<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel;

use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Normalizer\Standard\Product\PropertiesNormalizer as StandardPropertiesNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

/**
 * Transform the properties of products and variant product objects (fields and product values)
 * to the "indexing_product_and_product_model" format.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductPropertiesNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    private const FIELD_COMPLETENESS = 'completeness';
    private const FIELD_FAMILY_VARIANT = 'family_variant';
    private const FIELD_IN_GROUP = 'in_group';
    private const FIELD_ID = 'id';

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $data = [];

        $data[self::FIELD_ID] = (string) $product->getId();
        $data[StandardPropertiesNormalizer::FIELD_IDENTIFIER] = $product->getIdentifier();
        $data[StandardPropertiesNormalizer::FIELD_CREATED] = $this->serializer->normalize(
            $product->getCreated(),
            $format
        );
        $data[StandardPropertiesNormalizer::FIELD_UPDATED] = $this->serializer->normalize(
            $product->getUpdated(),
            $format
        );
        $data[StandardPropertiesNormalizer::FIELD_FAMILY] = $this->serializer->normalize(
            $product->getFamily(),
            $format
        );

        $data[StandardPropertiesNormalizer::FIELD_ENABLED] = (bool) $product->isEnabled();
        $data[StandardPropertiesNormalizer::FIELD_CATEGORIES] = $product->getCategoryCodes();

        $data[StandardPropertiesNormalizer::FIELD_GROUPS] = $product->getGroupCodes();

        foreach ($product->getGroupCodes() as $groupCode) {
            $data[self::FIELD_IN_GROUP][$groupCode] = true;
        }

        $data[self::FIELD_COMPLETENESS] = !$product->getCompletenesses()->isEmpty()
            ? $this->serializer->normalize(
                $product->getCompletenesses(),
                ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
                $context
            ) : [];

        $productValues = !$product->getValues()->isEmpty()
            ? $this->serializer->normalize(
                $product->getValues(),
                ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
                $context
            ) : [];

        $data[self::FIELD_FAMILY_VARIANT] = null;
        $parentValues = [];
        if ($product instanceof VariantProductInterface) {
            $familyVariant = $product->getFamilyVariant();
            $data[self::FIELD_FAMILY_VARIANT] = null !== $familyVariant ? $familyVariant->getCode() : null;

            if (null !== $product->getParent() && !$product->getParent()->getValues()->isEmpty()) {
                $parentValues = $this->serializer->normalize(
                    $product->getParent()->getValues(),
                    ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
                    $context
                );
            }
        }

        $data[StandardPropertiesNormalizer::FIELD_VALUES] = array_merge($productValues, $parentValues);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return ($data instanceof ProductInterface || $data instanceof VariantProductInterface)
            && ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX === $format;
    }
}
