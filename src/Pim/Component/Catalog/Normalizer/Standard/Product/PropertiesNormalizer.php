<?php

namespace Pim\Component\Catalog\Normalizer\Standard\Product;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

/**
 * Transform the properties of a product object (fields and product values)
 * to a standardized array
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PropertiesNormalizer extends SerializerAwareNormalizer implements NormalizerInterface
{
    const FIELD_FAMILY = 'family';
    const FIELD_GROUPS = 'groups';
    const FIELD_VARIANT_GROUP = 'variant_group';
    const FIELD_CATEGORY = 'categories';
    const FIELD_ENABLED = 'enabled';
    const FIELD_VALUES = 'values';
    const FIELD_CREATED = 'created';
    const FIELD_UPDATED = 'updated';

    /** @var CollectionFilterInterface */
    private $filter;

    /**
     * @param CollectionFilterInterface $filter The collection filter
     */
    public function __construct(CollectionFilterInterface $filter)
    {
        $this->filter = $filter;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $context = array_merge(['filter_types' => ['pim.transform.product_value.structured']], $context);
        $data = [];

        $data[self::FIELD_FAMILY] = $product->getFamily() ? $product->getFamily()->getCode() : null;
        $data[self::FIELD_GROUPS] = $this->normalizeGroups($product);
        $data[self::FIELD_VARIANT_GROUP] = $product->getVariantGroup() ? $product->getVariantGroup()->getCode() : null;
        $data[self::FIELD_CATEGORY] = $product->getCategoryCodes();
        $data[self::FIELD_ENABLED] = $product->isEnabled();
        $data[self::FIELD_VALUES] = $this->normalizeValues($product->getValues(), $context);
        $data[self::FIELD_CREATED] = $this->serializer->normalize($product->getCreated(), 'standard');
        $data[self::FIELD_UPDATED] = $this->serializer->normalize($product->getUpdated(), 'standard');

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && 'standard' === $format;
    }

    /**
     * Normalize the values of the product
     *
     * @param ArrayCollection $values
     * @param array           $context
     *
     * @return ArrayCollection
     */
    private function normalizeValues(ArrayCollection $values, array $context = [])
    {
        foreach ($context['filter_types'] as $filterType) {
            $values = $this->filter->filterCollection($values, $filterType, $context);
        }

        $data = [];
        foreach ($values as $value) {
            $data[$value->getAttribute()->getCode()][] = $this->serializer->normalize($value, 'standard', $context);
        }

        return $data;
    }

    /**
     * @param ProductInterface $product
     *
     * @return array
     */
    private function normalizeGroups(ProductInterface $product)
    {
        $groupCodes = [];

        if (count($product->getGroupCodes()) > 0) {
            $groupCodes = $product->getGroupCodes();
            if (null !== $product->getVariantGroup()) {
                $variantGroupCode = $product->getVariantGroup()->getCode();
                $groupCodes = array_diff($groupCodes, [$variantGroupCode]);
            }
        }

        return $groupCodes;
    }
}
