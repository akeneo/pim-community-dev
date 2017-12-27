<?php

namespace Pim\Component\Catalog\Normalizer\Indexing\Product;

use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Normalizer\Standard\Product\PropertiesNormalizer as StandardPropertiesNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

/**
 * Transform the properties of a product object (fields and product values)
 * to the indexing format.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PropertiesNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    const FIELD_COMPLETENESS = 'completeness';
    const FIELD_IN_GROUP = 'in_group';
    const FIELD_ID = 'id';

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
            $this->getUpdatedAt($product),
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
                ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX,
                $context
            ) : [];

        $data[StandardPropertiesNormalizer::FIELD_VALUES] = !$product->getValues()->isEmpty()
            ? $this->serializer->normalize(
                $product->getValues(),
                ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX,
                $context
            ) : [];

        $data[StandardPropertiesNormalizer::FIELD_LABEL] = $this->getLabel(
            $data[StandardPropertiesNormalizer::FIELD_VALUES],
            $product
        );

        return $data;
    }

    /**
     * Get label of the given product
     *
     * @param array            $values
     * @param ProductInterface $product
     *
     * @return array
     */
    private function getLabel(array $values, ProductInterface $product): array
    {
        if (null === $product->getFamily()) {
            return [];
        }

        $valuePath = sprintf('%s-text', $product->getFamily()->getAttributeAsLabel()->getCode());
        if (!isset($values[$valuePath])) {
            return [];
        }

        return $values[$valuePath];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX === $format;
    }

    /**
     * @param ProductInterface $product
     *
     * @return \Datetime
     */
    private function getUpdatedAt(ProductInterface $product): \Datetime
    {
        $date = $product->getUpdated();
        if ($product instanceof VariantProductInterface) {
            $dates = [$date];
            $parent = $product->getParent();
            while (null !== $parent) {
                $dates[] = $parent->getUpdated();
                $parent = $parent->getParent();
            }

            $date = max($dates);
        }

        return $date;
    }
}
