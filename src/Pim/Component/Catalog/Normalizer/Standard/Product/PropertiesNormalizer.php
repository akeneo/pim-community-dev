<?php

namespace Pim\Component\Catalog\Normalizer\Standard\Product;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueCollectionInterface;
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
    const FIELD_IDENTIFIER = 'identifier';
    const FIELD_FAMILY = 'family';
    const FIELD_GROUPS = 'groups';
    const FIELD_VARIANT_GROUP = 'variant_group';
    const FIELD_CATEGORIES = 'categories';
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

        $data[self::FIELD_IDENTIFIER] = $product->getIdentifier();
        $data[self::FIELD_FAMILY] = $product->getFamily() ? $product->getFamily()->getCode() : null;
        $data[self::FIELD_GROUPS] = $this->normalizeGroups($product);
        $data[self::FIELD_VARIANT_GROUP] = $product->getVariantGroup() ? $product->getVariantGroup()->getCode() : null;
        $data[self::FIELD_CATEGORIES] = $product->getCategoryCodes();
        $data[self::FIELD_ENABLED] = (bool) $product->isEnabled();
        $data[self::FIELD_VALUES] = $this->normalizeValues($product->getValues(), $format, $context);
        $data[self::FIELD_CREATED] = $this->serializer->normalize($product->getCreated(), $format);
        $data[self::FIELD_UPDATED] = $this->serializer->normalize($product->getUpdated(), $format);

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
     * @param ProductValueCollectionInterface $values
     * @param string                          $format
     * @param array                           $context
     *
     * @return ArrayCollection
     */
    private function normalizeValues(ProductValueCollectionInterface $values, $format, array $context = [])
    {
        foreach ($context['filter_types'] as $filterType) {
            $values = $this->filter->filterCollection($values, $filterType, $context);
        }

        $data = $this->serializer->normalize($values, $format, $context);

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
