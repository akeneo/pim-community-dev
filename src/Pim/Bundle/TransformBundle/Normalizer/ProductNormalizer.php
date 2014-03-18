<?php

namespace Pim\Bundle\TransformBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\Channel;

/**
 * A normalizer to transform a product entity into an array
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer implements NormalizerInterface
{
    /**
     * @var array $supportedFormats
     */
    protected $supportedFormats = array('json', 'xml');

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = array())
    {
        $locales  = isset($context['locales']) ? $context['locales'] : [];
        $channels = isset($context['channels']) ? $context['channels'] : [];
        $values = $this->filterValues($product->getValues(), $channels, $locales);
        $attributes = $this->getAttributes($values);

        $data = array();

        foreach ($attributes as $attribute) {
            $code = $attribute->getCode();

            $attributeValues = $values->filter(
                function ($value) use ($code) {
                    return $value->getAttribute()->getCode() == $code;
                }
            );

            foreach ($attributeValues as $value) {
                $data[$code][] = [
                    'value'  => $this->normalizeValueData($value->getData()),
                    'locale' => $value->getLocale(),
                    'scope'  => $value->getScope()
                ];
            }
        }

        if (isset($context['resource'])) {
            $data['resource'] = $context['resource'];
        }

        return $data;
    }

    /**
     * Returns a subset of values that match the channel and locale requirements
     *
     * @param ArrayCollection $values
     * @param string[]        $channels
     * @param string[]        $locales
     *
     * @return ArrayCollection
     */
    public function filterValues($values, array $channels = [], array $locales = [])
    {
        $values = $values->filter(
            function ($value) use ($channels) {
                return (!$value->getAttribute()->isScopable() || in_array($value->getScope(), $channels));
            }
        );

        $values = $values->filter(
            function ($value) use ($locales) {
                return (!$value->getAttribute()->isLocalizable() || in_array($value->getLocale(), $locales));
            }
        );

        return $values;
    }

    /**
     * Returns an array of all attrbutes for the provided values
     *
     * @param ArrayCollection $values
     *
     * @return array
     */
    public function getAttributes($values)
    {
        $attributes = $values->map(
            function ($value) {
                return $value->getAttribute();
            }
        );

        $uniqueAttributes = array();
        foreach ($attributes as $attribute) {
            if (!array_key_exists($attribute->getCode(), $uniqueAttributes)) {
                $uniqueAttributes[$attribute->getCode()] = $attribute;
            }
        }

        return $uniqueAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Prepares value data form serialization
     *
     * @param mixed $data Data to normalize
     *
     * @return mixed $data Normalized data
     */
    protected function normalizeValueData($data)
    {
        if ($data instanceof \Doctrine\Common\Collections\Collection) {
            $items = array();
            foreach ($data as $item) {
                $items[] = (string) $item;
            }

            return implode(', ', $items);
        }

        if (method_exists($data, '__toString')) {
            return (string) $data;
        }
        if ($data instanceof \DateTime) {
            return $data->format('c');
        }

        return $data;
    }
}
