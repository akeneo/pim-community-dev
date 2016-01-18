<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Structured;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

/**
 * A normalizer to transform a product entity into an array
 *
 * @author    Filips Alpe <filips@akeneo.com>
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
    const FIELD_VARIANT_GROUP = 'variant_group';

    /** @staticvar string */
    const FIELD_CATEGORY = 'categories';

    /** @staticvar string */
    const FIELD_ENABLED = 'enabled';

    /** @staticvar string */
    const FIELD_ASSOCIATIONS = 'associations';

    /** @staticvar string */
    const FIELD_VALUES = 'values';

    /** @var CollectionFilterInterface */
    protected $filter;

    /** @var string[] $supportedFormats */
    protected $supportedFormats = ['json', 'xml'];

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

        $defaultContext = [
            'only_associations'    => false,
            'exclude_associations' => false,
        ];

        $context = array_merge($defaultContext, $context);
        $context['entity'] = 'product';
        $data = [];

        if (isset($context['resource'])) {
            $data['resource'] = $context['resource'];
        }

        if (true === $context['only_associations']) {
            $data[self::FIELD_ASSOCIATIONS] = $this->normalizeAssociations($product->getAssociations());

            return $data;
        }

        $data[self::FIELD_FAMILY]        = $product->getFamily() ? $product->getFamily()->getCode() : null;
        $data[self::FIELD_GROUPS]        = $this->getGroups($product);
        $data[self::FIELD_VARIANT_GROUP] = $product->getVariantGroup() ? $product->getVariantGroup()->getCode() : null;
        $data[self::FIELD_CATEGORY]      = $product->getCategoryCodes();
        $data[self::FIELD_ENABLED]       = $product->isEnabled();
        $data[self::FIELD_VALUES]        = $this->normalizeValues($product->getValues(), $format, $context);

        if (false === $context['exclude_associations']) {
            $data[self::FIELD_ASSOCIATIONS] = $this->normalizeAssociations($product->getAssociations());
        }

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
        $values = $this->filter->filterCollection(
            $values,
            isset($context['filter_type']) ? $context['filter_type'] : 'pim.transform.product_value.structured',
            $context
        );

        $data = [];
        foreach ($values as $value) {
            $data[$value->getAttribute()->getCode()][] = $this->serializer->normalize($value, $format, $context);
        }

        return $data;
    }

    /**
     * Normalize the associations of the product
     *
     * @param Association[] $associations
     *
     * @return array
     */
    protected function normalizeAssociations($associations = [])
    {
        $data = [];

        foreach ($associations as $association) {
            $code = $association->getAssociationType()->getCode();

            foreach ($association->getGroups() as $group) {
                $data[$code]['groups'][] = $group->getCode();
            }

            foreach ($association->getProducts() as $product) {
                $data[$code]['products'][] = $product->getReference();
            }
        }

        return $data;
    }

    /**
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getGroups(ProductInterface $product)
    {
        $groups = [];

        if ($product->getGroupCodes()) {
            $groups = $product->getGroupCodes();
            if ($product->getVariantGroup()) {
                $variantGroup = $product->getVariantGroup()->getCode();
                $groups = array_diff($groups, [$variantGroup]);
            }
        }

        return $groups;
    }
}
