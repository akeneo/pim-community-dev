<?php

namespace Pim\Bundle\TransformBundle\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\TransformBundle\Normalizer\Filter\FilterableNormalizerInterface;
use Pim\Bundle\TransformBundle\Normalizer\Filter\NormalizerFilterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * A normalizer to transform a product entity into an array
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer implements NormalizerInterface, SerializerAwareInterface, FilterableNormalizerInterface
{
    /** @staticvar string */
    const FIELD_FAMILY = 'family';

    /** @staticvar string */
    const FIELD_GROUPS = 'groups';

    /** @staticvar string */
    const FIELD_CATEGORY = 'categories';

    /** @staticvar string */
    const FIELD_ENABLED = 'enabled';

    /** @staticvar string */
    const FIELD_ASSOCIATIONS = 'associations';

    /** @staticvar string */
    const FIELD_VALUES = 'values';

    /** @var SerializerInterface */
    protected $valuesSerializer;

    /** @var  NormalizerFilterInterface[] */
    protected $valuesFilters;

    /** @var string[] $supportedFormats */
    protected $supportedFormats = ['json', 'xml'];

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->valuesSerializer = $serializer;
    }

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
    public function normalize($product, $format = null, array $context = [])
    {
        if (!$this->valuesSerializer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $context['entity'] = 'product';
        $data = [];

        $data[self::FIELD_FAMILY]   = $product->getFamily() ? $product->getFamily()->getCode() : null;
        $data[self::FIELD_GROUPS]   = $product->getGroupCodes() ? explode(',', $product->getGroupCodes()) : [];
        $data[self::FIELD_CATEGORY] = $product->getCategoryCodes() ? explode(',', $product->getCategoryCodes()) : [];
        $data[self::FIELD_ENABLED]  = $product->isEnabled();

        $data[self::FIELD_ASSOCIATIONS] = $this->normalizeAssociations($product->getAssociations());

        $data[self::FIELD_VALUES] = $this->normalizeValues($product->getValues(), $format, $context);

        if (isset($context['resource'])) {
            $data['resource'] = $context['resource'];
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
        foreach ($this->valuesFilters as $filter) {
            $values = $filter->filter($values, $context);
        }

        $data = [];

        foreach ($values as $value) {
            $data[$value->getAttribute()->getCode()][] = $this->valuesSerializer->normalize($value, $format, $context);
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
}
