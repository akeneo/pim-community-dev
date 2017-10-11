<?php

namespace Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

/**
 * A normalizer to transform a product entity into a flat array
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    /** @staticvar string */
    const FIELD_FAMILY = 'family';

    /** @staticvar string */
    const FIELD_GROUPS = 'groups';

    /** @staticvar string */
    const FIELD_CATEGORY = 'categories';

    /** @staticvar string */
    const FIELD_ENABLED = 'enabled';

    /** @staticvar string */
    const ITEM_SEPARATOR = ',';

    /** @var string[] */
    protected $supportedFormats = ['flat'];

    /** @var CollectionFilterInterface */
    protected $filter;

    /**
     * @param CollectionFilterInterface $filter The collection filter
     */
    public function __construct(CollectionFilterInterface $filter = null)
    {
        $this->filter = $filter;
    }

    /**
     * {@inheritdoc}
     *
     * @param ProductInterface $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context = $this->resolveContext($context);

        $results = [];
        $results[self::FIELD_FAMILY] = $this->normalizeFamily($object->getFamily());
        $results[self::FIELD_GROUPS] = $this->normalizeGroups($object->getGroupCodes());
        $results[self::FIELD_CATEGORY] = $this->normalizeCategories($object->getCategoryCodes());
        $results = array_merge($results, $this->normalizeAssociations($object->getAssociations()));
        $results = array_replace($results, $this->normalizeValues($object, $format, $context));
        $results[self::FIELD_ENABLED] = (int) $object->isEnabled();

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Normalize values
     *
     * @param ProductInterface $product
     * @param string|null      $format
     * @param array            $context
     *
     * @return array
     */
    protected function normalizeValues(ProductInterface $product, $format = null, array $context = [])
    {
        $values = $this->getFilteredValues($product, $context);

        $normalizedValues = [];
        foreach ($values as $value) {
            $normalizedValues = array_replace(
                $normalizedValues,
                $this->serializer->normalize($value, $format, $context)
            );
        }
        ksort($normalizedValues);

        return $normalizedValues;
    }

    /**
     * Get filtered values
     *
     * @param ProductInterface $product
     * @param array            $context
     *
     * @return ValueCollectionInterface|ValueInterface[]
     */
    protected function getFilteredValues(ProductInterface $product, array $context = [])
    {
        if ($product instanceof VariantProductInterface) {
            $values = $product->getValuesForVariation();
        } else {
            $values = $product->getValues();
        }

        if (null === $this->filter) {
            return $values;
        }

        foreach ($context['filter_types'] as $filterType) {
            $values = $this->filter->filterCollection(
                $values,
                $filterType,
                [
                    'channels' => [$context['scopeCode']],
                    'locales'  => $context['localeCodes']
                ]
            );
        }

        return $values;
    }

    /**
     * Normalize the field name for values
     *
     * @param ValueInterface $value
     *
     * @return string
     */
    protected function getFieldValue($value)
    {
        $suffix = '';

        if ($value->getAttribute()->isLocalizable()) {
            $suffix = sprintf('-%s', $value->getLocale());
        }
        if ($value->getAttribute()->isScopable()) {
            $suffix .= sprintf('-%s', $value->getScope());
        }

        return $value->getAttribute()->getCode().$suffix;
    }

    /**
     * Normalizes a family
     *
     * @param FamilyInterface $family
     *
     * @return string
     */
    protected function normalizeFamily(FamilyInterface $family = null)
    {
        return $family ? $family->getCode() : '';
    }

    /**
     * Normalizes groups
     *
     * @param GroupInterface[] $groups
     *
     * @return string
     */
    protected function normalizeGroups($groups = [])
    {
        return implode(static::ITEM_SEPARATOR, $groups);
    }

    /**
     * Normalizes categories
     *
     * @param array $categories
     *
     * @return string
     */
    protected function normalizeCategories($categories = [])
    {
        return implode(static::ITEM_SEPARATOR, $categories);
    }

    /**
     * Normalize associations
     *
     * @param AssociationInterface[] $associations
     *
     * @return array
     */
    protected function normalizeAssociations($associations = [])
    {
        $results = [];
        foreach ($associations as $association) {
            $columnPrefix = $association->getAssociationType()->getCode();

            $groups = [];
            foreach ($association->getGroups() as $group) {
                $groups[] = $group->getCode();
            }

            $products = [];
            foreach ($association->getProducts() as $product) {
                $products[] = $product->getIdentifier();
            }

            $results[$columnPrefix . '-groups'] = implode(',', $groups);
            $results[$columnPrefix . '-products'] = implode(',', $products);
        }

        return $results;
    }

    /**
     * Merge default format option with context
     *
     * @param array $context
     *
     * @return array
     */
    protected function resolveContext(array $context)
    {
        return array_merge(
            [
                'scopeCode'     => null,
                'localeCodes'   => [],
                'metric_format' => 'multiple_fields',
                'filter_types'  => ['pim.transform.product_value.flat']
            ],
            $context
        );
    }
}
