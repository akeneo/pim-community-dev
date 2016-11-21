<?php

namespace Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Localization\Localizer\LocalizerRegistryInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Normalize a product value into an array
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    /** @var SerializerInterface */
    protected $serializer;

    /** @var LocalizerRegistryInterface */
    protected $localizerRegistry;

    /** @var string[] */
    protected $supportedFormats = ['csv', 'flat'];

    /** @var int */
    protected $precision;

    /**
     * @param LocalizerRegistryInterface $localizerRegistry
     * @param int                        $precision
     */
    public function __construct(LocalizerRegistryInterface $localizerRegistry, $precision = 4)
    {
        $this->localizerRegistry = $localizerRegistry;
        $this->precision = $precision;
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($entity, $format = null, array $context = [])
    {
        $data = $entity->getData();
        $fieldName = $this->getFieldName($entity);
        if ($this->filterLocaleSpecific($entity)) {
            return [];
        }

        $result = null;

        if (is_array($data)) {
            $data = new ArrayCollection($data);
        }

        $type = $entity->getAttribute()->getAttributeType();
        $backendType = $entity->getAttribute()->getBackendType();

        if (AttributeTypes::BOOLEAN === $type) {
            $result = [$fieldName => (string) (int) $data];
        } elseif (is_null($data)) {
            $result = [$fieldName => ''];
            if ('metric' === $backendType) {
                $result[$fieldName . '-unit'] = '';
            }
        } elseif (is_int($data)) {
            $result = [$fieldName => (string) $data];
        } elseif (is_float($data) || 'decimal' === $entity->getAttribute()->getBackendType()) {
            $pattern = $entity->getAttribute()->isDecimalsAllowed() ? sprintf('%%.%sF', $this->precision) : '%d';
            $result = [$fieldName => sprintf($pattern, $data)];
        } elseif (is_string($data)) {
            $result = [$fieldName => $data];
        } elseif (is_object($data)) {
            // TODO: Find a way to have proper currency-suffixed keys for normalized price data
            // even when an empty collection is passed
            if ('prices' === $backendType && $data instanceof Collection && $data->isEmpty()) {
                $result = [];
            } elseif ('options' === $backendType && $data instanceof Collection && $data->isEmpty() === false) {
                $data = $this->sortOptions($data);
                $context['field_name'] = $fieldName;
                $result = $this->serializer->normalize($data, $format, $context);
            } else {
                $context['field_name'] = $fieldName;
                if ('metric' === $backendType) {
                    $context['decimals_allowed'] = $entity->getAttribute()->isDecimalsAllowed();
                } elseif ('media' === $backendType) {
                    $context['value'] = $entity;
                }

                $result = $this->serializer->normalize($data, $format, $context);
            }
        }

        if (null === $result) {
            throw new \RuntimeException(
                sprintf(
                    'Cannot normalize product value "%s" which data is a(n) "%s"',
                    $fieldName,
                    is_object($data) ? get_class($data) : gettype($data)
                )
            );
        }

        $localizer = $this->localizerRegistry->getLocalizer($type);
        if (null !== $localizer) {
            foreach ($result as $field => $data) {
                $result[$field] = $localizer->localize($data, $context);
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductValueInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Normalize the field name for values
     *
     * @param ProductValueInterface $value
     *
     * @return string
     */
    protected function getFieldName(ProductValueInterface $value)
    {
        // TODO : should be extracted
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
     * Check if the attribute is locale specific and check if the given local exist in available locales
     *
     * @param ProductValueInterface $value
     *
     * @return bool
     */
    protected function filterLocaleSpecific(ProductValueInterface $value)
    {
        $attribute = $value->getAttribute();
        if ($attribute->isLocaleSpecific()) {
            $currentLocale = $value->getLocale();
            $availableLocales = $attribute->getLocaleSpecificCodes();
            if (!in_array($currentLocale, $availableLocales)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sort the collection of options by their defined sort order in the attribute
     *
     * @param Collection $optionsCollection
     *
     * @return Collection
     */
    protected function sortOptions(Collection $optionsCollection)
    {
        $options = $optionsCollection->toArray();
        usort(
            $options,
            function ($first, $second) {
                return $first->getSortOrder() > $second->getSortOrder();
            }
        );
        $sortedCollection = new ArrayCollection($options);

        return $sortedCollection;
    }
}
