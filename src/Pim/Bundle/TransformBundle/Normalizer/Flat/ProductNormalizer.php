<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Flat;

use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\TransformBundle\Normalizer\Filter\NormalizerFilterInterface;

/**
 * A normalizer to transform a product entity into a flat array
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer extends SerializerAwareNormalizer implements NormalizerInterface
{
    /** @staticvar string */
    const FIELD_FAMILY = 'family';

    /** @staticvar string */
    const FIELD_GROUPS = 'groups';

    /** @staticvar string */
    const FIELD_CATEGORY = 'categories';

    /** @staticvar string */
    const ITEM_SEPARATOR = ',';

    /** @var array */
    protected $supportedFormats = array('csv', 'flat');

    /** @var array */
    protected $results = array();

    /** @var array $fields */
    protected $fields = array();

    /** @var NormalizerFilterInterface[] */
    protected $valuesFilters = [];

    /**
     * {@inheritdoc}
     */
    public function setFilters(array $filters)
    {
        $this->valuesFilters = $filters;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $context = $this->resolveContext($context);

        if (isset($context['fields']) && !empty($context['fields'])) {
            $this->fields  = array_fill_keys($context['fields'], '');
            $this->results = $this->fields;
        } else {
            $this->results = $this->serializer->normalize($object->getIdentifier(), $format, $context);
        }

        $this->normalizeFamily($object->getFamily());

        $this->normalizeGroups($object->getGroupCodes());

        $this->normalizeCategories($object->getCategoryCodes());

        $this->normalizeAssociations($object->getAssociations());

        $this->normalizeValues($object, $format, $context);

        $this->normalizeProperties($object);

        return $this->results;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Normalize properties
     *
     * @param ProductInterface $product
     */
    protected function normalizeProperties(ProductInterface $product)
    {
        $this->results['enabled'] = (int) $product->isEnabled();
    }

    /**
     * Normalize values
     *
     * @param ProductInterface $product
     * @param string|null      $format
     * @param array            $context
     *
     * @return null
     */
    protected function normalizeValues(ProductInterface $product, $format = null, array $context = [])
    {
        if (empty($this->fields)) {
            $values = $this->getFilteredValues($product, $context);
            $context['metric_format'] = 'multiple_fields';

            $normalizedValues = [];
            foreach ($values as $value) {
                $normalizedValues = array_merge(
                    $normalizedValues,
                    $this->serializer->normalize($value, $format, $context)
                );
            }
            ksort($normalizedValues);
            $this->results = array_merge($this->results, $normalizedValues);
        } else {

            // TODO only used for quick export, find a way to homogeneize this part
            $values = $product->getValues();
            $context['metric_format'] = 'single_field';

            foreach ($values as $value) {
                $fieldValue = $this->getFieldValue($value);
                if ($value->getAttribute()->getAttributeType() === 'pim_catalog_price_collection'
                    || isset($this->fields[$fieldValue])) {
                    $normalizedValue = $this->serializer->normalize($value, $format, $context);
                    $this->results = array_merge($this->results, $normalizedValue);
                }
            }
        }
    }

    /**
     * Get filtered values
     *
     * @param ProductInterface $product
     * @param array            $context
     *
     * @return ProductValueInterface[]
     */
    protected function getFilteredValues(ProductInterface $product, array $context = [])
    {
        $values = $product->getValues();
        $context = [
            'identifier'  => $product->getIdentifier(),
            'scopeCode'   => $context['scopeCode'],
            'localeCodes' => $context['localeCodes']
        ];

        foreach ($this->valuesFilters as $filter) {
            $values = $filter->filter($values, $context);
        }

        return $values;
    }

    /**
     * Normalize the field name for values
     *
     * @param ProductValueInterface $value
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

        return $value->getAttribute()->getCode() . $suffix;
    }

    /**
     * Normalizes a family
     *
     * @param Family $family
     */
    protected function normalizeFamily(Family $family = null)
    {
        $this->results[self::FIELD_FAMILY] = $family ? $family->getCode() : '';
    }

    /**
     * Normalizes groups
     *
     * @param Group[] $groups
     */
    protected function normalizeGroups($groups = null)
    {
        $this->results[self::FIELD_GROUPS] = $groups;
    }

    /**
     * Normalizes categories
     *
     * @param string $categories
     */
    protected function normalizeCategories($categories = '')
    {
        $this->results[self::FIELD_CATEGORY] = $categories;
    }

    /**
     * Normalize associations
     *
     * @param Association[] $associations
     */
    protected function normalizeAssociations($associations = array())
    {
        foreach ($associations as $association) {
            $columnPrefix = $association->getAssociationType()->getCode();

            $groups = array();
            foreach ($association->getGroups() as $group) {
                $groups[] = $group->getCode();
            }

            $products = array();
            foreach ($association->getProducts() as $product) {
                $products[] = $product->getIdentifier();
            }

            $this->results[$columnPrefix .'-groups'] = implode(',', $groups);
            $this->results[$columnPrefix .'-products'] = implode(',', $products);
        }
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
        return array_merge(['scopeCode' => null, 'localeCodes' => []], $context);
    }
}
