<?php

namespace Pim\Component\Catalog\Normalizer\Structured;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

/**
 * A normalizer to transform product properties into an array
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductPropertiesNormalizer implements NormalizerInterface
{
    /** @staticvar string */
    const FIELD_FAMILY = 'family';

    /** @staticvar string */
    const FIELD_GROUPS = 'groups';

    /** @staticvar string */
    const FIELD_VARIANT_GROUP = 'variant_group';

    /** @staticvar string */
    const FIELD_CATEGORY = 'categories';

    /** @staticvar string */
    const FIELD_ENABLED = 'enabled';

    /** @staticvar string */
    const FIELD_VALUES = 'values';

    /** @var CollectionFilterInterface */
    protected $filter;

    /** @var NormalizerInterface */
    protected $valuesNormalizer;

    /** @var string[] $supportedFormats */
    protected $supportedFormats = ['json', 'xml'];

    /**
     * @param CollectionFilterInterface $filter
     * @param NormalizerInterface       $valuesNormalizer
     */
    public function __construct(CollectionFilterInterface $filter, NormalizerInterface $valuesNormalizer)
    {
        $this->filter = $filter;
        $this->valuesNormalizer = $valuesNormalizer;
    }

    /**
     * {@inheritdoc}
     *
     * @param ProductInterface $product
     */
    public function normalize($product, $format = null, array $context = [])
    {
        $defaultContext = ['filter_types' => ['pim.transform.product_value.structured']];

        $context = array_merge($defaultContext, $context);
        $context['entity'] = 'product';
        $data = [];

        if (isset($context['resource'])) {
            $data['resource'] = $context['resource'];
        }

        $data[self::FIELD_FAMILY]        = null !== $product->getFamily() ? $product->getFamily()->getCode() : null;
        $data[self::FIELD_GROUPS]        = $this->getGroups($product);
        $data[self::FIELD_VARIANT_GROUP] = null !== $product->getVariantGroup() ? $product->getVariantGroup()->getCode() : null;
        $data[self::FIELD_CATEGORY]      = $product->getCategoryCodes();
        $data[self::FIELD_ENABLED]       = $product->isEnabled();
        $data[self::FIELD_VALUES]        = $this->normalizeValues($product->getValues(), $format, $context);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Normalize the values of the product
     *
     * @param ArrayCollection $values
     * @param string          $format
     * @param array           $context
     *
     * @return ArrayCollection
     */
    protected function normalizeValues(ArrayCollection $values, $format, array $context = [])
    {
        foreach ($context['filter_types'] as $filterType) {
            $values = $this->filter->filterCollection($values, $filterType, $context);
        }

        $data = $this->valuesNormalizer->normalize($values, $format, $context);

        return $data;
    }

    /**
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getGroups(ProductInterface $product)
    {
        $groups = $product->getGroupCodes();

        if (null !== $product->getVariantGroup()) {
            $variantGroup = $product->getVariantGroup()->getCode();
            $groups = array_diff($groups, [$variantGroup]);
        }

        return $groups;
    }
}
