<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
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
class ProductNormalizer implements NormalizerInterface, SerializerAwareInterface, CacheableSupportsMethodInterface
{
    use SerializerAwareTrait;

    /** @staticvar string */
    protected const FIELD_FAMILY = 'family';

    /** @staticvar string */
    protected const FIELD_GROUPS = 'groups';

    /** @staticvar string */
    protected const FIELD_CATEGORY = 'categories';

    protected const FIELD_PARENT = 'parent';

    /** @staticvar string */
    protected const FIELD_ENABLED = 'enabled';

    /** @staticvar string */
    protected const ITEM_SEPARATOR = ',';

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
    public function normalize($object, $format = null, array $context = []): array
    {
        $context = $this->resolveContext($context);

        $results = [];
        $results[self::FIELD_FAMILY] = $this->normalizeFamily($object->getFamily());
        $results[self::FIELD_GROUPS] = $this->normalizeGroups($object->getGroupCodes());
        $results[self::FIELD_CATEGORY] = $this->normalizeCategories($object->getCategoryCodes());
        $results[self::FIELD_PARENT] = $this->normalizeParent($object->getParent());
        $results = array_merge($results, $this->normalizeAssociations($object->getAssociations()));
        $results = array_replace($results, $this->normalizeValues($object, $format, $context));
        $results[self::FIELD_ENABLED] = (int) $object->isEnabled();

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ProductInterface && in_array($format, $this->supportedFormats);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
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
    protected function normalizeValues(ProductInterface $product, $format = null, array $context = []): array
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
     * @return WriteValueCollection|ValueInterface[]
     */
    protected function getFilteredValues(ProductInterface $product, array $context = [])
    {
        if ($product->isVariant()) {
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
    protected function getFieldValue($value): string
    {
        $suffix = '';

        if ($value->isLocalizable()) {
            $suffix = sprintf('-%s', $value->getLocaleCode());
        }
        if ($value->isScopable()) {
            $suffix .= sprintf('-%s', $value->getScopeCode());
        }

        return $value->getAttributeCode().$suffix;
    }

    /**
     * Normalizes a family
     *
     * @param FamilyInterface $family
     *
     * @return string
     */
    protected function normalizeFamily(FamilyInterface $family = null): string
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
    protected function normalizeGroups(array $groups = []): string
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
    protected function normalizeCategories(array $categories = []): string
    {
        return implode(static::ITEM_SEPARATOR, $categories);
    }

    /**
     * Normalizes a product parent.
     *
     * @param ProductModelInterface $parent
     *
     * @return string
     */
    protected function normalizeParent(ProductModelInterface $parent = null): string
    {
        return $parent ? $parent->getCode() : '';
    }

    /**
     * Normalize associations
     *
     * @param Collection|AssociationInterface[] $associations
     *
     * @return array
     */
    protected function normalizeAssociations($associations = []): array
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

            $productModels = [];
            foreach ($association->getProductModels() as $productModel) {
                $productModels[] = $productModel->getCode();
            }

            $results[$columnPrefix . '-groups'] = implode(',', $groups);
            $results[$columnPrefix . '-products'] = implode(',', $products);
            $results[$columnPrefix . '-product_models'] = implode(',', $productModels);
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
    protected function resolveContext(array $context): array
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
